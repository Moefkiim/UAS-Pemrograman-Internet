<?php
include 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    // Validation
    $errors = [];
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($password)) $errors[] = "Password is required.";

    if (empty($errors)) {
        $user = getUserByEmail($email);
        if ($user && verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kim Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div style="background: rgba(255, 255, 255, 0.95); border-radius: 15px; padding: 2rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); backdrop-filter: blur(10px);">
                    <h2 class="text-center" style="color: #333; font-weight: 600; margin-bottom: 2rem;">Login</h2>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label" style="font-weight: 500; color: #333;">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required style="border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 1rem; transition: border-color 0.3s ease;">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label" style="font-weight: 500; color: #333;">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required style="border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 1rem; transition: border-color 0.3s ease;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(45deg, #667eea, #764ba2); border: none; border-radius: 25px; padding: 0.75rem 2rem; font-weight: 500; transition: all 0.3s ease; color: white; width: 100%;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(102, 126, 234, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">Login</button>
                </form>
                <p class="mt-3" style="text-align: center; color: #666;">Don't have an account? <a href="register.php" style="color: #667eea; text-decoration: none; font-weight: 500;">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
