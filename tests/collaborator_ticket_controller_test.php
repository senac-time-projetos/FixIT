<?php
declare(strict_types=1);

use FixIT\Config\PdoConnection;
use FixIT\Controllers\CollaboratorTicketController;
use FixIT\Models\CategoryRepository;
use FixIT\Models\CommentRepository;
use FixIT\Models\HistoryRepository;
use FixIT\Models\TicketRepository;
use FixIT\Models\UserRepository;
use FixIT\Services\TicketService;

$root = dirname(__DIR__);
foreach ([
    '/backend/config/PdoConnection.php', '/backend/models/UserRepository.php',
    '/backend/models/CategoryRepository.php', '/backend/models/TicketRepository.php',
    '/backend/models/CommentRepository.php', '/backend/models/HistoryRepository.php',
    '/backend/services/TicketService.php', '/backend/controllers/CollaboratorTicketController.php',
] as $file) {
    require_once $root . $file;
}

function controllerAssert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

$pdo = PdoConnection::connect(['driver' => 'sqlite', 'database' => ':memory:']);
foreach ([
    "CREATE TABLE usuarios (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT NOT NULL, email TEXT NOT NULL UNIQUE, senha_hash TEXT NOT NULL, perfil TEXT NOT NULL, situacao TEXT NOT NULL DEFAULT 'ativo')",
    "CREATE TABLE categorias (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT NOT NULL UNIQUE, descricao TEXT, situacao TEXT NOT NULL DEFAULT 'ativo')",
    "CREATE TABLE chamados (numero INTEGER PRIMARY KEY AUTOINCREMENT, solicitante_id INTEGER NOT NULL, categoria_id INTEGER NOT NULL, tecnico_id INTEGER, titulo TEXT NOT NULL, descricao TEXT NOT NULL, prioridade TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'aberto', solucao TEXT, aberto_em TEXT DEFAULT CURRENT_TIMESTAMP, atualizado_em TEXT DEFAULT CURRENT_TIMESTAMP, encerrado_em TEXT, FOREIGN KEY (solicitante_id) REFERENCES usuarios(id), FOREIGN KEY (categoria_id) REFERENCES categorias(id), FOREIGN KEY (tecnico_id) REFERENCES usuarios(id))",
    "CREATE TABLE comentarios (id INTEGER PRIMARY KEY AUTOINCREMENT, chamado_numero INTEGER NOT NULL, autor_id INTEGER NOT NULL, conteudo TEXT NOT NULL, criado_em TEXT DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (chamado_numero) REFERENCES chamados(numero), FOREIGN KEY (autor_id) REFERENCES usuarios(id))",
    "CREATE TABLE historico_chamados (id INTEGER PRIMARY KEY AUTOINCREMENT, chamado_numero INTEGER NOT NULL, autor_id INTEGER, evento TEXT NOT NULL, descricao TEXT NOT NULL, estado_anterior TEXT, estado_novo TEXT, criado_em TEXT DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (chamado_numero) REFERENCES chamados(numero), FOREIGN KEY (autor_id) REFERENCES usuarios(id))",
] as $statement) {
    $pdo->exec($statement);
}

$users = new UserRepository($pdo);
$categories = new CategoryRepository($pdo);
$tickets = new TicketRepository($pdo);
$comments = new CommentRepository($pdo);
$history = new HistoryRepository($pdo);
$service = new TicketService($pdo, $tickets, $categories, $comments, $history);
$controller = new CollaboratorTicketController($service, $tickets, $categories, $comments, $history);
$newActor = static function (string $name) use ($users): array {
    $id = $users->create(['nome' => $name, 'email' => strtolower(str_replace(' ', '.', $name)) . '@fixit.test', 'senha_hash' => 'hash', 'perfil' => 'colaborador']);
    return ['id' => $id, 'nome' => $name, 'perfil' => 'colaborador'];
};
$owner = $newActor('Mariana Oliveira');
$other = $newActor('Carlos Souza');
$activeCategory = $categories->create('Impressora');
$inactiveCategory = $categories->create('Legado');
$categories->deactivate($inactiveCategory);

controllerAssert($controller->activeCategories() === [$activeCategory => 'Impressora'], 'formulário deve receber apenas categorias ativas');
$number = $controller->open($owner, [
    'title' => 'Impressora não imprime', 'description' => 'A impressora não responde desde o início do expediente.',
    'category' => (string) $activeCategory, 'priority' => 'media',
]);
$controller->open($other, [
    'title' => 'Monitor sem imagem', 'description' => 'O monitor permanece sem imagem após reiniciar.',
    'category' => (string) $activeCategory, 'priority' => 'baixa',
]);
controllerAssert(count($controller->list($owner)) === 1, 'lista deve restringir chamados pelo usuário autenticado');
controllerAssert($controller->detail($other, $number) === null, 'detalhe não deve revelar chamado de outro colaborador');
$detail = $controller->detail($owner, $number);
controllerAssert($detail !== null && $detail['number'] === $number, 'detalhe autorizado deve usar o contrato visual');
$controller->comment($owner, $number, 'O problema ainda acontece.');
$detail = $controller->detail($owner, $number);
controllerAssert(count($detail['history']) === 2 && str_contains($detail['history'][1]['text'], 'O problema ainda acontece.'), 'comentário deve aparecer na linha do tempo');

$pdo->prepare("UPDATE chamados SET status = 'encerrado', solucao = 'Resolvido em teste', encerrado_em = CURRENT_TIMESTAMP WHERE numero = :numero")->execute(['numero' => $number]);
try {
    $controller->comment($owner, $number, 'Comentário tardio.');
    controllerAssert(false, 'chamado encerrado não pode receber comentário');
} catch (DomainException) {
}

foreach (['novo-chamado.php', 'chamado.php'] as $page) {
    $source = file_get_contents($root . '/frontend/pages/' . $page);
    controllerAssert(str_contains($source, 'name="_csrf"'), $page . ' precisa enviar token CSRF');
}

echo "OK: integração do fluxo real do colaborador validada.\n";
