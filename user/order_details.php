<?php
include '../includes/functions.php';
requireLogin();

// Get order ID from request
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit();
}

$db = getDB();

// Get order details and verify ownership
$stmt = $db->prepare("
    SELECT o.*, g.name as game_name
    FROM orders o
    JOIN games g ON o.game_id = g.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found or access denied']);
    exit();
}

$order = $result->fetch_assoc();

// Get order items
$stmt = $db->prepare("
    SELECT oi.*, dp.name as product_name, pp.amount, (oi.price * oi.quantity) as subtotal
    FROM order_items oi
    JOIN digital_products dp ON oi.product_id = dp.id
    JOIN product_packages pp ON oi.package_id = pp.id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$items = [];

while ($item = $items_result->fetch_assoc()) {
    $items[] = $item;
}

// Get payment information
$stmt = $db->prepare("SELECT * FROM payments WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$payment_result = $stmt->get_result();
$payment = $payment_result->fetch_assoc();

// Return JSON response
echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items,
    'payment' => $payment
]);
?>