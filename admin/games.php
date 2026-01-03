<?php
include '../includes/functions.php';
requireAdmin();

$db = getDB();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $name = sanitize($_POST['name']);
        $slug = sanitize($_POST['slug']);
        $publisher = sanitize($_POST['publisher']);
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = uploadImage($_FILES['image']);
        }
        $stmt = $db->prepare("INSERT INTO games (name, slug, publisher, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $slug, $publisher, $image);
        if ($stmt->execute()) {
            $message = "Game added successfully.";
        } else {
            $message = "Error adding game.";
        }
    } elseif (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $name = sanitize($_POST['name']);
        $slug = sanitize($_POST['slug']);
        $publisher = sanitize($_POST['publisher']);
        $image = sanitize($_POST['existing_image']);
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = uploadImage($_FILES['image']);
        }
        $stmt = $db->prepare("UPDATE games SET name = ?, slug = ?, publisher = ?, image = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $slug, $publisher, $image, $id);
        if ($stmt->execute()) {
            $message = "Game updated successfully.";
        } else {
            $message = "Error updating game.";
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM games WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Game deleted successfully.";
        } else {
            $message = "Error deleting game.";
        }
    }
}

$games = $db->query("SELECT * FROM games ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Games - Admin</title>
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
        <h1>Manage Games</h1>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addGameModal">Add New Game</button>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Publisher</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($game = $games->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $game['id']; ?></td>
                        <td><?php echo $game['name']; ?></td>
                        <td><?php echo $game['slug']; ?></td>
                        <td><?php echo $game['publisher']; ?></td>
                        <td><?php if ($game['image']): ?><img src="<?php echo $game['image']; ?>" alt="Image" width="50"><?php else: ?>No Image<?php endif; ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editGameModal" onclick="editGame(<?php echo $game['id']; ?>, '<?php echo addslashes($game['name']); ?>', '<?php echo addslashes($game['slug']); ?>', '<?php echo addslashes($game['publisher']); ?>', '<?php echo addslashes($game['image']); ?>')">Edit</button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $game['id']; ?>">
                                <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Game Modal -->
    <div class="modal fade" id="addGameModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Game</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" required>
                        </div>
                        <div class="mb-3">
                            <label for="publisher" class="form-label">Publisher</label>
                            <input type="text" class="form-control" id="publisher" name="publisher">
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Game Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add" class="btn btn-primary">Add Game</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Game Modal -->
    <div class="modal fade" id="editGameModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Game</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id">
                        <input type="hidden" id="existingImage" name="existing_image">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSlug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="editSlug" name="slug" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPublisher" class="form-label">Publisher</label>
                            <input type="text" class="form-control" id="editPublisher" name="publisher">
                        </div>
                        <div class="mb-3">
                            <label for="editImage" class="form-label">Game Image</label>
                            <input type="file" class="form-control" id="editImage" name="image" accept="image/*">
                            <small class="form-text text-muted">Leave empty to keep current image</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit" class="btn btn-primary">Update Game</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editGame(id, name, slug, publisher, image) {
            document.getElementById('editId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editSlug').value = slug;
            document.getElementById('editPublisher').value = publisher;
            document.getElementById('existingImage').value = image;
        }
    </script>
</body>
</html>
