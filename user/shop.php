<?php
include '../includes/functions.php';
requireLogin();

$db = getDB();

$game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : null;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

if ($game_id) {
    $stmt = $db->prepare("SELECT * FROM games WHERE id = ?");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $game = $stmt->get_result()->fetch_assoc();
    if (!$game) {
        header('Location: shop.php');
        exit();
    }
    
    if ($search) {
        $search_param = "%$search%";
        $stmt = $db->prepare("SELECT dp.*, pp.id as package_id, pp.amount, pp.price as package_price FROM digital_products dp LEFT JOIN product_packages pp ON dp.id = pp.product_id WHERE dp.game_id = ? AND (dp.name LIKE ? OR pp.amount LIKE ?) ORDER BY dp.name, pp.amount");
        $stmt->bind_param("iss", $game_id, $search_param, $search_param);
    } else {
        $stmt = $db->prepare("SELECT dp.*, pp.id as package_id, pp.amount, pp.price as package_price FROM digital_products dp LEFT JOIN product_packages pp ON dp.id = pp.product_id WHERE dp.game_id = ? ORDER BY dp.name, pp.amount");
        $stmt->bind_param("i", $game_id);
    }
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    if ($search) {
        $search_param = "%$search%";
        $stmt = $db->prepare("SELECT * FROM games WHERE name LIKE ? ORDER BY name");
        $stmt->bind_param("s", $search_param);
    } else {
        $stmt = $db->prepare("SELECT * FROM games ORDER BY name");
    }
    $stmt->execute();
    $games = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $game_id ? $game['name'] . ' - Shop' : 'Shop'; ?> - Kim Store</title>
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
        <form method="GET" class="mb-4">
            <div class="input-group" style="max-width: 500px; margin: 0 auto;">
                <input type="text" class="form-control" name="search" placeholder="Search games or products..." value="<?php echo htmlspecialchars($search); ?>" style="border-radius: 25px 0 0 25px; border: 1px solid rgba(255, 255, 255, 0.2); padding: 0.75rem 1rem; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);">
                <?php if ($game_id): ?>
                    <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">
                <?php endif; ?>
                <button class="btn btn-outline-secondary" type="submit" style="border-radius: 0 25px 25px 0; border: 1px solid rgba(255, 255, 255, 0.2); background: linear-gradient(45deg, #667eea, #764ba2); color: white; border: none; padding: 0.75rem 1.5rem; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(102, 126, 234, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">Search</button>
            </div>
        </form>

        <?php if ($game_id): ?>
            <h1 style="color: white; font-weight: 600; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3); text-align: center; margin-bottom: 2rem;"><?php echo $game['name']; ?> Top-Up</h1>
            <div style="text-align: center; margin-bottom: 2rem;">
                <a href="shop.php" class="btn btn-secondary" style="background: rgba(108, 117, 125, 0.9); backdrop-filter: blur(10px); border: none; border-radius: 25px; padding: 0.75rem 2rem; color: white; text-decoration: none; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(108, 117, 125, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">Back to Games</a>
            </div>

            <div class="row">
                <?php
                $current_product = null;
                while ($product = $products->fetch_assoc()):
                    if ($current_product != $product['id']):
                        if ($current_product !== null) echo '</tbody></table></div></div>';
                        $current_product = $product['id'];
                ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); transition: all 0.3s ease; height: 100%;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 40px rgba(0, 0, 0, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(0, 0, 0, 0.2)';">
                            <div class="card-header" style="background: linear-gradient(45deg, #667eea, #764ba2); color: white; border-radius: 20px 20px 0 0; border: none; padding: 1.5rem;">
                                <h5 class="card-title" style="margin: 0; font-weight: 600;"><?php echo $product['name']; ?></h5>
                                <?php if ($product['image']): ?>
                                    <div style="height: 120px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.9); border-radius: 10px; margin-top: 1rem;">
                                        <img src="../<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>" style="max-height: 100px; max-width: 100%; object-fit: contain; border-radius: 8px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body" style="padding: 1.5rem; flex-grow: 1; display: flex; flex-direction: column;">
                                <table class="table table-sm" style="margin: 0;">
                                    <thead>
                                        <tr style="border-bottom: 2px solid #dee2e6;">
                                            <th style="border: none; font-weight: 600; color: #333; font-size: 0.85rem;">Amount</th>
                                            <th style="border: none; font-weight: 600; color: #333; font-size: 0.85rem;">Price</th>
                                            <th style="border: none; font-weight: 600; color: #333; font-size: 0.85rem;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                <?php endif; ?>
                                        <tr style="border: none;">
                                            <td style="border: none; vertical-align: middle; font-size: 0.9rem;"><?php echo $product['amount']; ?></td>
                                            <td style="border: none; vertical-align: middle; font-weight: 600; color: #667eea; font-size: 0.9rem;">Rp <?php echo number_format($product['package_price'], 0, ',', '.'); ?></td>
                                            <td style="border: none; vertical-align: middle;">
                                                <a href="checkout.php?package_id=<?php echo $product['package_id']; ?>" class="btn btn-sm btn-success" style="background: linear-gradient(45deg, #28a745, #20c997); border: none; border-radius: 20px; padding: 0.4rem 0.8rem; font-weight: 500; transition: all 0.3s ease; font-size: 0.8rem;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(40, 167, 69, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">Buy Now</a>
                                            </td>
                                        </tr>
                <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php if ($current_product !== null) echo '</tbody></table></div></div>'; ?>
            </div>
        <?php else: ?>
            <h1 style="color: white; font-weight: 600; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3); text-align: center; margin-bottom: 2rem;">Choose a Game</h1>
            <div class="row">
                <?php while ($game = $games->fetch_assoc()): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); transition: all 0.3s ease; overflow: hidden; height: 100%;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 40px rgba(0, 0, 0, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(0, 0, 0, 0.2)';">
                            <?php if ($game['image']): ?>
                                <div style="height: 160px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: linear-gradient(45deg, #f8f9fa, #e9ecef);">
                                    <img src="../<?php echo $game['image']; ?>" class="card-img-top" alt="<?php echo $game['name']; ?>" style="max-height: 140px; max-width: 100%; object-fit: contain; border-radius: 10px;">
                                </div>
                            <?php endif; ?>
                            <div class="card-body" style="padding: 1rem; flex-grow: 1; display: flex; flex-direction: column;">
                                <h6 class="card-title" style="color: #333; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;"><?php echo $game['name']; ?></h6>
                                <p class="card-text" style="color: #666; font-size: 0.8rem; margin-bottom: 1rem;">Publisher: <?php echo $game['publisher']; ?></p>
                                <a href="shop.php?game_id=<?php echo $game['id']; ?>" class="btn btn-primary btn-sm mt-auto" style="background: linear-gradient(45deg, #667eea, #764ba2); border: none; border-radius: 20px; padding: 0.5rem 1rem; font-weight: 500; transition: all 0.3s ease; color: white; font-size: 0.85rem;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(102, 126, 234, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">Shop Now</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
