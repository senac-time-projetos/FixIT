<?php
declare(strict_types=1);

use FixIT\Config\PdoConnection;
use FixIT\Controllers\TechnicianTicketController;
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
    '/backend/services/TicketService.php', '/backend/controllers/TechnicianTicketController.php',
] as $file) {
    require_once $root . $file;
}

function technicianControllerAssert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

function technicianDomainFailure(callable $operation, string $message): void
{
    try {
        $operation();
    } catch (DomainException) {
        return;
    }
    technicianControllerAssert(false, $message);
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
$controller = new TechnicianTicketController($service, $tickets, $categories, $comments, $history);
$newActor = static function (string $name, string $profile) use ($users): array {
    $id = $users->create(['nome' => $name, 'email' => strtolower(str_replace(' ', '.', $name)) . '@fixit.test', 'senha_hash' => 'hash', 'perfil' => $profile]);
    return ['id' => $id, 'nome' => $name, 'perfil' => $profile];
};
$owner = $newActor('Mariana Oliveira', 'colaborador');
$technician = $newActor('Douglas Gomes', 'tecnico');
$otherTechnician = $newActor('Fernanda Lima', 'tecnico');
$administrator = $newActor('Ana Admin', 'administrador');
$categoryId = $categories->create('Impressora');

$number = $service->open($owner, [
    'titulo' => 'Impressora não imprime', 'descricao' => 'A fila permanece parada desde o início do expediente.',
    'categoria_id' => $categoryId, 'prioridade' => 'alta',
]);
technicianControllerAssert(count($controller->queue($technician, ['busca' => 'Mariana', 'prioridade' => 'alta'])) === 1, 'fila precisa aplicar busca e prioridade');
technicianControllerAssert(count($controller->queue($administrator, ['status' => 'aberto'])) === 1, 'administrador precisa consultar a fila');
technicianDomainFailure(fn () => $controller->queue($owner), 'colaborador não pode consultar fila técnica');

$detail = $controller->detail($technician, $number);
technicianControllerAssert($detail !== null && $detail['assignee_id'] === null, 'detalhe técnico precisa adaptar o chamado livre');
$controller->assign($technician, $number);
technicianDomainFailure(fn () => $controller->assign($otherTechnician, $number), 'segundo técnico não pode assumir chamado atribuído');
technicianDomainFailure(fn () => $controller->update($otherTechnician, $number, 'Tentativa indevida.'), 'outro técnico não pode atualizar');
$controller->update($technician, $number, 'Fila limpa e serviço reiniciado.');
$controller->close($technician, $number, 'Fila reiniciada e página de teste impressa com sucesso.');
$closed = $controller->detail($administrator, $number);
technicianControllerAssert($closed['status'] === 'encerrado' && $closed['solution'] !== null, 'encerramento precisa ficar disponível para consulta');
technicianControllerAssert(count($closed['history']) === 4, 'linha do tempo precisa conter abertura, atribuição, atualização e encerramento');

$technicianNumber = $controller->open($technician, [
    'title' => 'Acesso ao sistema', 'description' => 'O acesso apresenta erro ao abrir o painel principal.',
    'category' => (string) $categoryId, 'priority' => 'media',
]);
technicianControllerAssert($tickets->findByNumber($technicianNumber)['solicitante_id'] === $technician['id'], 'abertura pelo técnico precisa usar a sessão');

foreach (['novo-chamado.php', 'atender-chamado.php'] as $page) {
    $source = file_get_contents($root . '/frontend/pages/' . $page);
    technicianControllerAssert(str_contains($source, 'name="_csrf"'), $page . ' precisa enviar token CSRF');
}

echo "OK: integração do fluxo técnico validada.\n";
