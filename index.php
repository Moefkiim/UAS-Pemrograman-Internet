<?php
include 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kim Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.3) 0%, rgba(118, 75, 162, 0.3) 100%) !important; backdrop-filter: blur(15px);">
        <div class="container">
            <a class="navbar-brand" href="index.php" style="font-weight: 600; color: white !important;">Kim Store</a>
            <div class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a class="nav-link" href="admin/dashboard.php" style="color: rgba(255, 255, 255, 0.9) !important; transition: color 0.3s ease;">Admin Dashboard</a>
                    <?php else: ?>
                        <a class="nav-link" href="user/shop.php" style="color: rgba(255, 255, 255, 0.9) !important; transition: color 0.3s ease;">Shop</a>
                        <a class="nav-link" href="user/orders.php" style="color: rgba(255, 255, 255, 0.9) !important; transition: color 0.3s ease;">My Orders</a>
                    <?php endif; ?>
                    <a class="nav-link" href="profile.php" style="color: rgba(255, 255, 255, 0.9) !important; transition: color 0.3s ease;">Profile</a>
                    <a class="nav-link" href="logout.php" style="color: rgba(255, 255, 255, 0.9) !important; transition: color 0.3s ease;">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="login.php" style="color: rgba(255, 255, 255, 0.9) !important; transition: color 0.3s ease;">Login</a>
                    <a class="nav-link" href="register.php" style="color: rgba(255, 255, 255, 0.9) !important; transition: color 0.3s ease;">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="jumbotron" style="background: rgba(255, 255, 255, 0.9); border-radius: 15px; padding: 3rem; margin-bottom: 2rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); backdrop-filter: blur(10px);">
            <h1 class="display-4">Welcome to Kim Store</h1>
            <p class="lead">Top up your favorite games instantly!</p>
            <?php if (!isLoggedIn()): ?>
                <a class="btn btn-primary btn-lg" href="register.php" role="button" style="background: linear-gradient(45deg, #667eea, #764ba2); border: none; border-radius: 25px; padding: 0.75rem 2rem; font-weight: 500; transition: all 0.3s ease; color: white;">Get Started</a>
            <?php else: ?>
                <?php if (!isAdmin()): ?>
                    <a class="btn btn-primary btn-lg" href="user/shop.php" role="button" style="background: linear-gradient(45deg, #667eea, #764ba2); border: none; border-radius: 25px; padding: 0.75rem 2rem; font-weight: 500; transition: all 0.3s ease; color: white;">Start Shopping</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="row">
            <?php
            $db = getDB();
            $games = $db->query("SELECT * FROM games LIMIT 6");
            while ($game = $games->fetch_assoc()):
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card" style="border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease, box-shadow 0.3s ease; overflow: hidden; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); height: 100%;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 40px rgba(0, 0, 0, 0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(0, 0, 0, 0.1)';">
                        <?php if ($game['image']): ?>
                            <div style="height: 160px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: linear-gradient(45deg, #f8f9fa, #e9ecef);">
                                <img src="<?php echo $game['image']; ?>" class="card-img-top" alt="<?php echo $game['name']; ?>" style="max-height: 140px; max-width: 100%; object-fit: contain; border-radius: 10px;">
                            </div>
                        <?php endif; ?>
                        <div class="card-body" style="padding: 1rem; flex-grow: 1; display: flex; flex-direction: column;">
                            <h6 class="card-title" style="color: #333; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem;"><?php echo $game['name']; ?></h6>
                            <p class="card-text" style="color: #666; font-size: 0.8rem; margin-bottom: 1rem;">Publisher: <?php echo $game['publisher']; ?></p>
                            <?php if (isLoggedIn() && !isAdmin()): ?>
                                <a href="user/shop.php?game_id=<?php echo $game['id']; ?>" class="btn btn-primary btn-sm mt-auto" style="background: linear-gradient(45deg, #667eea, #764ba2); border: none; border-radius: 20px; padding: 0.5rem 1rem; font-weight: 500; transition: all 0.3s ease; color: white; font-size: 0.85rem;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(102, 126, 234, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">Shop Now</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
