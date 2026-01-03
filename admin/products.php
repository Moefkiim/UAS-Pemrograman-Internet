<?php
include '../includes/functions.php';
requireAdmin();

$db = getDB();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $game_id = (int)$_POST['game_id'];
        $server_id = !empty($_POST['server_id']) ? (int)$_POST['server_id'] : null;
        $name = sanitize($_POST['name']);
        $price = (float)$_POST['price'];
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = uploadImage($_FILES['image']);
        }
        $stmt = $db->prepare("INSERT INTO digital_products (game_id, server_id, name, price, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisds", $game_id, $server_id, $name, $price, $image);
        if ($stmt->execute()) {
            $message = "Product added successfully.";
        } else {
            $message = "Error adding product.";
        }
    } elseif (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $game_id = (int)$_POST['game_id'];
        $server_id = !empty($_POST['server_id']) ? (int)$_POST['server_id'] : null;
        $name = sanitize($_POST['name']);
        $price = (float)$_POST['price'];
        $image = sanitize($_POST['existing_image']);
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = uploadImage($_FILES['image']);
        }
        $stmt = $db->prepare("UPDATE digital_products SET game_id = ?, server_id = ?, name = ?, price = ?, image = ? WHERE id = ?");
        $stmt->bind_param("iisds i", $game_id, $server_id, $name, $price, $image, $id);
        if ($stmt->execute()) {
            $message = "Product updated successfully.";
        } else {
            $message = "Error updating product.";
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM digital_products WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Product deleted successfully.";
        } else {
            $message = "Error deleting product.";
        }
    }
}

$products = $db->query("SELECT dp.*, g.name as game_name FROM digital_products dp JOIN games g ON dp.game_id = g.id ORDER BY dp.name");
$games = $db->query("SELECT id, name FROM games ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin</title>
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
        <h1>Manage Digital Products</h1>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal">Add New Product</button>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Game</th>
                    <th>Name</th>
                    <th>Server ID</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><?php echo $product['game_name']; ?></td>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo $product['server_id'] ?: '-'; ?></td>
                        <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                        <td><?php if ($product['image']): ?><img src="<?php echo $product['image']; ?>" alt="Image" width="50"><?php else: ?>No Image<?php endif; ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProductModal" onclick="editProduct(<?php echo $product['id']; ?>, <?php echo $product['game_id']; ?>, <?php echo $product['server_id'] ?: 'null'; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>, '<?php echo addslashes($product['image']); ?>')">Edit</button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="game_id" class="form-label">Game</label>
                            <select class="form-control" id="game_id" name="game_id" required>
                                <option value="">Select Game</option>
                                <?php $games->data_seek(0); while ($game = $games->fetch_assoc()): ?>
                                    <option value="<?php echo $game['id']; ?>"><?php echo $game['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="server_id" class="form-label">Server ID (optional)</label>
                            <input type="number" class="form-control" id="server_id" name="server_id">
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id">
                        <input type="hidden" id="existingImage" name="existing_image">
                        <div class="mb-3">
                            <label for="editGameId" class="form-label">Game</label>
                            <select class="form-control" id="editGameId" name="game_id" required>
                                <option value="">Select Game</option>
                                <?php $games->data_seek(0); while ($game = $games->fetch_assoc()): ?>
                                    <option value="<?php echo $game['id']; ?>"><?php echo $game['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editServerId" class="form-label">Server ID (optional)</label>
                            <input type="number" class="form-control" id="editServerId" name="server_id">
                        </div>
                        <div class="mb-3">
                            <label for="editName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPrice" class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" id="editPrice" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="editImage" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="editImage" name="image" accept="image/*">
                            <small class="form-text text-muted">Leave empty to keep current image</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editProduct(id, gameId, serverId, name, price, image) {
            document.getElementById('editId').value = id;
            document.getElementById('editGameId').value = gameId;
            document.getElementById('editServerId').value = serverId === null ? '' : serverId;
            document.getElementById('editName').value = name;
            document.getElementById('editPrice').value = price;
            document.getElementById('existingImage').value = image;
        }
    </script>
</body>
</html>
