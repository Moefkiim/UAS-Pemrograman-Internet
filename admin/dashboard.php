<?php
include '../includes/functions.php';
requireAdmin();

$user = getUserById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kim Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="background: rgba(33, 37, 41, 0.9) !important; backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
        <div class="container">
            <a class="navbar-brand" href="#" style="font-weight: 600; color: white !important;">Admin Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../profile.php" style="color: rgba(255, 255, 255, 0.8) !important; transition: color 0.3s ease;">Profile</a>
                <a class="nav-link" href="../logout.php" style="color: rgba(255, 255, 255, 0.8) !important; transition: color 0.3s ease;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 style="color: white; font-weight: 600; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);">Welcome, <?php echo $user['name']; ?> (Admin)</h1>
        <div class="row">
            <div class="col-md-3">
                <div class="list-group" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 15px; padding: 1rem; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);">
                    <a href="dashboard.php" class="list-group-item list-group-item-action active" style="border-radius: 10px; margin-bottom: 0.5rem; background: linear-gradient(45deg, #667eea, #764ba2); color: white; border: none;">Dashboard</a>
                    <a href="games.php" class="list-group-item list-group-item-action" style="border-radius: 10px; margin-bottom: 0.5rem; background: rgba(255, 255, 255, 0.8); color: #333; border: none; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(102, 126, 234, 0.1)'; this.style.transform='translateX(5px)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)';">Manage Games</a>
                    <a href="products.php" class="list-group-item list-group-item-action" style="border-radius: 10px; margin-bottom: 0.5rem; background: rgba(255, 255, 255, 0.8); color: #333; border: none; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(102, 126, 234, 0.1)'; this.style.transform='translateX(5px)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)';">Manage Products</a>
                    <a href="packages.php" class="list-group-item list-group-item-action" style="border-radius: 10px; margin-bottom: 0.5rem; background: rgba(255, 255, 255, 0.8); color: #333; border: none; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(102, 126, 234, 0.1)'; this.style.transform='translateX(5px)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)';">Manage Packages</a>
                    <a href="orders.php" class="list-group-item list-group-item-action" style="border-radius: 10px; margin-bottom: 0.5rem; background: rgba(255, 255, 255, 0.8); color: #333; border: none; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(102, 126, 234, 0.1)'; this.style.transform='translateX(5px)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)';">Manage Orders</a>
                    <a href="payments.php" class="list-group-item list-group-item-action" style="border-radius: 10px; margin-bottom: 0.5rem; background: rgba(255, 255, 255, 0.8); color: #333; border: none; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(102, 126, 234, 0.1)'; this.style.transform='translateX(5px)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.8)'; this.style.transform='translateX(0)';">Manage Payments</a>
                </div>
            </div>
            <div class="col-md-9">
                <h2 style="color: white; font-weight: 600; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3); margin-bottom: 2rem;">Dashboard Overview</h2>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 15px; border: none; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 35px rgba(0, 0, 0, 0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(0, 0, 0, 0.15)';">
                            <div class="card-body" style="padding: 2rem;">
                                <h5 class="card-title" style="color: #333; font-weight: 600; margin-bottom: 1rem;">Total Games</h5>
                                <p class="card-text" style="font-size: 2rem; font-weight: 700; color: #667eea;">
                                    <?php
                                    $db = getDB();
                                    $result = $db->query("SELECT COUNT(*) as count FROM games");
                                    echo $result->fetch_assoc()['count'];
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 15px; border: none; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 35px rgba(0, 0, 0, 0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(0, 0, 0, 0.15)';">
                            <div class="card-body" style="padding: 2rem;">
                                <h5 class="card-title" style="color: #333; font-weight: 600; margin-bottom: 1rem;">Total Products</h5>
                                <p class="card-text" style="font-size: 2rem; font-weight: 700; color: #667eea;">
                                    <?php
                                    $result = $db->query("SELECT COUNT(*) as count FROM digital_products");
                                    echo $result->fetch_assoc()['count'];
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 15px; border: none; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 35px rgba(0, 0, 0, 0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(0, 0, 0, 0.15)';">
                            <div class="card-body" style="padding: 2rem;">
                                <h5 class="card-title" style="color: #333; font-weight: 600; margin-bottom: 1rem;">Total Orders</h5>
                                <p class="card-text" style="font-size: 2rem; font-weight: 700; color: #667eea;">
                                    <?php
                                    $result = $db->query("SELECT COUNT(*) as count FROM orders");
                                    echo $result->fetch_assoc()['count'];
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
