<?php
declare(strict_types=1);

use FixIT\Controllers\CollaboratorTicketController;
use FixIT\Controllers\AdministrationController;
use FixIT\Controllers\TechnicianTicketController;
use FixIT\Models\CategoryRepository;
use FixIT\Models\CommentRepository;
use FixIT\Models\HistoryRepository;
use FixIT\Models\TicketRepository;
use FixIT\Models\UserRepository;
use FixIT\Services\TicketService;
use FixIT\Services\AdministrationService;

require_once __DIR__ . '/models/UserRepository.php';
require_once __DIR__ . '/models/CategoryRepository.php';
require_once __DIR__ . '/models/TicketRepository.php';
require_once __DIR__ . '/models/CommentRepository.php';
require_once __DIR__ . '/models/HistoryRepository.php';
require_once __DIR__ . '/services/TicketService.php';
require_once __DIR__ . '/services/AdministrationService.php';
require_once __DIR__ . '/controllers/AdministrationController.php';
require_once __DIR__ . '/controllers/CollaboratorTicketController.php';
require_once __DIR__ . '/controllers/TechnicianTicketController.php';

$pdo = require __DIR__ . '/config/connection.php';
$users = new UserRepository($pdo);
$categories = new CategoryRepository($pdo);
$tickets = new TicketRepository($pdo);
$comments = new CommentRepository($pdo);
$history = new HistoryRepository($pdo);
$ticketService = new TicketService($pdo, $tickets, $categories, $comments, $history);
$administrationService = new AdministrationService($users, $categories);

return [
    'pdo' => $pdo,
    'users' => $users,
    'categories' => $categories,
    'tickets' => $tickets,
    'comments' => $comments,
    'history' => $history,
    'ticket_service' => $ticketService,
    'administration_service' => $administrationService,
    'administration_controller' => new AdministrationController($administrationService, $users, $categories, $tickets),
    'collaborator_ticket_controller' => new CollaboratorTicketController($ticketService, $tickets, $categories, $comments, $history),
    'technician_ticket_controller' => new TechnicianTicketController($ticketService, $tickets, $categories, $comments, $history),
];
