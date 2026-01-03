<?php
include '../includes/functions.php';
requireLogin();

$db = getDB();

// Get direct package_id
$direct_package_id = isset($_GET['package_id']) ? (int)$_GET['package_id'] : null;
if (!$direct_package_id) {
    header('Location: shop.php');
    exit();
}

$message = '';
$errors = [];

// Get item details
$cart_items = [];
$total = 0;

$stmt = $db->prepare("SELECT pp.id, pp.amount, pp.price, dp.id as product_id, dp.name as product_name, dp.game_id, g.name as game_name FROM product_packages pp JOIN digital_products dp ON pp.product_id = dp.id JOIN games g ON dp.game_id = g.id WHERE pp.id = ?");
$stmt->bind_param("i", $direct_package_id);
$stmt->execute();
$result = $stmt->get_result();
if ($item = $result->fetch_assoc()) {
    $item['quantity'] = 1;
    $item['subtotal'] = $item['price'];
    $cart_items[] = $item;
    $total = $item['subtotal'];
} else {
    header('Location: shop.php');
    exit();
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $game_user_id = sanitize($_POST['game_user_id']);
    $nickname = sanitize($_POST['nickname']);
    $payment_method = sanitize($_POST['payment_method']);

    // Validation
    if (empty($game_user_id)) $errors[] = "Game User ID is required.";
    if (empty($payment_method)) $errors[] = "Payment method is required.";

    if (empty($errors)) {
        // Get first game_id (assuming all items are for the same game)
        $game_id = $cart_items[0]['game_id'];

        // Create order
        $stmt = $db->prepare("INSERT INTO orders (user_id, game_id, game_user_id, nickname, total_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissd", $_SESSION['user_id'], $game_id, $game_user_id, $nickname, $total);
        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;

            // Insert order items
            foreach ($cart_items as $item) {
                $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, package_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiiid", $order_id, $item['product_id'], $item['id'], $item['quantity'], $item['price']);
                $stmt->execute();
            }

            // Create payment
            $stmt = $db->prepare("INSERT INTO payments (order_id, method) VALUES (?, ?)");
            $stmt->bind_param("is", $order_id, $payment_method);
            $stmt->execute();

            // Redirect to order success
            header('Location: orders.php?success=1');
            exit();
        } else {
            $errors[] = "Failed to create order.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Kim Store</title>
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
        <h1 style="color: white; font-weight: 600; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3); text-align: center; margin-bottom: 2rem;">Checkout</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" style="border-radius: 15px; border: none; background: linear-gradient(45deg, #dc3545, #fd7e14); color: white; margin-bottom: 2rem;">
                <ul style="margin: 0; padding-left: 1rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);">
                    <h3 style="color: #333; font-weight: 600; margin-bottom: 1.5rem;">Order Summary</h3>
                    <div style="overflow-x: auto;">
                        <table class="table" style="margin: 0;">
                            <thead style="background: linear-gradient(45deg, #667eea, #764ba2); color: white; border-radius: 10px;">
                                <tr>
                                    <th style="border: none; padding: 1rem; font-weight: 600;">Game</th>
                                    <th style="border: none; padding: 1rem; font-weight: 600;">Product</th>
                                    <th style="border: none; padding: 1rem; font-weight: 600;">Amount</th>
                                    <th style="border: none; padding: 1rem; font-weight: 600;">Quantity</th>
                                    <th style="border: none; padding: 1rem; font-weight: 600;">Price</th>
                                    <th style="border: none; padding: 1rem; font-weight: 600;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                                        <td style="padding: 1rem; vertical-align: middle;"><?php echo $item['game_name']; ?></td>
                                        <td style="padding: 1rem; vertical-align: middle;"><?php echo $item['product_name']; ?></td>
                                        <td style="padding: 1rem; vertical-align: middle;"><?php echo $item['amount']; ?></td>
                                        <td style="padding: 1rem; vertical-align: middle;"><?php echo $item['quantity']; ?></td>
                                        <td style="padding: 1rem; vertical-align: middle; font-weight: 600; color: #667eea;">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                        <td style="padding: 1rem; vertical-align: middle; font-weight: 600; color: #667eea;">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot style="background: rgba(0, 0, 0, 0.05); border-radius: 0 0 10px 10px;">
                                <tr>
                                    <td colspan="5" style="padding: 1rem; text-align: right; font-weight: 700; color: #333; border: none;">Total:</td>
                                    <td style="padding: 1rem; font-weight: 700; color: #667eea; font-size: 1.1rem; border: none;">Rp <?php echo number_format($total, 0, ',', '.'); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 2rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);">
                    <h3 style="color: #333; font-weight: 600; margin-bottom: 1.5rem;">Checkout Details</h3>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="game_user_id" class="form-label" style="font-weight: 500; color: #333;">Game User ID</label>
                            <input type="text" class="form-control" id="game_user_id" name="game_user_id" required style="border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 1rem; transition: border-color 0.3s ease;">
                            <div class="form-text" style="color: #666; font-size: 0.875rem;">Enter your in-game user ID.</div>
                        </div>
                        <div class="mb-3">
                            <label for="nickname" class="form-label" style="font-weight: 500; color: #333;">Nickname (optional)</label>
                            <input type="text" class="form-control" id="nickname" name="nickname" style="border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 1rem; transition: border-color 0.3s ease;">
                        </div>
                        <div class="mb-3">
                            <label for="payment_method" class="form-label" style="font-weight: 500; color: #333;">Payment Method</label>
                            <select class="form-control" id="payment_method" name="payment_method" required style="border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 1rem; transition: border-color 0.3s ease;">
                                <option value="">Select Payment Method</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="E-Wallet">E-Wallet</option>
                                <option value="Credit Card">Credit Card</option>
                            </select>
                        </div>
                        <button type="submit" name="checkout" class="btn btn-success btn-lg w-100" style="background: linear-gradient(45deg, #28a745, #20c997); border: none; border-radius: 25px; padding: 1rem; font-weight: 600; font-size: 1.1rem; transition: all 0.3s ease; color: white;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(40, 167, 69, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">Place Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
