<?php
declare(strict_types=1);

use FixIT\Config\PdoConnection;
use FixIT\Controllers\AdministrationController;
use FixIT\Models\CategoryRepository;
use FixIT\Models\UserRepository;
use FixIT\Models\TicketRepository;
use FixIT\Services\AdministrationService;

$root = dirname(__DIR__);
foreach ([
    '/backend/config/PdoConnection.php', '/backend/models/UserRepository.php',
    '/backend/models/CategoryRepository.php', '/backend/models/TicketRepository.php', '/backend/services/AdministrationService.php',
    '/backend/controllers/AdministrationController.php',
] as $file) {
    require_once $root . $file;
}

function administrationAssert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

function administrationFailure(callable $operation, string $message): void
{
    try {
        $operation();
    } catch (DomainException) {
        return;
    }
    administrationAssert(false, $message);
}

$pdo = PdoConnection::connect(['driver' => 'sqlite', 'database' => ':memory:']);
$pdo->exec("CREATE TABLE usuarios (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT NOT NULL, email TEXT NOT NULL UNIQUE, senha_hash TEXT NOT NULL, perfil TEXT NOT NULL, situacao TEXT NOT NULL DEFAULT 'ativo')");
$pdo->exec("CREATE TABLE categorias (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT NOT NULL UNIQUE, descricao TEXT, situacao TEXT NOT NULL DEFAULT 'ativo')");
$pdo->exec("CREATE TABLE chamados (numero INTEGER PRIMARY KEY AUTOINCREMENT, solicitante_id INTEGER NOT NULL, categoria_id INTEGER NOT NULL, tecnico_id INTEGER, titulo TEXT NOT NULL, descricao TEXT NOT NULL, prioridade TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'aberto', solucao TEXT, aberto_em TEXT DEFAULT CURRENT_TIMESTAMP, atualizado_em TEXT DEFAULT CURRENT_TIMESTAMP, encerrado_em TEXT)");
$users = new UserRepository($pdo);
$categories = new CategoryRepository($pdo);
$tickets = new TicketRepository($pdo);
$service = new AdministrationService($users, $categories);
$controller = new AdministrationController($service, $users, $categories, $tickets);
$administratorId = $users->create(['nome' => 'Ana Admin', 'email' => 'ana@fixit.test', 'senha_hash' => password_hash('admin123', PASSWORD_DEFAULT), 'perfil' => 'administrador']);
$administrator = ['id' => $administratorId, 'nome' => 'Ana Admin', 'perfil' => 'administrador'];
$collaborator = ['id' => 999, 'nome' => 'Sem acesso', 'perfil' => 'colaborador'];

$userId = $controller->saveUser($administrator, null, ['name' => 'Mariana Oliveira', 'email' => 'MARIANA@FIXIT.TEST', 'role' => 'colaborador', 'status' => 'ativo', 'password' => 'segura123']);
$created = $users->findById($userId);
administrationAssert($created['email'] === 'mariana@fixit.test' && password_verify('segura123', $created['senha_hash']), 'cadastro precisa normalizar e gerar hash');
$originalHash = $created['senha_hash'];
$controller->saveUser($administrator, $userId, ['name' => 'Mariana Atualizada', 'email' => 'mariana@fixit.test', 'role' => 'tecnico', 'status' => 'ativo', 'password' => '']);
administrationAssert($users->findById($userId)['senha_hash'] === $originalHash, 'senha vazia na edição precisa preservar hash');
administrationFailure(fn () => $controller->saveUser($administrator, null, ['name' => 'Duplicada', 'email' => 'mariana@fixit.test', 'role' => 'colaborador', 'status' => 'ativo', 'password' => 'segura123']), 'e-mail duplicado precisa falhar');
administrationFailure(fn () => $controller->deactivateUser($administrator, $administratorId), 'administrador não pode desativar a própria conta');
administrationFailure(fn () => $controller->saveUser($administrator, $administratorId, ['name' => 'Ana Admin', 'email' => 'ana@fixit.test', 'role' => 'administrador', 'status' => 'inativo', 'password' => '']), 'edição não pode contornar bloqueio de auto-desativação');
$controller->deactivateUser($administrator, $userId);
administrationAssert($users->findById($userId)['situacao'] === 'inativo', 'desativação precisa preservar e inativar usuário');

$categoryId = $controller->saveCategory($administrator, null, ['name' => 'Impressora', 'description' => 'Equipamentos de impressão', 'status' => 'ativo']);
$tickets->create(['solicitante_id' => $administratorId, 'categoria_id' => $categoryId, 'titulo' => 'Teste de resumo', 'descricao' => 'Chamado usado para validar agregações.', 'prioridade' => 'alta']);
$summary = $controller->summary($administrator);
administrationAssert($summary['total'] === 1 && $summary['aberto'] === 1 && $summary['by_category']['Impressora'] === 1 && $summary['by_priority']['alta'] === 1, 'resumo administrativo precisa usar agregações persistidas');
administrationFailure(fn () => $controller->summary($collaborator), 'colaborador não pode consultar resumo administrativo');
$controller->saveCategory($administrator, $categoryId, ['name' => 'Impressoras', 'description' => 'Impressoras e filas', 'status' => 'ativo']);
administrationAssert($categories->findById($categoryId)['descricao'] === 'Impressoras e filas', 'edição precisa persistir descrição');
administrationFailure(fn () => $controller->saveCategory($administrator, null, ['name' => 'Impressoras', 'description' => '', 'status' => 'ativo']), 'categoria duplicada precisa falhar');
$controller->deactivateCategory($administrator, $categoryId);
administrationAssert($categories->list(true) === [], 'categoria inativa não pode aparecer em seleção ativa');
administrationFailure(fn () => $controller->users($collaborator), 'colaborador não pode acessar cadastros administrativos');

foreach (['usuario-form.php', 'categorias.php'] as $page) {
    $source = file_get_contents($root . '/frontend/pages/' . $page);
    administrationAssert(str_contains($source, 'name="_csrf"'), $page . ' precisa enviar token CSRF');
    administrationAssert(str_contains($source, '$csrf->validate'), $page . ' precisa validar token CSRF');
}

echo "OK: cadastros administrativos validados.\n";
