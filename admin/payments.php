<?php
include '../includes/functions.php';
requireAdmin();

$db = getDB();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment_status'])) {
    $payment_id = (int)$_POST['payment_id'];
    $status = sanitize($_POST['status']);
    
    // Start transaction
    $db->begin_transaction();
    
    try {
        // Update payment status
        $stmt = $db->prepare("UPDATE payments SET status = ?, paid_at = ? WHERE id = ?");
        $paid_at = ($status === 'paid') ? date('Y-m-d H:i:s') : null;
        $stmt->bind_param("ssi", $status, $paid_at, $payment_id);
        $stmt->execute();
        
        // Get order_id from payment
        $stmt = $db->prepare("SELECT order_id FROM payments WHERE id = ?");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $payment = $result->fetch_assoc();
        $order_id = $payment['order_id'];
        
        if ($status === 'paid') {
            // Update order status to processing
            $stmt = $db->prepare("UPDATE orders SET status = 'processing' WHERE id = ?");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            
            // Get order details for topup log
            $stmt = $db->prepare("
                SELECT o.game_user_id, o.nickname, oi.product_id, oi.package_id, oi.quantity, 
                       dp.name as product_name, pp.amount, g.name as game_name
                FROM orders o 
                JOIN order_items oi ON o.id = oi.order_id 
                JOIN digital_products dp ON oi.product_id = dp.id 
                JOIN product_packages pp ON oi.package_id = pp.id 
                JOIN games g ON o.game_id = g.id 
                WHERE o.id = ?
            ");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $order_items = [];
            while ($item = $result->fetch_assoc()) {
                $order_items[] = $item;
            }
            
            // Create topup request data (simulated API call data)
            $request_data = json_encode([
                'order_id' => $order_id,
                'game_user_id' => $order_items[0]['game_user_id'],
                'nickname' => $order_items[0]['nickname'],
                'game' => $order_items[0]['game_name'],
                'items' => array_map(function($item) {
                    return [
                        'product' => $item['product_name'],
                        'amount' => $item['amount'],
                        'quantity' => $item['quantity']
                    ];
                }, $order_items)
            ]);
            
            // Simulate API response (in real implementation, this would be from actual API call)
            $response_data = json_encode([
                'status' => 'success',
                'message' => 'Topup processed successfully',
                'transaction_id' => 'TXN_' . $order_id . '_' . time()
            ]);
            
            // Insert topup log
            $stmt = $db->prepare("INSERT INTO topup_logs (order_id, request_data, response_data, status) VALUES (?, ?, ?, 'success')");
            $stmt->bind_param("iss", $order_id, $request_data, $response_data);
            $stmt->execute();
            
            // Update order status to completed
            $stmt = $db->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            
        } elseif ($status === 'failed' || $status === 'expired') {
            // Update order status to failed
            $stmt = $db->prepare("UPDATE orders SET status = 'failed' WHERE id = ?");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
        }
        
        $db->commit();
        $message = "Payment status updated successfully.";
        
    } catch (Exception $e) {
        $db->rollback();
        $message = "Error updating payment status: " . $e->getMessage();
    }
}

$payments = $db->query("SELECT p.*, o.id as order_id, u.name as user_name FROM payments p JOIN orders o ON p.order_id = o.id JOIN users u ON o.user_id = u.id ORDER BY p.created_at DESC");
$topup_logs = $db->query("SELECT tl.*, o.id as order_id FROM topup_logs tl JOIN orders o ON tl.order_id = o.id ORDER BY tl.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments & Topup Logs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../profile.php">Profile</a>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Manage Payments & Topup Logs</h1>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <h2>Payments</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Payment Ref</th>
                    <th>Paid At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($payment = $payments->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $payment['id']; ?></td>
                        <td><?php echo $payment['order_id']; ?></td>
                        <td><?php echo $payment['user_name']; ?></td>
                        <td><?php echo $payment['method']; ?></td>
                        <td>
                            <span class="badge bg-<?php echo $payment['status'] === 'paid' ? 'success' : ($payment['status'] === 'unpaid' ? 'warning' : 'secondary'); ?>">
                                <?php echo ucfirst($payment['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $payment['payment_ref'] ?: '-'; ?></td>
                        <td><?php echo $payment['paid_at'] ?: '-'; ?></td>
                        <td>
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#updatePaymentModal" onclick="updatePayment(<?php echo $payment['id']; ?>, '<?php echo $payment['status']; ?>')">Update Status</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2>Topup Logs</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Order ID</th>
                    <th>Status</th>
                    <th>Request Data</th>
                    <th>Response Data</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($log = $topup_logs->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $log['id']; ?></td>
                        <td><?php echo $log['order_id']; ?></td>
                        <td>
                            <span class="badge bg-<?php echo $log['status'] === 'success' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($log['status']); ?>
                            </span>
                        </td>
                        <td><small><?php echo substr($log['request_data'], 0, 100) . (strlen($log['request_data']) > 100 ? '...' : ''); ?></small></td>
                        <td><small><?php echo substr($log['response_data'], 0, 100) . (strlen($log['response_data']) > 100 ? '...' : ''); ?></small></td>
                        <td><?php echo $log['created_at']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Update Payment Modal -->
    <div class="modal fade" id="updatePaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Payment Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="paymentId" name="payment_id">
                        <div class="mb-3">
                            <label for="paymentStatus" class="form-label">Status</label>
                            <select class="form-control" id="paymentStatus" name="status" required>
                                <option value="unpaid">Unpaid</option>
                                <option value="paid">Paid</option>
                                <option value="expired">Expired</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_payment_status" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updatePayment(paymentId, currentStatus) {
            document.getElementById('paymentId').value = paymentId;
            document.getElementById('paymentStatus').value = currentStatus;
        }
    </script>
</body>
</html>
