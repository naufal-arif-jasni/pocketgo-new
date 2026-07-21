<?php
// process.php - Secure AJAX/Fetch backend for RFID UID scanning processing
// Handles JT308 or similar USB HID Keyboard RFID Reader inputs

// Return pure JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Require database configuration
require_once 'db_conn.php';

// Sanitize inputs helper
function sanitizeUid($uid) {
    // Keep only alphanumeric characters and remove leading/trailing whitespaces
    $clean = preg_replace('/[^A-Za-z0-9]/', '', $uid);
    return trim($clean);
}

// Check if request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Read raw body stream for JSON payload (common in fetch/AJAX)
    $rawData = file_get_contents('php://input');
    $jsonData = json_decode($rawData, true);
    
    // Fetch card_uid from JSON or standard URL-encoded form POST
    $cardUidRaw = '';
    if (isset($jsonData['card_uid'])) {
        $cardUidRaw = $jsonData['card_uid'];
    } elseif (isset($_POST['card_uid'])) {
        $cardUidRaw = $_POST['card_uid'];
    }
    
    // Format and sanitize raw UID input
    $cardUid = sanitizeUid($cardUidRaw);
    
    // Error out if UID is blank
    if (empty($cardUid)) {
        echo json_encode([
            'success' => false,
            'message' => 'No valid card UID scanned. Please tap the card again.'
        ]);
        exit();
    }
    
    // Initialize default response payload
    $response = [
        'success' => true,
        'uid' => $cardUid,
        'matched' => false,
        'message' => 'Card scanned successfully.',
        'card_details' => null
    ];
    
    // ── REAL DATABASE ENGINE LOOKUP & SEPARATION ──
    try {
        if (isset($pdo)) {
            // Attempt 1: Query the normalized separated relational tables
            // This pulls details directly from student_cards linked to students and their parent user account
            $stmt = $pdo->prepare("
                SELECT sc.card_serial, sc.balance, sc.daily_limit, sc.status,
                       s.name AS student_name, s.student_id, s.class,
                       u.name AS parent_name
                FROM student_cards sc
                JOIN students s ON sc.student_id = s.student_id
                JOIN users u ON s.userId = u.id
                WHERE sc.card_serial = :uid OR sc.card_serial = :padded_uid
                LIMIT 1
            ");
            
            // Format fallback for 10-digit zero padding (many RFID readers output standard 10-digit serials)
            $paddedUid = str_pad($cardUid, 10, "0", STR_PAD_LEFT);
            
            $stmt->execute([
                ':uid' => $cardUid,
                ':padded_uid' => $paddedUid
            ]);
            
            $card = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($card) {
                $response['matched'] = true;
                $response['message'] = 'Student card scanned and matched!';
                $response['card_details'] = [
                    'student_name' => $card['student_name'],
                    'student_id' => $card['student_id'],
                    'class' => $card['class'],
                    'balance' => number_format((float)$card['balance'], 2),
                    'daily_limit' => number_format((float)$card['daily_limit'], 2),
                    'status' => ucfirst($card['status']),
                    'parent_name' => $card['parent_name']
                ];
                
                // ── [PLACEHOLDER 1] LOG SWIPE TIMESTAMP ──
                // You can log this card scan to an auditing or real-time event logging table
                /*
                $logTime = date('Y-m-d H:i:s');
                $logStmt = $pdo->prepare("INSERT INTO rfid_logs (card_serial, scanned_at, action) VALUES (?, ?, 'terminal_swipe')");
                $logStmt->execute([$card['card_serial'], $logTime]);
                */
                
                // ── [PLACEHOLDER 2] PROCESS TRANSACTION DEBIT (OPTIONAL CANTEEN PAY) ──
                // If you are using this screen as a payment register terminal, trigger purchase debits here
                /*
                $amountToCharge = 3.50; // Example cost of a lunch set
                if ($card['balance'] >= $amountToCharge) {
                     $newBal = $card['balance'] - $amountToCharge;
                     $updateBal = $pdo->prepare("UPDATE student_cards SET balance = ? WHERE card_serial = ?");
                     $updateBal->execute([$newBal, $card['card_serial']]);
                }
                */
                
            } else {
                // Attempt 2: Backward-compatible fallback lookup directly in the legacy parent 'users' table columns
                $stmtFallback = $pdo->prepare("
                    SELECT name, child, childClass, studentId, balance, daily_limit, status, card_serial
                    FROM users
                    WHERE card_serial = :uid OR card_serial = :padded_uid
                    LIMIT 1
                ");
                $stmtFallback->execute([
                    ':uid' => $cardUid,
                    ':padded_uid' => $paddedUid
                ]);
                
                $user = $stmtFallback->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    $response['matched'] = true;
                    $response['message'] = 'Card matched on legacy parent columns!';
                    $response['card_details'] = [
                        'student_name' => !empty($user['child']) ? $user['child'] : $user['name'],
                        'student_id' => !empty($user['studentId']) ? $user['studentId'] : 'PG-LEGACY',
                        'class' => !empty($user['childClass']) ? $user['childClass'] : 'N/A',
                        'balance' => number_format((float)$user['balance'], 2),
                        'daily_limit' => number_format((float)$user['daily_limit'], 2),
                        'status' => ucfirst($user['status']),
                        'parent_name' => $user['name']
                    ];
                } else {
                    // Scenario: Unregistered Card Scanned
                    $response['matched'] = false;
                    $response['message'] = 'Card scanned but serial UID is unregistered.';
                    $response['card_details'] = [
                        'student_name' => 'Unregistered Chip',
                        'student_id' => 'N/A',
                        'class' => 'N/A',
                        'balance' => '0.00',
                        'daily_limit' => '0.00',
                        'status' => 'Unknown',
                        'parent_name' => 'N/A'
                    ];
                }
            }
        } else {
            throw new Exception("PDO database connection handle is not defined.");
        }
    } catch (\Exception $e) {
        $response['success'] = false;
        $response['message'] = 'Database processing error: ' . $e->getMessage();
    }
    
    // Output final structured JSON
    echo json_encode($response);
    exit();

} else {
    // Redirect direct GET hits to scan.php
    header("Location: scan.php");
    exit();
}
?>
