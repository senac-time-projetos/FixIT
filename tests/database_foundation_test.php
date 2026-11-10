<?php
declare(strict_types=1);

use FixIT\Config\PdoConnection;
use FixIT\Models\CategoryRepository;
use FixIT\Models\CommentRepository;
use FixIT\Models\HistoryRepository;
use FixIT\Models\TicketRepository;
use FixIT\Models\UserRepository;

$root = dirname(__DIR__);
require_once $root . '/backend/config/PdoConnection.php';
require_once $root . '/backend/models/UserRepository.php';
require_once $root . '/backend/models/CategoryRepository.php';
require_once $root . '/backend/models/TicketRepository.php';
require_once $root . '/backend/models/CommentRepository.php';
require_once $root . '/backend/models/HistoryRepository.php';

function dbAssert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

$pdo = PdoConnection::connect(['driver' => 'sqlite', 'database' => ':memory:']);
dbAssert((int) $pdo->query('PRAGMA foreign_keys')->fetchColumn() === 1, 'chaves estrangeiras precisam estar ativas nos testes');

$schema = [
    "CREATE TABLE usuarios (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT NOT NULL, email TEXT NOT NULL UNIQUE, senha_hash TEXT NOT NULL, perfil TEXT NOT NULL, situacao TEXT NOT NULL DEFAULT 'ativo', criado_em TEXT DEFAULT CURRENT_TIMESTAMP, atualizado_em TEXT DEFAULT CURRENT_TIMESTAMP)",
    "CREATE TABLE categorias (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT NOT NULL UNIQUE, descricao TEXT, situacao TEXT NOT NULL DEFAULT 'ativo', criado_em TEXT DEFAULT CURRENT_TIMESTAMP, atualizado_em TEXT DEFAULT CURRENT_TIMESTAMP)",
    "CREATE TABLE chamados (numero INTEGER PRIMARY KEY AUTOINCREMENT, solicitante_id INTEGER NOT NULL, categoria_id INTEGER NOT NULL, tecnico_id INTEGER, titulo TEXT NOT NULL, descricao TEXT NOT NULL, prioridade TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'aberto', solucao TEXT, aberto_em TEXT DEFAULT CURRENT_TIMESTAMP, atualizado_em TEXT DEFAULT CURRENT_TIMESTAMP, encerrado_em TEXT, FOREIGN KEY (solicitante_id) REFERENCES usuarios(id), FOREIGN KEY (categoria_id) REFERENCES categorias(id), FOREIGN KEY (tecnico_id) REFERENCES usuarios(id))",
    "CREATE TABLE comentarios (id INTEGER PRIMARY KEY AUTOINCREMENT, chamado_numero INTEGER NOT NULL, autor_id INTEGER NOT NULL, conteudo TEXT NOT NULL, criado_em TEXT DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (chamado_numero) REFERENCES chamados(numero), FOREIGN KEY (autor_id) REFERENCES usuarios(id))",
    "CREATE TABLE historico_chamados (id INTEGER PRIMARY KEY AUTOINCREMENT, chamado_numero INTEGER NOT NULL, autor_id INTEGER, evento TEXT NOT NULL, descricao TEXT NOT NULL, estado_anterior TEXT, estado_novo TEXT, criado_em TEXT DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (chamado_numero) REFERENCES chamados(numero), FOREIGN KEY (autor_id) REFERENCES usuarios(id))",
];
foreach ($schema as $statement) {
    $pdo->exec($statement);
}

$users = new UserRepository($pdo);
$categories = new CategoryRepository($pdo);
$tickets = new TicketRepository($pdo);
$comments = new CommentRepository($pdo);
$history = new HistoryRepository($pdo);

$requesterId = $users->create([
    'nome' => 'Mariana Oliveira', 'email' => 'MARIANA@FIXIT.TEST',
    'senha_hash' => password_hash('teste-seguro', PASSWORD_DEFAULT), 'perfil' => 'colaborador',
]);
$technicianId = $users->create([
    'nome' => 'Douglas Gomes', 'email' => 'douglas@fixit.test',
    'senha_hash' => password_hash('teste-seguro', PASSWORD_DEFAULT), 'perfil' => 'tecnico',
]);
dbAssert($users->findByEmail('mariana@fixit.test')['id'] === $requesterId, 'busca por e-mail precisa normalizar o valor');
dbAssert(count($users->list(['perfil' => 'tecnico'])) === 1, 'filtro de perfil precisa funcionar');
dbAssert($users->update($requesterId, ['nome' => 'Mariana O.', 'email' => 'mariana@fixit.test', 'perfil' => 'colaborador', 'situacao' => 'ativo']), 'edição de usuário precisa alterar o registro');

$categoryId = $categories->create('Impressora', 'Equipamentos de impressão');
dbAssert(count($categories->list(true)) === 1, 'lista ativa de categorias precisa funcionar');

$ticketNumber = $tickets->create([
    'solicitante_id' => $requesterId, 'categoria_id' => $categoryId,
    'titulo' => 'Impressora não imprime', 'descricao' => 'A fila está parada.', 'prioridade' => 'media',
]);
$history->append([
    'chamado_numero' => $ticketNumber, 'autor_id' => $requesterId, 'evento' => 'abertura',
    'descricao' => 'Chamado aberto.', 'estado_novo' => 'aberto',
]);
dbAssert($tickets->findByNumber($ticketNumber)['solicitante'] === 'Mariana O.', 'detalhe precisa relacionar o solicitante');
dbAssert(count($tickets->listForRequester($requesterId, ['status' => 'aberto'])) === 1, 'lista do colaborador precisa restringir por solicitante');
dbAssert(count($tickets->listQueue(['busca' => (string) $ticketNumber, 'prioridade' => 'media'])) === 1, 'fila precisa combinar busca e prioridade');

PdoConnection::transaction($pdo, function () use ($tickets, $history, $ticketNumber, $technicianId): void {
    dbAssert($tickets->assign($ticketNumber, $technicianId), 'atribuição precisa alterar somente chamado aberto');
    $history->append([
        'chamado_numero' => $ticketNumber, 'autor_id' => $technicianId, 'evento' => 'atribuicao',
        'descricao' => 'Técnico assumiu o chamado.', 'estado_anterior' => 'aberto', 'estado_novo' => 'em_atendimento',
    ]);
});
$comments->create($ticketNumber, $requesterId, 'O problema continua.');
dbAssert(count($comments->listForTicket($ticketNumber)) === 1, 'comentário precisa ser listado com autor');

PdoConnection::transaction($pdo, function () use ($tickets, $history, $ticketNumber, $technicianId): void {
    dbAssert($tickets->close($ticketNumber, $technicianId, 'Fila reiniciada e teste concluído.'), 'encerramento precisa conferir técnico e status');
    $history->append([
        'chamado_numero' => $ticketNumber, 'autor_id' => $technicianId, 'evento' => 'encerramento',
        'descricao' => 'Chamado encerrado.', 'estado_anterior' => 'em_atendimento', 'estado_novo' => 'encerrado',
    ]);
});
dbAssert(count($history->listForTicket($ticketNumber)) === 3, 'histórico precisa preservar os três eventos');
$summary = $tickets->summary();
dbAssert($summary['total'] === 1 && $summary['encerrado'] === 1, 'resumo precisa calcular totais do banco');
dbAssert(count($summary['por_categoria']) === 1 && count($summary['por_prioridade']) === 1, 'resumo precisa calcular distribuições');
dbAssert($users->deactivate($requesterId), 'desativação de usuário precisa preservar o registro');
dbAssert($categories->deactivate($categoryId), 'inativação de categoria precisa preservar o registro');

$beforeRollback = (int) $pdo->query('SELECT COUNT(*) FROM categorias')->fetchColumn();
try {
    PdoConnection::transaction($pdo, function (PDO $connection): void {
        $connection->exec("INSERT INTO categorias (nome, situacao) VALUES ('Temporária', 'ativo')");
        throw new RuntimeException('falha controlada');
    });
} catch (RuntimeException $exception) {
    dbAssert($exception->getMessage() === 'falha controlada', 'a transação precisa preservar a exceção original');
}
dbAssert((int) $pdo->query('SELECT COUNT(*) FROM categorias')->fetchColumn() === $beforeRollback, 'falha precisa executar rollback');

$installationSql = file_get_contents($root . '/database/database.sql');
$seedSql = file_get_contents($root . '/database/seeds/demo.sql');
foreach (['usuarios', 'categorias', 'chamados', 'comentarios', 'historico_chamados'] as $table) {
    dbAssert(str_contains($installationSql, 'CREATE TABLE IF NOT EXISTS ' . $table), 'instalação precisa criar ' . $table);
}
dbAssert(str_contains($installationSql, 'utf8mb4'), 'instalação precisa usar utf8mb4');
dbAssert(!str_contains($seedSql, 'FixIT@Colab2026'), 'seed não pode conter senha em texto puro');
dbAssert(substr_count($seedSql, '$2y$') === 3, 'seed precisa conter três hashes bcrypt');
preg_match_all('/\$2y\$[^\']+/', $seedSql, $seedHashes);
$demoPasswords = ['FixIT@Colab2026', 'FixIT@Tecnico2026', 'FixIT@Admin2026'];
foreach ($demoPasswords as $index => $password) {
    dbAssert(password_verify($password, $seedHashes[0][$index] ?? ''), 'hash fictício do seed precisa corresponder à documentação');
}

echo "OK: fundação de banco e persistência validada.\n";
