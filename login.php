<?php
session_start();
include 'db.php';

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'officer' || $_SESSION['role'] === 'admin') {
        header("Location: officer_dashboard.php");
    } else {
        header("Location: directory.php");
    }
    exit;
}

$error = "";
$portal_type = $_GET['role'] ?? 'applicant'; // Detects if 'applicant' (student) or 'officer'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT user_id, username, password_hash, role, org_id FROM Users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if ($password === $user['password_hash'] || password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['org_id'] = $user['org_id'];

                if ($user['role'] === 'officer' || $user['role'] === 'admin') {
                    header("Location: officer_dashboard.php");
                } else {
                    header("Location: directory.php");
                }
                exit;
            } else {
                $error = "Invalid password credentials.";
            }
        } else {
            $error = "User account not found in the system registry.";
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - UnitePortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-B/GM4XqrwHnWXNOWMbloTmrYXZg10cakYGmpfsR/bbzQ6JAJI4ihuyADKLnBgrCe" crossorigin="anonymous">
    <style>
        body { background-color: #0b0f19 !important; color: #e2e8f0 !important; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { background-color: #111827 !important; border: 1px solid #1f2937 !important; border-radius: 12px; box-shadow: 0 0 30px rgba(0,0,0,0.6); }
        .form-control { background-color: #1f2937 !important; border: 1px solid #374151 !important; color: #fff !important; }
        .form-control:focus { border-color: <?php echo ($portal_type === 'officer') ? '#ff9900' : '#00f0ff'; ?> !important; box-shadow: 0 0 10px rgba(0, 240, 255, 0.2); }
        .btn-portal { background: transparent; border: 2px solid <?php echo ($portal_type === 'officer') ? '#ff9900' : '#00f0ff'; ?>; color: <?php echo ($portal_type === 'officer') ? '#ff9900' : '#00f0ff'; ?>; font-weight: bold; border-radius: 8px; transition: all 0.2s; }
        .btn-portal:hover { background: <?php echo ($portal_type === 'officer') ? '#ff9900' : '#00f0ff'; ?>; color: #0b0f19; box-shadow: 0 0 15px <?php echo ($portal_type === 'officer') ? '#ff9900' : '#00f0ff'; ?>; }
        .portal-title { color: <?php echo ($portal_type === 'officer') ? '#ff9900' : '#00f0ff'; ?>; text-shadow: 0 0 15px rgba(0, 240, 255, 0.4); }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="container" style="max-width: 420px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold portal-title">
                <?php echo ($portal_type === 'officer') ? 'UnitePortal // Officer Login' : 'UnitePortal // Student Login'; ?>
            </h2>
            <p class="text-secondary small">Authenticate gateway credentials</p>
        </div>

        <div class="card p-4">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 mb-3 fs-7"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label text-secondary small">Username</label>
                    <input type="text" class="form-control" name="username" required placeholder="Enter username...">
                </div>
                <div class="mb-4">
                    <label class="form-label text-secondary small">Password</label>
                    <input type="password" class="form-control" name="password" required placeholder="Enter password...">
                </div>
                <button type="submit" class="btn btn-portal w-100 py-2 mb-3">Initialize Login</button>

                <!-- Show registration link ONLY when logging into the Student Portal -->
                <?php if ($portal_type === 'applicant'): ?>
                    <div class="text-center mb-3">
                        <span class="text-muted small">Don't have a student account? </span>
                        <a href="register.php" class="text-info small text-decoration-none fw-bold">Register Student Account</a>
                    </div>
                <?php endif; ?>

                <div class="text-center">
                    <a href="index.php" class="text-muted small text-decoration-none">← Return to Portal Select</a>
                </div>
            </form>
        </div>
    </div>

    <script type="module" src="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-1a/pXj49ZQ1aHEmrJ+gMw1otqoVsYwlEnlD8mIfY2TV03r20Y0CN7uqx1tQogjPL" crossorigin="anonymous"></script>
</body>
</html>