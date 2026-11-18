<?php
session_start();
include 'db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'officer' || $_SESSION['role'] === 'admin') {
        header("Location: officer_dashboard.php");
    } else {
        header("Location: directory.php");
    }
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $course = trim($_POST['course']);
    $year_level = intval($_POST['year_level']);

    if (!empty($username) && !empty($email) && !empty($password) && !empty($first_name) && !empty($last_name) && !empty($course)) {
        // Check if username or email already exists
        $check = $conn->prepare("SELECT user_id FROM Users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $message = '<div class="alert alert-danger py-2 mb-3 fs-7">Username or email is already registered.</div>';
        } else {
            // Hash password securely
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'applicant';

            // 1. Insert into Users table
            $user_stmt = $conn->prepare("INSERT INTO Users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $user_stmt->bind_param("ssss", $username, $email, $password_hash, $role);

            if ($user_stmt->execute()) {
                $new_user_id = $conn->insert_id;

                // 2. Insert linked student profile into Students table
                $student_stmt = $conn->prepare("INSERT INTO Students (user_id, first_name, last_name, course, year_level) VALUES (?, ?, ?, ?, ?)");
                $student_stmt->bind_param("isssi", $new_user_id, $first_name, $last_name, $course, $year_level);

                if ($student_stmt->execute()) {
                    $message = '<div class="alert alert-success py-2 mb-3 fs-7">Registration successful! <a href="login.php?role=applicant" class="text-info fw-bold text-decoration-underline">Log in here</a>.</div>';
                } else {
                    $message = '<div class="alert alert-danger py-2 mb-3 fs-7">Error saving student profile: ' . htmlspecialchars($conn->error) . '</div>';
                }
            } else {
                $message = '<div class="alert alert-danger py-2 mb-3 fs-7">Error creating user account: ' . htmlspecialchars($conn->error) . '</div>';
            }
        }
    } else {
        $message = '<div class="alert alert-warning py-2 mb-3 fs-7">Please fill in all required fields.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Registration - UnitePortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-B/GM4XqrwHnWXNOWMbloTmrYXZg10cakYGmpfsR/bbzQ6JAJI4ihuyADKLnBgrCe" crossorigin="anonymous">
    <style>
        body { background-color: #0b0f19 !important; color: #e2e8f0 !important; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { background-color: #111827 !important; border: 1px solid #1f2937 !important; border-radius: 12px; box-shadow: 0 0 30px rgba(0,0,0,0.6); }
        .form-control, .form-select { background-color: #1f2937 !important; border: 1px solid #374151 !important; color: #fff !important; }
        .form-control:focus, .form-select:focus { border-color: #00f0ff !important; box-shadow: 0 0 10px rgba(0, 240, 255, 0.2); }
        .btn-portal { background: transparent; border: 2px solid #00f0ff; color: #00f0ff; font-weight: bold; border-radius: 8px; transition: all 0.2s; }
        .btn-portal:hover { background: #00f0ff; color: #0b0f19; box-shadow: 0 0 15px #00f0ff; }
        .portal-title { color: #00f0ff; text-shadow: 0 0 15px rgba(0, 240, 255, 0.4); }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5" style="min-height: 100vh;">
    <div class="container" style="max-width: 500px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold portal-title">UnitePortal // Student Registration</h2>
            <p class="text-secondary small">Initialize new applicant identity</p>
        </div>

        <div class="card p-4">
            <?php echo $message; ?>

            <form method="POST">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-secondary small">First Name</label>
                        <input type="text" class="form-control" name="first_name" required placeholder="e.g., Juan">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-secondary small">Last Name</label>
                        <input type="text" class="form-control" name="last_name" required placeholder="e.g., Dela Cruz">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small">Username</label>
                    <input type="text" class="form-control" name="username" required placeholder="Choose a unique username...">
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small">Email Address</label>
                    <input type="email" class="form-control" name="email" required placeholder="student@uc.edu.ph">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label text-secondary small">Course / Program</label>
                        <input type="text" class="form-control" name="course" required placeholder="e.g., BSIT, BSCS">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-secondary small">Year Level</label>
                        <select class="form-select" name="year_level" required>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary small">Password</label>
                    <input type="password" class="form-control" name="password" required placeholder="Create password...">
                </div>

                <button type="submit" class="btn btn-portal w-100 py-2 mb-3">Create Student Account</button>

                <div class="text-center mb-2">
                    <span class="text-muted small">Already registered? </span>
                    <a href="login.php?role=applicant" class="text-info small text-decoration-none fw-bold">Log in here</a>
                </div>
                <div class="text-center">
                    <a href="index.php" class="text-muted small text-decoration-none">← Return to Portal Select</a>
                </div>
            </form>
        </div>
    </div>

    <script type="module" src="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-1a/pXj49ZQ1aHEmrJ+gMw1otqoVsYwlEnlD8mIfY2TV03r20Y0CN7uqx1tQogjPL" crossorigin="anonymous"></script>
</body>
</html>