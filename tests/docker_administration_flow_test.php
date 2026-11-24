<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$application = require $root . '/backend/bootstrap.php';
$pdo = $application['pdo'];
$users = $application['users'];
$categories = $application['categories'];
$controller = $application['administration_controller'];

function dockerAdministrationAssert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

$adminRow = $users->findByEmail('admin@fixit.test');
dockerAdministrationAssert($adminRow !== null, 'seed precisa fornecer administrador');
$administrator = ['id' => (int) $adminRow['id'], 'nome' => (string) $adminRow['nome'], 'perfil' => (string) $adminRow['perfil']];
$beforeUsers = (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
$beforeCategories = (int) $pdo->query('SELECT COUNT(*) FROM categorias')->fetchColumn();

$pdo->beginTransaction();
try {
    $userId = $controller->saveUser($administrator, null, ['name' => 'Teste Temporário', 'email' => 'temporario.admin@fixit.test', 'role' => 'tecnico', 'status' => 'ativo', 'password' => 'temporaria123']);
    $createdUser = $users->findById($userId);
    dockerAdministrationAssert(password_verify('temporaria123', $createdUser['senha_hash']), 'MySQL precisa armazenar hash válido');
    $hash = $createdUser['senha_hash'];
    $controller->saveUser($administrator, $userId, ['name' => 'Teste Atualizado', 'email' => 'temporario.admin@fixit.test', 'role' => 'colaborador', 'status' => 'ativo', 'password' => '']);
    dockerAdministrationAssert($users->findById($userId)['senha_hash'] === $hash, 'MySQL precisa preservar senha vazia na edição');
    $controller->deactivateUser($administrator, $userId);

    $categoryId = $controller->saveCategory($administrator, null, ['name' => 'Categoria Temporária', 'description' => 'Criada no teste transacional', 'status' => 'ativo']);
    $controller->saveCategory($administrator, $categoryId, ['name' => 'Categoria Temporária Atualizada', 'description' => 'Atualizada', 'status' => 'ativo']);
    $controller->deactivateCategory($administrator, $categoryId);
    dockerAdministrationAssert($categories->findById($categoryId)['situacao'] === 'inativo', 'MySQL precisa preservar categoria desativada');
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

dockerAdministrationAssert((int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() === $beforeUsers, 'teste não pode deixar usuário residual');
dockerAdministrationAssert((int) $pdo->query('SELECT COUNT(*) FROM categorias')->fetchColumn() === $beforeCategories, 'teste não pode deixar categoria residual');
echo "OK: fluxo administrativo validado no MySQL Docker sem resíduos.\n";
