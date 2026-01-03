<?php
include '../includes/functions.php';
requireLogin();

$db = getDB();

$orders = $db->query("SELECT o.*, g.name as game_name FROM orders o JOIN games g ON o.game_id = g.id WHERE o.user_id = {$_SESSION['user_id']} ORDER BY o.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Kim Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.3) 0%, rgba(118, 75, 162, 0.3) 100%) !important; backdrop-filter: blur(15px);">
        <div class="container">
            <a class="navbar-brand" href="../index.php" style="font-weight: 600; color: white !important;">Kim Store</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="shop.php" style="color: rgba(255, 255, 255, 0.9) !important; transition: color 0.3s ease;">Shop</a>
                <a class="nav-link" href="orders.php" style="color: rgba(255, 255, 255, 0.9) !important; transition: color 0.3s ease;">My Orders</a>
                <a class="nav-link" href="../profile.php" style="color: rgba(255, 255, 255, 0.9) !important; transition: color 0.3s ease;">Profile</a>
                <a class="nav-link" href="../logout.php" style="color: rgba(255, 255, 255, 0.9) !important; transition: color 0.3s ease;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 style="color: white; font-weight: 600; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3); text-align: center; margin-bottom: 2rem;">My Orders</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" style="border-radius: 15px; border: none; background: linear-gradient(45deg, #28a745, #20c997); color: white; text-align: center; margin-bottom: 2rem;">Order placed successfully!</div>
        <?php endif; ?>

        <?php if ($orders->num_rows > 0): ?>
            <div class="table-responsive" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 2rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);">
                <table class="table" style="margin: 0;">
                    <thead style="background: linear-gradient(45deg, #667eea, #764ba2); color: white; border-radius: 10px;">
                        <tr>
                            <th style="border: none; padding: 1rem; font-weight: 600;">Order ID</th>
                            <th style="border: none; padding: 1rem; font-weight: 600;">Game</th>
                            <th style="border: none; padding: 1rem; font-weight: 600;">Game User ID</th>
                            <th style="border: none; padding: 1rem; font-weight: 600;">Nickname</th>
                            <th style="border: none; padding: 1rem; font-weight: 600;">Total Price</th>
                            <th style="border: none; padding: 1rem; font-weight: 600;">Status</th>
                            <th style="border: none; padding: 1rem; font-weight: 600;">Order Date</th>
                            <th style="border: none; padding: 1rem; font-weight: 600;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                                <td style="padding: 1rem; vertical-align: middle; font-weight: 600; color: #667eea;">#<?php echo $order['id']; ?></td>
                                <td style="padding: 1rem; vertical-align: middle;"><?php echo $order['game_name']; ?></td>
                                <td style="padding: 1rem; vertical-align: middle;"><?php echo $order['game_user_id']; ?></td>
                                <td style="padding: 1rem; vertical-align: middle;"><?php echo $order['nickname'] ?: '-'; ?></td>
                                <td style="padding: 1rem; vertical-align: middle; font-weight: 600; color: #667eea;">Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></td>
                                <td style="padding: 1rem; vertical-align: middle;">
                                    <span class="badge" style="background: <?php echo $order['status'] === 'completed' ? 'linear-gradient(45deg, #28a745, #20c997)' : ($order['status'] === 'pending' ? 'linear-gradient(45deg, #ffc107, #fd7e14)' : 'linear-gradient(45deg, #6c757d, #495057)'); ?>; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 500;">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; vertical-align: middle;"><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                                <td style="padding: 1rem; vertical-align: middle;">
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderDetailsModal" onclick="showOrderDetails(<?php echo $order['id']; ?>)" style="background: linear-gradient(45deg, #17a2b8, #138496); border: none; border-radius: 20px; padding: 0.5rem 1rem; color: white; font-weight: 500; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(23, 162, 184, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">Details</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 3rem; text-align: center; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);">
                <p style="color: #666; font-size: 1.1rem; margin: 0;">You have no orders yet. <a href="shop.php" style="color: #667eea; text-decoration: none; font-weight: 500;">Start shopping</a></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);">
                <div class="modal-header" style="background: linear-gradient(45deg, #667eea, #764ba2); color: white; border-radius: 20px 20px 0 0; border: none;">
                    <h5 class="modal-title" style="font-weight: 600;">Order Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent" style="padding: 2rem;">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showOrderDetails(orderId) {
            // Show loading message
            document.getElementById('orderDetailsContent').innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading order details...</p></div>';

            // Fetch order details via AJAX
            fetch('order_details.php?order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayOrderDetails(data);
                    } else {
                        document.getElementById('orderDetailsContent').innerHTML = '<div class="alert alert-danger">Error: ' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('orderDetailsContent').innerHTML = '<div class="alert alert-danger">Failed to load order details. Please try again.</div>';
                });
        }

        function displayOrderDetails(data) {
            const order = data.order;
            const items = data.items;
            const payment = data.payment;

            let html = `
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 style="color: #333; font-weight: 600;">Order Information</h6>
                        <p><strong>Order ID:</strong> #${order.id}</p>
                        <p><strong>Game:</strong> ${order.game_name}</p>
                        <p><strong>Game User ID:</strong> ${order.game_user_id}</p>
                        <p><strong>Nickname:</strong> ${order.nickname || '-'}</p>
                        <p><strong>Order Date:</strong> ${new Date(order.created_at).toLocaleString()}</p>
                        <p><strong>Status:</strong> <span class="badge" style="background: ${getStatusColor(order.status)};">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 style="color: #333; font-weight: 600;">Payment Information</h6>
                        <p><strong>Payment Method:</strong> ${payment ? payment.method : 'Not specified'}</p>
                        <p><strong>Payment Status:</strong> ${payment ? payment.status : 'Pending'}</p>
                        <p><strong>Total Amount:</strong> <span style="color: #667eea; font-weight: 600;">Rp ${new Intl.NumberFormat('id-ID').format(order.total_price)}</span></p>
                    </div>
                </div>

                <h6 style="color: #333; font-weight: 600; margin-bottom: 1rem;">Order Items</h6>
                <div class="table-responsive">
                    <table class="table table-sm" style="background: rgba(255, 255, 255, 0.8); border-radius: 10px; overflow: hidden;">
                        <thead style="background: linear-gradient(45deg, #667eea, #764ba2); color: white;">
                            <tr>
                                <th style="border: none; padding: 0.75rem;">Product</th>
                                <th style="border: none; padding: 0.75rem;">Amount</th>
                                <th style="border: none; padding: 0.75rem;">Quantity</th>
                                <th style="border: none; padding: 0.75rem;">Price</th>
                                <th style="border: none; padding: 0.75rem;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>`;

            items.forEach(item => {
                html += `
                            <tr>
                                <td style="padding: 0.75rem; vertical-align: middle;">${item.product_name}</td>
                                <td style="padding: 0.75rem; vertical-align: middle;">${item.amount}</td>
                                <td style="padding: 0.75rem; vertical-align: middle;">${item.quantity}</td>
                                <td style="padding: 0.75rem; vertical-align: middle; font-weight: 600; color: #667eea;">Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</td>
                                <td style="padding: 0.75rem; vertical-align: middle; font-weight: 600; color: #667eea;">Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                            </tr>`;
            });

            html += `
                        </tbody>
                        <tfoot style="background: rgba(0, 0, 0, 0.05);">
                            <tr>
                                <td colspan="4" style="padding: 0.75rem; text-align: right; font-weight: 600;">Total:</td>
                                <td style="padding: 0.75rem; font-weight: 700; color: #667eea;">Rp ${new Intl.NumberFormat('id-ID').format(order.total_price)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>`;

            document.getElementById('orderDetailsContent').innerHTML = html;
        }

        function getStatusColor(status) {
            switch(status) {
                case 'completed': return 'linear-gradient(45deg, #28a745, #20c997)';
                case 'pending': return 'linear-gradient(45deg, #ffc107, #fd7e14)';
                case 'cancelled': return 'linear-gradient(45deg, #dc3545, #fd7e14)';
                default: return 'linear-gradient(45deg, #6c757d, #495057)';
            }
        }
    </script>
</body>
</html>
