<?php
// api.php - Backend Controller Router for PocketGo
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'db_conn.php';

// Parse query action
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Helper to get JSON payload
function getJsonInput() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

// Return JSON error response
function sendError($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}

// Return JSON success response
function sendSuccess($data = []) {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

if (!$pdo) {
    sendError('Database connection failed. Please ensure MySQL is running and pocketgo_db is imported.', 500);
}

// Hydrate user record with multiple cards and Visa card information from separated relational tables
function hydrateUser($user) {
    global $pdo;
    if (!$user) return null;
    $user['balance'] = (float)$user['balance'];
    $user['daily_limit'] = (float)$user['daily_limit'];
    $user['topupTotal'] = (float)$user['topupTotal'];
    $user['topupCount'] = (int)$user['topupCount'];

    $cards = [];
    try {
        // Query normalized student_cards and students tables
        $stmt = $pdo->prepare("
            SELECT sc.*, s.name AS student_name, s.student_id, s.class 
            FROM student_cards sc
            JOIN students s ON sc.student_id = s.student_id
            WHERE s.userId = ?
        ");
        $stmt->execute([$user['id']]);
        $rows = $stmt->fetchAll();
        if ($rows && count($rows) > 0) {
            foreach ($rows as $row) {
                $cards[] = [
                    'card_serial' => $row['card_serial'],
                    'student_name' => $row['student_name'],
                    'student_id' => $row['student_id'],
                    'class' => $row['class'],
                    'balance' => (float)$row['balance'],
                    'daily_limit' => (float)$row['daily_limit'],
                    'status' => $row['status']
                ];
            }
        }
    } catch (\Exception $e) {
        // Fallback to legacy behavior if table query fails
    }

    if (empty($cards) && !empty($user['cards_json'])) {
        $cards = json_decode($user['cards_json'], true);
    }
    
    // Backwards-compatible fallback if cards are still empty
    if (empty($cards) && (!empty($user['card_serial']) || !empty($user['studentId']))) {
        $cards = [
            [
                'card_serial' => $user['card_serial'],
                'student_name' => $user['child'],
                'student_id' => $user['studentId'],
                'class' => $user['childClass'],
                'balance' => (float)$user['balance'],
                'daily_limit' => (float)$user['daily_limit'],
                'status' => $user['status'] ?? 'active'
            ]
        ];
    }
    $user['cards'] = $cards;

    $visa = null;
    try {
        // Query normalized visa_cards table
        $stmtV = $pdo->prepare("SELECT * FROM visa_cards WHERE userId = ? LIMIT 1");
        $stmtV->execute([$user['id']]);
        $vRow = $stmtV->fetch();
        if ($vRow) {
            $visa = [
                'cardholder_name' => $vRow['cardholder_name'],
                'card_number' => $vRow['card_number'],
                'expiry_date' => $vRow['expiry_date']
            ];
        }
    } catch (\Exception $e) {
        // Fallback to legacy behavior if table query fails
    }

    if (!$visa && !empty($user['visa_card_json'])) {
        $visa = json_decode($user['visa_card_json'], true);
    }
    $user['visa_card'] = $visa;

    return $user;
}

switch ($action) {
    
    // ── 1. LOGIN ──
    case 'login':
        $input = getJsonInput();
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? 'parent';

        if (empty($email) || empty($password)) {
            sendError('Please enter your credentials.');
        }

        if ($role === 'admin') {
            // Admin authentication
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            if ($admin && $admin['password'] === $password) {
                sendSuccess(['role' => 'admin', 'user' => $admin]);
            }
            sendError('Invalid admin credentials.');
        } else {
            // Parent authentication
            $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?) AND role = 'parent'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && $user['password'] === $password) {
                $user = hydrateUser($user);
                sendSuccess(['role' => 'parent', 'user' => $user]);
            }
            sendError('Invalid email or password.');
        }
        break;

    // ── 2. REGISTER ──
    case 'register':
        $input = getJsonInput();
        $name = trim($input['name'] ?? '');
        $ic = trim($input['ic'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            sendError('Missing required registration fields.');
        }

        // Check if email already registered
        $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            sendError('Email address is already registered.');
        }

        $stmt = $pdo->prepare("INSERT INTO users (name, ic, email, phone, child, childClass, studentId, card_serial, balance, daily_limit, status, password, role, topupTotal, topupCount) VALUES (?, ?, ?, ?, '', '', '', '', 0.00, 50.00, 'active', ?, 'parent', 0.00, 0)");
        $stmt->execute([$name, $ic, $email, $phone, $password]);
        
        $newUserId = $pdo->lastInsertId();
        
        // Fetch newly created user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$newUserId]);
        $user = hydrateUser($stmt->fetch());

        sendSuccess(['user' => $user]);
        break;

    // ── 3. FETCH USER DATA ──
    case 'user':
        $email = $_GET['email'] ?? '';
        if (empty($email)) {
            sendError('Email parameter required.');
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            sendError('User not found.');
        }

        $user = hydrateUser($user);

        // Get user transactions
        $stmtTx = $pdo->prepare("SELECT * FROM transactions WHERE userId = ? ORDER BY date DESC");
        $stmtTx->execute([$user['id']]);
        $txns = $stmtTx->fetchAll();
        foreach ($txns as &$tx) {
            $tx['id'] = (int)$tx['id'];
            $tx['userId'] = (int)$tx['userId'];
            $tx['amount'] = (float)$tx['amount'];
        }

        // Get user support reports
        $stmtRep = $pdo->prepare("SELECT * FROM reports WHERE reporterName = ? ORDER BY createdAt DESC");
        $stmtRep->execute([$user['name']]);
        $reports = $stmtRep->fetchAll();
        foreach ($reports as &$rep) {
            $rep['id'] = (int)$rep['id'];
        }

        sendSuccess([
            'user' => $user,
            'transactions' => $txns,
            'reports' => $reports
        ]);
        break;

    // ── 4. UPDATE SPENDING LIMIT ──
    case 'update-limit':
        $input = getJsonInput();
        $email = trim($input['email'] ?? '');
        $limit = (float)($input['limit'] ?? 50.00);
        $cardSerial = trim($input['card_serial'] ?? '');

        if (empty($email)) {
            sendError('Email required.');
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            sendError('User not found.');
        }

        $cards = [];
        if (!empty($user['cards_json'])) {
            $cards = json_decode($user['cards_json'], true);
        }
        if (empty($cards) && (!empty($user['card_serial']) || !empty($user['studentId']))) {
            $cards = [
                [
                    'card_serial' => $user['card_serial'],
                    'student_name' => $user['child'],
                    'student_id' => $user['studentId'],
                    'class' => $user['childClass'],
                    'balance' => (float)$user['balance'],
                    'daily_limit' => (float)$user['daily_limit'],
                    'status' => $user['status'] ?? 'active'
                ]
            ];
        }

        // Find the card and update limit
        $found = false;
        $syncLimit = $limit;
        foreach ($cards as &$c) {
            if ($c['card_serial'] === $cardSerial || (empty($cardSerial) && count($cards) === 1)) {
                $c['daily_limit'] = $limit;
                $found = true;
                $syncLimit = $limit;
                break;
            }
        }

        // Save back to both separated student_cards table and synced users table
        try {
            $stmtCardUp = $pdo->prepare("UPDATE student_cards SET daily_limit = ? WHERE card_serial = ?");
            $stmtCardUp->execute([$limit, $cardSerial]);
        } catch (\Exception $e) {
            // Silently ignore if table doesn't exist
        }

        $stmtUp = $pdo->prepare("UPDATE users SET daily_limit = ?, cards_json = ? WHERE id = ?");
        $stmtUp->execute([$syncLimit, json_encode($cards), $user['id']]);

        // Get updated user details
        $stmtGet = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmtGet->execute([$user['id']]);
        $updatedUser = hydrateUser($stmtGet->fetch());

        sendSuccess(['user' => $updatedUser]);
        break;

    // ── 4b. REGISTER CARD ──
    case 'register-card':
        $input = getJsonInput();
        $email = trim($input['email'] ?? '');
        $cardSerial = trim($input['card_serial'] ?? '');
        $studentName = trim($input['student_name'] ?? '');
        $studentNric = trim($input['student_nric'] ?? '');
        $class = trim($input['class'] ?? '');

        if (empty($email) || empty($cardSerial) || empty($studentName) || empty($studentNric) || empty($class)) {
            sendError('Please fill in all card details.');
        }

        if (strlen($cardSerial) !== 10 || !is_numeric($cardSerial)) {
            sendError('Card Serial No. must be exactly 10 digits.');
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            sendError('User not found.');
        }

        // Parse existing cards
        $cards = [];
        if (!empty($user['cards_json'])) {
            $cards = json_decode($user['cards_json'], true);
        }
        
        // Backwards-compatible migration if cards list is empty
        if (empty($cards) && (!empty($user['card_serial']) || !empty($user['studentId']))) {
            $cards = [
                [
                    'card_serial' => $user['card_serial'],
                    'student_name' => $user['child'],
                    'student_id' => $user['studentId'],
                    'class' => $user['childClass'],
                    'balance' => (float)$user['balance'],
                    'daily_limit' => (float)$user['daily_limit'],
                    'status' => $user['status'] ?? 'active'
                ]
            ];
        }

        // Check if card serial is already registered
        foreach ($cards as $c) {
            if ($c['card_serial'] === $cardSerial) {
                sendError('This card serial is already registered.');
            }
        }

        // Create new card
        $newCard = [
            'card_serial' => $cardSerial,
            'student_name' => $studentName,
            'student_id' => $studentNric,
            'class' => $class,
            'balance' => 0.00,
            'daily_limit' => 50.00,
            'status' => 'active'
        ];
        $cards[] = $newCard;

        // If this is the FIRST card, we sync it to the main columns
        $childName = $user['child'];
        $childCls = $user['childClass'];
        $sid = $user['studentId'];
        $serial = $user['card_serial'];
        $bal = (float)$user['balance'];
        $limitVal = (float)$user['daily_limit'];

        if (count($cards) === 1) {
            $childName = $studentName;
            $childCls = $class;
            $sid = $studentNric;
            $serial = $cardSerial;
            $bal = 0.00;
            $limitVal = 50.00;
        }

        // Save to normalized student and cards tables, and fallback to users table
        try {
            // Check if student profile already exists
            $stmtCheckS = $pdo->prepare("SELECT id FROM students WHERE student_id = ?");
            $stmtCheckS->execute([$studentNric]);
            if (!$stmtCheckS->fetch()) {
                $stmtInsS = $pdo->prepare("INSERT INTO students (userId, student_id, name, class) VALUES (?, ?, ?, ?)");
                $stmtInsS->execute([$user['id'], $studentNric, $studentName, $class]);
            }

            // Insert card record
            $stmtInsC = $pdo->prepare("INSERT INTO student_cards (student_id, card_serial, balance, daily_limit, status) VALUES (?, ?, 0.00, 50.00, 'active')");
            $stmtInsC->execute([$studentNric, $cardSerial]);
        } catch (\Exception $e) {
            // Silently ignore if tables don't exist
        }

        $stmtUp = $pdo->prepare("UPDATE users SET child = ?, childClass = ?, studentId = ?, card_serial = ?, balance = ?, daily_limit = ?, cards_json = ? WHERE id = ?");
        $stmtUp->execute([$childName, $childCls, $sid, $serial, $bal, $limitVal, json_encode($cards), $user['id']]);

        // Get updated user details
        $stmtGet = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmtGet->execute([$user['id']]);
        $updatedUser = hydrateUser($stmtGet->fetch());

        sendSuccess(['user' => $updatedUser]);
        break;

    // ── 4c. LINK VISA CARD ──
    case 'link-visa':
        $input = getJsonInput();
        $email = trim($input['email'] ?? '');
        $cardholder = trim($input['cardholder_name'] ?? '');
        $cardNum = trim($input['card_number'] ?? '');
        $expiry = trim($input['expiry_date'] ?? '');
        $cvv = trim($input['cvv'] ?? '');

        if (empty($email) || empty($cardholder) || empty($cardNum) || empty($expiry) || empty($cvv)) {
            sendError('Please fill in all credit card details.');
        }

        // Clean card number (e.g. spaces/dashes) and check length
        $cleanCard = str_replace([' ', '-'], '', $cardNum);
        if (strlen($cleanCard) < 13 || strlen($cleanCard) > 19 || !is_numeric($cleanCard)) {
            sendError('Invalid card number format.');
        }

        // Mask the card number to keep last 4 digits (e.g. •••• 4321)
        $maskedCard = '•••• ' . substr($cleanCard, -4);

        $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            sendError('User not found.');
        }

        $visaObj = [
            'cardholder_name' => $cardholder,
            'card_number' => $maskedCard,
            'expiry_date' => $expiry
        ];

        // Save to normalized visa_cards table
        try {
            // Delete previous visa card for user (one visa card per user logic)
            $stmtDelV = $pdo->prepare("DELETE FROM visa_cards WHERE userId = ?");
            $stmtDelV->execute([$user['id']]);

            // Insert new visa card
            $stmtInsV = $pdo->prepare("INSERT INTO visa_cards (userId, cardholder_name, card_number, expiry_date) VALUES (?, ?, ?, ?)");
            $stmtInsV->execute([$user['id'], $cardholder, $maskedCard, $expiry]);
        } catch (\Exception $e) {
            // Silently ignore if table doesn't exist
        }

        $stmtUp = $pdo->prepare("UPDATE users SET visa_card_json = ? WHERE id = ?");
        $stmtUp->execute([json_encode($visaObj), $user['id']]);

        // Fetch and return hydrated user
        $stmtGet = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmtGet->execute([$user['id']]);
        $updatedUser = hydrateUser($stmtGet->fetch());

        sendSuccess(['user' => $updatedUser]);
        break;

    // ── 5. PERFORM TOP UP ──
    case 'topup':
        $input = getJsonInput();
        $email = trim($input['email'] ?? '');
        $amount = (float)($input['amount'] ?? 0);
        $method = trim($input['method'] ?? 'FPX');
        $cardSerial = trim($input['card_serial'] ?? '');

        if (empty($email) || $amount <= 0) {
            sendError('Invalid parameters.');
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            sendError('User not found.');
        }

        $cards = [];
        if (!empty($user['cards_json'])) {
            $cards = json_decode($user['cards_json'], true);
        }
        if (empty($cards) && (!empty($user['card_serial']) || !empty($user['studentId']))) {
            $cards = [
                [
                    'card_serial' => $user['card_serial'],
                    'student_name' => $user['child'],
                    'student_id' => $user['studentId'],
                    'class' => $user['childClass'],
                    'balance' => (float)$user['balance'],
                    'daily_limit' => (float)$user['daily_limit'],
                    'status' => $user['status'] ?? 'active'
                ]
            ];
        }

        if (empty($cards)) {
            sendError('Please register a student card before topping up.');
        }

        // Add to targeted card's balance
        $found = false;
        $targetCardName = 'Child Wallet';
        $newCardBalance = 0;
        foreach ($cards as &$c) {
            if ($c['card_serial'] === $cardSerial || (empty($cardSerial) && count($cards) > 0)) {
                $c['balance'] = (float)$c['balance'] + $amount;
                $newCardBalance = $c['balance'];
                $targetCardName = $c['student_name'];
                $found = true;
                break;
            }
        }

        $newBalance = (float)$user['balance'] + $amount;
        $newTopupTotal = (float)$user['topupTotal'] + $amount;
        $newTopupCount = (int)$user['topupCount'] + 1;

        // Sync main column for single-card backwards-compatibility
        $syncBalance = (count($cards) === 1) ? $newCardBalance : $newBalance;

        // Update both student_cards table and payments table (D2: Payment File)
        try {
            // Update individual card balance
            $stmtCardUp = $pdo->prepare("UPDATE student_cards SET balance = balance + ? WHERE card_serial = ?");
            $stmtCardUp->execute([$amount, $cardSerial]);

            // Save record to payments table (D2: Payment File)
            $dateStrNow = date('Y-m-d H:i');
            $stmtPay = $pdo->prepare("INSERT INTO payments (userId, card_serial, amount, method, date, status) VALUES (?, ?, ?, ?, ?, 'Completed')");
            $stmtPay->execute([$user['id'], $cardSerial, $amount, $method, $dateStrNow]);
        } catch (\Exception $e) {
            // Silently ignore if tables don't exist
        }

        $stmtUp = $pdo->prepare("UPDATE users SET balance = ?, topupTotal = ?, topupCount = ?, cards_json = ? WHERE id = ?");
        $stmtUp->execute([$syncBalance, $newTopupTotal, $newTopupCount, json_encode($cards), $user['id']]);

        // Create transaction entry
        $dateStr = date('Y-m-d H:i');
        $timeStr = date('g:i A');
        
        $desc = "Top Up RM " . number_format($amount, 2) . " for " . $targetCardName . " via " . $method;
        $title = "Top Up (" . $targetCardName . ")";
        $sub = (strpos($method, 'Bank') !== false || strpos($method, 'Maybank') !== false || strpos($method, 'CIMB') !== false) 
            ? "Online Banking · " . $timeStr 
            : "Card · " . $timeStr;

        $stmtTx = $pdo->prepare("INSERT INTO transactions (userId, description, amount, date, type, icon, cat, title, sub) VALUES (?, ?, ?, ?, 'topup', '⬆️', 'topup', ?, ?)");
        $stmtTx->execute([$user['id'], $desc, $amount, $dateStr, $title, $sub]);

        // Get updated user data
        $stmtGet = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmtGet->execute([$user['id']]);
        $updatedUser = hydrateUser($stmtGet->fetch());

        // Get user transactions
        $stmtTList = $pdo->prepare("SELECT * FROM transactions WHERE userId = ? ORDER BY date DESC");
        $stmtTList->execute([$user['id']]);
        $txns = $stmtTList->fetchAll();
        foreach ($txns as &$tx) {
            $tx['id'] = (int)$tx['id'];
            $tx['userId'] = (int)$tx['userId'];
            $tx['amount'] = (float)$tx['amount'];
        }

        sendSuccess(['user' => $updatedUser, 'transactions' => $txns]);
        break;

    // ── 6. CREATE SUPPORT COMPLAINT REPORT ──
    case 'create-report':
        $input = getJsonInput();
        $email = trim($input['email'] ?? '');
        $type = trim($input['type'] ?? 'other');
        $subject = trim($input['subject'] ?? '');
        $description = trim($input['description'] ?? '');

        if (empty($email) || empty($subject) || empty($description)) {
            sendError('Please fill up all details.');
        }

        $stmt = $pdo->prepare("SELECT name, child FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            sendError('User not found.');
        }

        $dateStr = date('Y-m-d H:i');

        $stmtRep = $pdo->prepare("INSERT INTO reports (reporterName, child, type, subject, description, status, createdAt) VALUES (?, ?, ?, ?, ?, 'Open', ?)");
        $stmtRep->execute([$user['name'], $user['child'], $type, $subject, $description, $dateStr]);

        // Retrieve list of reports for user
        $stmtList = $pdo->prepare("SELECT * FROM reports WHERE reporterName = ? ORDER BY createdAt DESC");
        $stmtList->execute([$user['name']]);
        $reports = $stmtList->fetchAll();
        foreach ($reports as &$rep) {
            $rep['id'] = (int)$rep['id'];
        }

        sendSuccess(['reports' => $reports]);
        break;

    // ── 7. DELETE REPORT ──
    case 'delete-report':
        $input = getJsonInput();
        $id = (int)($input['id'] ?? 0);

        if ($id <= 0) {
            sendError('Invalid report ID.');
        }

        $stmtDel = $pdo->prepare("DELETE FROM reports WHERE id = ?");
        $stmtDel->execute([$id]);

        sendSuccess();
        break;

    // ── 8. ADMIN: GET ALL SYSTEM DATA ──
    case 'admin-data':
        // Fetch all users (excluding admin itself)
        $stmtUsers = $pdo->query("SELECT * FROM users WHERE role != 'admin' ORDER BY id ASC");
        $users = $stmtUsers->fetchAll();
        foreach ($users as &$u) {
            $u['id'] = (int)$u['id'];
            $u['balance'] = (float)$u['balance'];
            $u['daily_limit'] = (float)$u['daily_limit'];
            $u['topupTotal'] = (float)$u['topupTotal'];
            $u['topupCount'] = (int)$u['topupCount'];
        }

        // Fetch all transactions
        $stmtTxns = $pdo->query("SELECT t.*, u.name as userName FROM transactions t JOIN users u ON t.userId = u.id ORDER BY t.date DESC");
        $txns = $stmtTxns->fetchAll();
        foreach ($txns as &$tx) {
            $tx['id'] = (int)$tx['id'];
            $tx['userId'] = (int)$tx['userId'];
            $tx['amount'] = (float)$tx['amount'];
        }

        // Fetch all reports
        $stmtReps = $pdo->query("SELECT * FROM reports ORDER BY createdAt DESC");
        $reps = $stmtReps->fetchAll();
        foreach ($reps as &$r) {
            $r['id'] = (int)$r['id'];
        }

        sendSuccess([
            'users' => $users,
            'transactions' => $txns,
            'reports' => $reps
        ]);
        break;

    // ── 9. ADMIN: SAVE USER (CREATE/UPDATE) ──
    case 'admin-save-user':
        $input = getJsonInput();
        $crudAction = $input['action'] ?? 'create';
        $id = (int)($input['id'] ?? 0);
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $child = trim($input['child'] ?? '');
        $balance = (float)($input['balance'] ?? 0);
        $status = trim($input['status'] ?? 'active');

        if (empty($name) || empty($email) || empty($child)) {
            sendError('Name, Email, and Child Name are required.');
        }

        if ($crudAction === 'create') {
            // Check email uniqueness
            $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?)");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                sendError('Email address is already in use.');
            }

            $studentId = 'PG-' . rand(10000, 99999);
            $topupTotal = $balance > 0 ? $balance : 0.00;
            $topupCount = $balance > 0 ? 1 : 0;

            $stmtIns = $pdo->prepare("INSERT INTO users (name, email, phone, child, childClass, studentId, balance, daily_limit, status, password, role, topupTotal, topupCount) VALUES (?, ?, ?, ?, '4 Amanah', ?, ?, 50.00, ?, 'password123', 'parent', ?, ?)");
            $stmtIns->execute([$name, $email, $phone, $child, $studentId, $balance, $status, $topupTotal, $topupCount]);
        } else {
            if ($id <= 0) sendError('Invalid user ID for update.');
            
            // Check email uniqueness excluding self
            $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?) AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                sendError('Email address is already in use by another account.');
            }

            $stmtUp = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, child = ?, balance = ?, status = ? WHERE id = ?");
            $stmtUp->execute([$name, $email, $phone, $child, $balance, $status, $id]);
        }

        sendSuccess();
        break;

    // ── 10. ADMIN: DELETE USER ──
    case 'admin-delete-user':
        $input = getJsonInput();
        $id = (int)($input['id'] ?? 0);

        if ($id <= 0) sendError('Invalid user ID.');

        $stmtDel = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmtDel->execute([$id]);

        sendSuccess();
        break;

    // ── 11. ADMIN: SAVE TRANSACTION (CREATE/UPDATE) ──
    case 'admin-save-transaction':
        $input = getJsonInput();
        $crudAction = $input['action'] ?? 'create';
        $id = (int)($input['id'] ?? 0);
        $userId = (int)($input['userId'] ?? 0);
        $description = trim($input['description'] ?? '');
        $amount = (float)($input['amount'] ?? 0);
        $type = trim($input['type'] ?? 'spend');
        $date = trim($input['date'] ?? '');

        if ($userId <= 0 || empty($description) || empty($date)) {
            sendError('User, Description, and Date are required.');
        }

        // Adjust amount sign based on type
        $txnAmount = ($type === 'topup') ? abs($amount) : -abs($amount);

        if ($crudAction === 'create') {
            // Check user
            $stmtUser = $pdo->prepare("SELECT id, balance, topupTotal, topupCount FROM users WHERE id = ?");
            $stmtUser->execute([$userId]);
            $user = $stmtUser->fetch();
            if (!$user) sendError('Target user not found.');

            // Insert transaction
            $icon = ($type === 'topup') ? '⬆️' : '🍱';
            $cat = ($type === 'topup') ? 'topup' : 'canteen';
            
            $stmtIns = $pdo->prepare("INSERT INTO transactions (userId, description, amount, date, type, icon, cat, title, sub) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtIns->execute([$userId, $description, $txnAmount, $date, $type, $icon, $cat, $description, $date]);

            // Adjust user balance
            $newBalance = (float)$user['balance'] + $txnAmount;
            if ($type === 'topup') {
                $newTopTotal = (float)$user['topupTotal'] + abs($txnAmount);
                $newTopCount = (int)$user['topupCount'] + 1;
                $stmtUp = $pdo->prepare("UPDATE users SET balance = ?, topupTotal = ?, topupCount = ? WHERE id = ?");
                $stmtUp->execute([$newBalance, $newTopTotal, $newTopCount, $userId]);
            } else {
                $stmtUp = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
                $stmtUp->execute([$newBalance, $userId]);
            }
        } else {
            if ($id <= 0) sendError('Invalid Transaction ID.');

            // Fetch current transaction state to revert balance
            $stmtTx = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
            $stmtTx->execute([$id]);
            $oldTx = $stmtTx->fetch();
            if (!$oldTx) sendError('Transaction not found.');

            // Revert old balance
            $stmtOldUser = $pdo->prepare("SELECT id, balance FROM users WHERE id = ?");
            $stmtOldUser->execute([$oldTx['userId']]);
            $oldUser = $stmtOldUser->fetch();
            if ($oldUser) {
                $revBalance = (float)$oldUser['balance'] - (float)$oldTx['amount'];
                $stmtRev = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
                $stmtRev->execute([$revBalance, $oldTx['userId']]);
            }

            // Update transaction record
            $stmtUp = $pdo->prepare("UPDATE transactions SET userId = ?, description = ?, amount = ?, date = ?, type = ?, title = ?, sub = ? WHERE id = ?");
            $stmtUp->execute([$userId, $description, $txnAmount, $date, $type, $description, $date, $id]);

            // Apply new balance to new/existing user
            $stmtNewUser = $pdo->prepare("SELECT id, balance FROM users WHERE id = ?");
            $stmtNewUser->execute([$userId]);
            $newUser = $stmtNewUser->fetch();
            if ($newUser) {
                $newBal = (float)$newUser['balance'] + $txnAmount;
                $stmtApp = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
                $stmtApp->execute([$newBal, $userId]);
            }
        }

        sendSuccess();
        break;

    // ── 12. ADMIN: DELETE TRANSACTION ──
    case 'admin-delete-transaction':
        $input = getJsonInput();
        $id = (int)($input['id'] ?? 0);

        if ($id <= 0) sendError('Invalid Transaction ID.');

        // Get txn details to revert user balance
        $stmtTx = $pdo->prepare("SELECT userId, amount FROM transactions WHERE id = ?");
        $stmtTx->execute([$id]);
        $tx = $stmtTx->fetch();
        if ($tx) {
            $stmtUser = $pdo->prepare("SELECT id, balance FROM users WHERE id = ?");
            $stmtUser->execute([$tx['userId']]);
            $user = $stmtUser->fetch();
            if ($user) {
                $newBal = (float)$user['balance'] - (float)$tx['amount'];
                $stmtUp = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
                $stmtUp->execute([$newBal, $tx['userId']]);
            }
        }

        // Delete txn
        $stmtDel = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
        $stmtDel->execute([$id]);

        sendSuccess();
        break;

    // ── 13. ADMIN: UPDATE COMPLAINT STATUS ──
    case 'admin-update-report-status':
        $input = getJsonInput();
        $id = (int)($input['id'] ?? 0);
        $status = trim($input['status'] ?? 'Open');

        if ($id <= 0) sendError('Invalid report ID.');

        $stmtUp = $pdo->prepare("UPDATE reports SET status = ? WHERE id = ?");
        $stmtUp->execute([$status, $id]);

        sendSuccess();
        break;

    // ── 14. ADMIN: DELETE REPORT ──
    case 'admin-delete-report':
        $input = getJsonInput();
        $id = (int)($input['id'] ?? 0);

        if ($id <= 0) sendError('Invalid report ID.');

        $stmtDel = $pdo->prepare("DELETE FROM reports WHERE id = ?");
        $stmtDel->execute([$id]);

        sendSuccess();
        break;

    default:
        sendError('Invalid API Action: ' . $action);
        break;
}
?>
