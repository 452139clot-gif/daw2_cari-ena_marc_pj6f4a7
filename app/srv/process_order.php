<?php
// api/process_order.php
header('Content-Type: application/json; charset=utf-8');

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use POST.']);
    exit;
}

// Read JSON body
$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON body.']);
    exit;
}

// Basic server-side validation & sanitize
$order_code = isset($data['order_code']) ? trim($data['order_code']) : '';
$full_name  = isset($data['full_name']) ? trim($data['full_name']) : '';
$email      = isset($data['email']) ? filter_var($data['email'], FILTER_SANITIZE_EMAIL) : '';
$address    = isset($data['address']) ? trim($data['address']) : '';
$phone      = isset($data['phone']) ? trim($data['phone']) : '';
$items      = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];

if ($order_code === '' || $full_name === '' || $email === '' || count($items) === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields (order_code, full_name, email, items).']);
    exit;
}

// Validate email lightly
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

// compute subtotal
$subtotal = 0.0;
$clean_items = [];
foreach ($items as $it) {
    $name = isset($it['name']) ? substr(trim($it['name']),0,200) : 'Unknown';
    $price = isset($it['price']) ? floatval($it['price']) : 0.0;
    $qty = isset($it['quantity']) ? intval($it['quantity']) : 0;
    if ($qty <= 0 || $price < 0) continue;
    $line_total = $price * $qty;
    $subtotal += $line_total;
    $clean_items[] = [
        'name' => $name,
        'price' => round($price, 2),
        'quantity' => $qty,
        'line_total' => round($line_total, 2)
    ];
}

if (count($clean_items) === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No valid items to process.']);
    exit;
}

// VAT 21%
$vat_rate = 0.21;
$total_with_vat = $subtotal * (1 + $vat_rate);

// Format numbers to 2 decimals
$subtotal = round($subtotal, 2);
$total_with_vat = round($total_with_vat, 2);

// Prepare DB (SQLite)
try {
    $dbPath = __DIR__ . '/../onlineOrders.db'; // one level up from api/
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create table if not exists
    $createSQL = "
    CREATE TABLE IF NOT EXISTS orders (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      order_code TEXT,
      full_name TEXT,
      email TEXT,
      address TEXT,
      phone TEXT,
      items_json TEXT,
      subtotal REAL,
      total_with_vat REAL,
      vat_rate REAL,
      created_at TEXT
    );";
    $pdo->exec($createSQL);

    // Insert the order
    $stmt = $pdo->prepare('INSERT INTO orders (order_code, full_name, email, address, phone, items_json, subtotal, total_with_vat, vat_rate, created_at) VALUES (:order_code, :full_name, :email, :address, :phone, :items_json, :subtotal, :total_with_vat, :vat_rate, :created_at)');
    $stmt->execute([
        ':order_code' => $order_code,
        ':full_name' => $full_name,
        ':email' => $email,
        ':address' => $address,
        ':phone' => $phone,
        ':items_json' => json_encode($clean_items, JSON_UNESCAPED_UNICODE),
        ':subtotal' => $subtotal,
        ':total_with_vat' => $total_with_vat,
        ':vat_rate' => $vat_rate,
        ':created_at' => date('c')
    ]);

    $orderId = $pdo->lastInsertId();

    $formatted_total = sprintf('Total with VAT (21%%): € %.2f', $total_with_vat);
    $message = "Order saved (ID: {$orderId}, Code: {$order_code}). Subtotal: € " . number_format($subtotal, 2) . ". " . $formatted_total;

    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'formatted_total' => $formatted_total,
        'message' => $message
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}
