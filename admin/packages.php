<?php
include '../includes/functions.php';
requireAdmin();

$db = getDB();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $product_id = (int)$_POST['product_id'];
        $amount = sanitize($_POST['amount']);
        $price = (float)$_POST['price'];
        $stmt = $db->prepare("INSERT INTO product_packages (product_id, amount, price) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $product_id, $amount, $price);
        if ($stmt->execute()) {
            $message = "Package added successfully.";
        } else {
            $message = "Error adding package.";
        }
    } elseif (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $product_id = (int)$_POST['product_id'];
        $amount = sanitize($_POST['amount']);
        $price = (float)$_POST['price'];
        $stmt = $db->prepare("UPDATE product_packages SET product_id = ?, amount = ?, price = ? WHERE id = ?");
        $stmt->bind_param("isdi", $product_id, $amount, $price, $id);
        if ($stmt->execute()) {
            $message = "Package updated successfully.";
        } else {
            $message = "Error updating package.";
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM product_packages WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Package deleted successfully.";
        } else {
            $message = "Error deleting package.";
        }
    }
}

$packages = $db->query("SELECT pp.*, dp.name as product_name, g.name as game_name FROM product_packages pp JOIN digital_products dp ON pp.product_id = dp.id JOIN games g ON dp.game_id = g.id ORDER BY pp.amount");
$products = $db->query("SELECT dp.id, dp.name, g.name as game_name FROM digital_products dp JOIN games g ON dp.game_id = g.id ORDER BY g.name, dp.name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages - Admin</title>
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
        <h1>Manage Product Packages</h1>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPackageModal">Add New Package</button>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Game</th>
                    <th>Product</th>
                    <th>Amount</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($package = $packages->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $package['id']; ?></td>
                        <td><?php echo $package['game_name']; ?></td>
                        <td><?php echo $package['product_name']; ?></td>
                        <td><?php echo $package['amount']; ?></td>
                        <td>Rp <?php echo number_format($package['price'], 0, ',', '.'); ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editPackageModal" onclick="editPackage(<?php echo $package['id']; ?>, <?php echo $package['product_id']; ?>, '<?php echo addslashes($package['amount']); ?>', <?php echo $package['price']; ?>)">Edit</button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $package['id']; ?>">
                                <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Package Modal -->
    <div class="modal fade" id="addPackageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product</label>
                            <select class="form-control" id="product_id" name="product_id" required>
                                <option value="">Select Product</option>
                                <?php $products->data_seek(0); while ($product = $products->fetch_assoc()): ?>
                                    <option value="<?php echo $product['id']; ?>"><?php echo $product['game_name'] . ' - ' . $product['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="text" class="form-control" id="amount" name="amount" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add" class="btn btn-primary">Add Package</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Package Modal -->
    <div class="modal fade" id="editPackageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id">
                        <div class="mb-3">
                            <label for="editProductId" class="form-label">Product</label>
                            <select class="form-control" id="editProductId" name="product_id" required>
                                <option value="">Select Product</option>
                                <?php $products->data_seek(0); while ($product = $products->fetch_assoc()): ?>
                                    <option value="<?php echo $product['id']; ?>"><?php echo $product['game_name'] . ' - ' . $product['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editAmount" class="form-label">Amount</label>
                            <input type="text" class="form-control" id="editAmount" name="amount" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPrice" class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" id="editPrice" name="price" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit" class="btn btn-primary">Update Package</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editPackage(id, productId, amount, price) {
            document.getElementById('editId').value = id;
            document.getElementById('editProductId').value = productId;
            document.getElementById('editAmount').value = amount;
            document.getElementById('editPrice').value = price;
        }
    </script>
</body>
</html>
