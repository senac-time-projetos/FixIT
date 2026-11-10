<?php
declare(strict_types=1);

use FixIT\Models\CategoryRepository;
use FixIT\Models\CommentRepository;
use FixIT\Models\HistoryRepository;
use FixIT\Models\TicketRepository;
use FixIT\Models\UserRepository;

require_once __DIR__ . '/models/UserRepository.php';
require_once __DIR__ . '/models/CategoryRepository.php';
require_once __DIR__ . '/models/TicketRepository.php';
require_once __DIR__ . '/models/CommentRepository.php';
require_once __DIR__ . '/models/HistoryRepository.php';

$pdo = require __DIR__ . '/config/connection.php';

return [
    'pdo' => $pdo,
    'users' => new UserRepository($pdo),
    'categories' => new CategoryRepository($pdo),
    'tickets' => new TicketRepository($pdo),
    'comments' => new CommentRepository($pdo),
    'history' => new HistoryRepository($pdo),
];
