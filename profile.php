<?php
include 'includes/functions.php';
requireLogin();

$db = getDB();
$user = getUserById($_SESSION['user_id']);

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($name)) $errors[] = "Name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (getUserByEmail($email) && $email !== $user['email']) $errors[] = "Email already exists.";

    $update_password = false;
    if (!empty($new_password)) {
        if (!verifyPassword($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect.";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters.";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match.";
        } else {
            $update_password = true;
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE users SET name = ?, email = ?" . ($update_password ? ", password = ?" : "") . " WHERE id = ?");
        if ($update_password) {
            $hashed_password = hashPassword($new_password);
            $stmt->bind_param("sssi", $name, $email, $hashed_password, $_SESSION['user_id']);
        } else {
            $stmt->bind_param("ssi", $name, $email, $_SESSION['user_id']);
        }
        if ($stmt->execute()) {
            $message = "Profile updated successfully.";
            $user = getUserById($_SESSION['user_id']); // Refresh user data
        } else {
            $errors[] = "Failed to update profile.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Kim Store</title>
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
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 2rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);">
                    <h2 class="text-center" style="color: #333; font-weight: 600; margin-bottom: 1.5rem;">Update Profile</h2>
                    <?php if ($message): ?>
                        <div class="alert alert-success" style="border-radius: 10px; border: none; background: linear-gradient(45deg, #28a745, #20c997); color: white;"><?php echo $message; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" style="border-radius: 10px; border: none; background: linear-gradient(45deg, #dc3545, #fd7e14); color: white;">
                            <ul style="margin: 0; padding-left: 1rem;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label" style="font-weight: 500; color: #333;">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required style="border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 1rem; transition: border-color 0.3s ease;">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label" style="font-weight: 500; color: #333;">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required style="border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 1rem; transition: border-color 0.3s ease;">
                        </div>
                        <hr style="border: none; height: 1px; background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.1), transparent); margin: 2rem 0;">
                        <h5 style="color: #333; font-weight: 600; margin-bottom: 1rem;">Change Password (optional)</h5>
                        <div class="mb-3">
                            <label for="current_password" class="form-label" style="font-weight: 500; color: #333;">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" style="border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 1rem; transition: border-color 0.3s ease;">
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label" style="font-weight: 500; color: #333;">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" style="border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 1rem; transition: border-color 0.3s ease;">
                            <div class="form-text" style="color: #666; font-size: 0.875rem;">Leave blank to keep current password.</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label" style="font-weight: 500; color: #333;">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" style="border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 1rem; transition: border-color 0.3s ease;">
                        </div>
                        <button type="submit" class="btn btn-primary" style="background: linear-gradient(45deg, #667eea, #764ba2); border: none; border-radius: 25px; padding: 0.75rem 2rem; font-weight: 500; transition: all 0.3s ease; color: white; width: 100%;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(102, 126, 234, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
