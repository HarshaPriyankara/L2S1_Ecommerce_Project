<?php
include 'includes/db.php';
require_once 'includes/security.php';
ayurora_start_secure_session();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$current_user_id = (int) $_SESSION['user_id'];
$allowed_roles = ['customer', 'admin'];
$message = '';
$error = '';
$search = trim($_GET['search'] ?? '');
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$has_user_filters = $search !== '' || $role_filter !== '' || $status_filter !== '';

$column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'is_active'");
if ($column_check && $column_check->num_rows === 0) {
    $conn->query('ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER role');
}

if (isset($_POST['update_user'])) {
    $user_id = (int) ($_POST['user_id'] ?? 0);
    $role = $_POST['role'] ?? '';
    $is_active = isset($_POST['is_active']) ? (int) $_POST['is_active'] : 0;

    if ($user_id <= 0 || !in_array($role, $allowed_roles, true) || !in_array($is_active, [0, 1], true)) {
        $error = 'Please choose valid user settings.';
    } elseif ($user_id === $current_user_id && ($role !== 'admin' || $is_active !== 1)) {
        $error = 'You cannot remove your own admin access or deactivate your own account.';
    } else {
        $update_stmt = $conn->prepare('UPDATE users SET role = ?, is_active = ? WHERE id = ?');
        $update_stmt->bind_param('sii', $role, $is_active, $user_id);

        if ($update_stmt->execute()) {
            $message = 'User updated successfully.';
        } else {
            $error = 'Could not update user. Please try again.';
        }

        $update_stmt->close();
    }
}

$users = [];
$where = [];
$types = '';
$params = [];

if ($search !== '') {
    $where[] = '(u.name LIKE ? OR u.email LIKE ?)';
    $types .= 'ss';
    $search_like = '%' . $search . '%';
    $params[] = $search_like;
    $params[] = $search_like;
}

if (in_array($role_filter, $allowed_roles, true)) {
    $where[] = 'u.role = ?';
    $types .= 's';
    $params[] = $role_filter;
}

if ($status_filter === 'active' || $status_filter === 'inactive') {
    $where[] = 'u.is_active = ?';
    $types .= 'i';
    $params[] = $status_filter === 'active' ? 1 : 0;
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$users_sql = "
    SELECT u.id, u.name, u.email, u.role, u.is_active, u.created_at, COUNT(o.id) AS order_count
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id
    $where_sql
    GROUP BY u.id, u.name, u.email, u.role, u.is_active, u.created_at
    ORDER BY u.created_at DESC, u.id DESC
";
$users_stmt = $conn->prepare($users_sql);

if ($types !== '') {
    $users_stmt->bind_param($types, ...$params);
}

$users_stmt->execute();
$users_result = $users_stmt->get_result();

while ($user = $users_result->fetch_assoc()) {
    $users[] = $user;
}

$users_stmt->close();

include 'includes/header.php';
?>

<div class="admin-users-page">
    <div class="order-history-header">
        <div>
            <span class="eyebrow">Admin</span>
            <h2 class="section-title">User Management</h2>
        </div>
        <a href="admin.php" class="btn btn-outline">Product Dashboard</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form class="admin-user-filter" method="GET" action="admin_users.php">
        <div class="product-search-main">
            <label>Search Users</label>
            <div class="product-search-field">
                <i class="fas fa-search"></i>
                <input type="search" name="search" placeholder="Name or email" value="<?php echo htmlspecialchars($search); ?>">
            </div>
        </div>
        <div class="category-filter-field">
            <label>Role</label>
            <select name="role" class="form-control">
                <option value="">All Roles</option>
                <?php foreach ($allowed_roles as $role): ?>
                    <option value="<?php echo htmlspecialchars($role); ?>" <?php echo $role_filter === $role ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(ucfirst($role)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="category-filter-field">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <div class="product-search-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
            <?php if ($has_user_filters): ?>
                <a href="admin_users.php" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Orders</th>
                    <th>Joined</th>
                    <th>Manage</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No users found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <?php $is_current_user = (int) $user['id'] === $current_user_id; ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                <span class="admin-user-meta">
                                    ID #<?php echo (int) $user['id']; ?>
                                    <?php if ($is_current_user): ?>
                                        &middot; You
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                            <td>
                                <span class="stock-badge <?php echo (int) $user['is_active'] === 1 ? 'in-stock' : 'out-stock'; ?>">
                                    <?php echo (int) $user['is_active'] === 1 ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="admin_user_orders.php?user_id=<?php echo (int) $user['id']; ?>" class="auth-link">
                                    <?php echo (int) $user['order_count']; ?> orders
                                </a>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <form method="POST" action="admin_users.php" class="admin-user-form">
                                    <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">

                                    <select name="role" class="form-control" <?php echo $is_current_user ? 'disabled' : ''; ?>>
                                        <?php foreach ($allowed_roles as $role): ?>
                                            <option value="<?php echo htmlspecialchars($role); ?>" <?php echo $user['role'] === $role ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars(ucfirst($role)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <select name="is_active" class="form-control" <?php echo $is_current_user ? 'disabled' : ''; ?>>
                                        <option value="1" <?php echo (int) $user['is_active'] === 1 ? 'selected' : ''; ?>>Active</option>
                                        <option value="0" <?php echo (int) $user['is_active'] === 0 ? 'selected' : ''; ?>>Inactive</option>
                                    </select>

                                    <?php if ($is_current_user): ?>
                                        <span class="status-badge status-active">Protected</span>
                                    <?php else: ?>
                                        <button type="submit" name="update_user" class="btn btn-primary">Save</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
