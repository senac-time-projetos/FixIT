<?php
declare(strict_types=1);

use FixIT\Config\PdoConnection;
use FixIT\Models\CategoryRepository;
use FixIT\Models\CommentRepository;
use FixIT\Models\HistoryRepository;
use FixIT\Models\TicketRepository;
use FixIT\Models\UserRepository;
use FixIT\Services\TicketService;

$root = dirname(__DIR__);
require_once $root . '/backend/config/PdoConnection.php';
require_once $root . '/backend/models/UserRepository.php';
require_once $root . '/backend/models/CategoryRepository.php';
require_once $root . '/backend/models/TicketRepository.php';
require_once $root . '/backend/models/CommentRepository.php';
require_once $root . '/backend/models/HistoryRepository.php';
require_once $root . '/backend/services/TicketService.php';

function ticketAssert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

function expectDomainException(callable $operation, string $message): void
{
    try {
        $operation();
    } catch (DomainException) {
        return;
    }
    ticketAssert(false, $message);
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

$makeActor = static function (string $name, string $profile) use ($users): array {
    $id = $users->create([
        'nome' => $name,
        'email' => strtolower(str_replace(' ', '.', $name)) . '@fixit.test',
        'senha_hash' => password_hash('teste-seguro', PASSWORD_DEFAULT),
        'perfil' => $profile,
    ]);
    return ['id' => $id, 'nome' => $name, 'perfil' => $profile];
};

$owner = $makeActor('Mariana Oliveira', 'colaborador');
$otherRequester = $makeActor('Carlos Souza', 'colaborador');
$technician = $makeActor('Douglas Gomes', 'tecnico');
$otherTechnician = $makeActor('Fernanda Lima', 'tecnico');
$administrator = $makeActor('Ana Admin', 'administrador');
$categoryId = $categories->create('Impressora', 'Equipamentos de impressão');
$inactiveCategoryId = $categories->create('Legado', 'Categoria indisponível');
$categories->deactivate($inactiveCategoryId);

expectDomainException(
    fn () => $service->open($owner, ['titulo' => 'Oi', 'descricao' => 'Descrição suficientemente longa.', 'categoria_id' => $categoryId, 'prioridade' => 'media']),
    'abertura precisa rejeitar título curto'
);
expectDomainException(
    fn () => $service->open($owner, ['titulo' => 'Problema válido', 'descricao' => 'Descrição suficientemente longa.', 'categoria_id' => $inactiveCategoryId, 'prioridade' => 'media']),
    'abertura precisa rejeitar categoria inativa'
);

$number = $service->open($owner, [
    'titulo' => 'Impressora não imprime',
    'descricao' => 'A fila de impressão permanece parada desde hoje cedo.',
    'categoria_id' => $categoryId,
    'prioridade' => 'alta',
]);
$ticket = $tickets->findByNumber($number);
ticketAssert($ticket !== null && $ticket['status'] === 'aberto', 'abertura precisa criar chamado aberto');
ticketAssert(count($history->listForTicket($number)) === 1, 'abertura precisa criar exatamente um histórico');
ticketAssert($service->canView($owner, $ticket), 'solicitante precisa visualizar o próprio chamado');
ticketAssert(!$service->canView($otherRequester, $ticket), 'outro colaborador não pode visualizar o chamado');
ticketAssert($service->canView($technician, $ticket), 'técnico precisa visualizar a fila');
ticketAssert($service->canView($administrator, $ticket), 'administrador precisa visualizar todos os chamados');

expectDomainException(fn () => $service->comment($number, $otherRequester, 'Tentativa indevida.'), 'comentário precisa respeitar visibilidade');
$commentId = $service->comment($number, $owner, 'O problema continua após reiniciar.');
ticketAssert($commentId > 0 && count($comments->listForTicket($number)) === 1, 'solicitante precisa comentar no próprio chamado');
expectDomainException(fn () => $service->comment($number, $owner, 'x'), 'comentário curto precisa ser rejeitado');

expectDomainException(fn () => $service->assign($number, $administrator), 'administrador não pode assumir atendimento técnico');
$service->assign($number, $technician);
$assigned = $tickets->findByNumber($number);
ticketAssert($assigned['status'] === 'em_atendimento' && (int) $assigned['tecnico_id'] === $technician['id'], 'atribuição precisa salvar técnico e status');
ticketAssert(count($history->listForTicket($number)) === 2, 'atribuição precisa acrescentar histórico');
expectDomainException(fn () => $service->assign($number, $otherTechnician), 'segundo técnico não pode assumir chamado atribuído');
ticketAssert(count($history->listForTicket($number)) === 2, 'atribuição recusada não pode criar histórico');

expectDomainException(fn () => $service->registerUpdate($number, $otherTechnician, 'Atualização indevida.'), 'somente técnico responsável pode atualizar');
$updateId = $service->registerUpdate($number, $technician, 'Fila limpa e serviço de impressão reiniciado.');
ticketAssert($updateId > 0 && count($history->listForTicket($number)) === 3, 'atualização técnica precisa entrar no histórico');
expectDomainException(fn () => $service->close($number, $technician, 'Curta'), 'solução curta precisa ser rejeitada');
expectDomainException(fn () => $service->close($number, $otherTechnician, 'Solução válida, porém por técnico incorreto.'), 'outro técnico não pode encerrar');
$service->close($number, $technician, 'Fila reiniciada e página de teste impressa com sucesso.');
$closed = $tickets->findByNumber($number);
ticketAssert($closed['status'] === 'encerrado' && $closed['solucao'] !== null && $closed['encerrado_em'] !== null, 'encerramento precisa salvar estado, solução e data');
ticketAssert(count($history->listForTicket($number)) === 4, 'encerramento precisa acrescentar histórico');

$rollbackNumber = $service->open($owner, [
    'titulo' => 'Teste de rollback',
    'descricao' => 'Chamado usado para comprovar a atomicidade da atribuição.',
    'categoria_id' => $categoryId,
    'prioridade' => 'baixa',
]);
$pdo->exec("CREATE TRIGGER fail_assignment_history BEFORE INSERT ON historico_chamados WHEN NEW.evento = 'atribuicao' BEGIN SELECT RAISE(ABORT, 'falha controlada'); END");
try {
    $service->assign($rollbackNumber, $technician);
    ticketAssert(false, 'falha de histórico precisa interromper a atribuição');
} catch (PDOException $exception) {
    ticketAssert(str_contains($exception->getMessage(), 'falha controlada'), 'teste de rollback precisa capturar a falha controlada');
}
$pdo->exec('DROP TRIGGER fail_assignment_history');
$rolledBack = $tickets->findByNumber($rollbackNumber);
ticketAssert($rolledBack['status'] === 'aberto' && $rolledBack['tecnico_id'] === null, 'falha no histórico precisa reverter a atribuição');
ticketAssert(count($history->listForTicket($rollbackNumber)) === 1, 'rollback não pode deixar histórico órfão');

echo "OK: regras transacionais de chamados validadas.\n";
