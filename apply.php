<?php
session_start();
include 'db.php';

// Security check: Only students/applicants can access the application form
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'applicant') {
    header("Location: login.php?role=applicant");
    exit;
}

$org_id = $_GET['org_id'] ?? 1;

// Fetch organization details
$stmt = $conn->prepare("SELECT * FROM Organizations WHERE org_id = ?");
$stmt->bind_param("i", $org_id);
$stmt->execute();
$org = $stmt->get_result()->fetch_assoc();

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the real student_id linked to the logged-in user session
    $user_id = $_SESSION['user_id'];
    $s_query = $conn->prepare("SELECT student_id FROM Students WHERE user_id = ?");
    $s_query->bind_param("i", $user_id);
    $s_query->execute();
    $s_result = $s_query->get_result()->fetch_assoc();

    if ($s_result) {
        $student_id = $s_result['student_id'];
        $statement = $_POST['statement'];

        if (!empty(trim($statement))) {
            $insert = $conn->prepare("INSERT INTO Applications (student_id, org_id, statement, status) VALUES (?, ?, ?, 'Pending')");
            $insert->bind_param("iis", $student_id, $org_id, $statement);
            
            if ($insert->execute()) {
                $message = '<div class="alert alert-success py-2">Application submitted successfully! Status: Pending</div>';
            } else {
                $message = '<div class="alert alert-danger py-2">Error: ' . htmlspecialchars($conn->error) . '</div>';
            }
        } else {
            $message = '<div class="alert alert-warning py-2">Please enter a valid statement of purpose.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger py-2">Student profile error. Please re-register.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply - <?php echo htmlspecialchars($org['org_name'] ?? 'Organization'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-B/GM4XqrwHnWXNOWMbloTmrYXZg10cakYGmpfsR/bbzQ6JAJI4ihuyADKLnBgrCe" crossorigin="anonymous">
    <style>
        body { background-color: #0b0f19 !important; color: #e2e8f0 !important; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: #111827 !important; border-bottom: 1px solid #1f2937; }
        .navbar-brand { color: #00f0ff !important; text-shadow: 0 0 10px rgba(0, 240, 255, 0.4); }
        .card { background-color: #111827 !important; border: 1px solid #1f2937 !important; border-radius: 12px; box-shadow: 0 0 25px rgba(0,0,0,0.5); }
        .form-control { background-color: #1f2937 !important; border: 1px solid #374151 !important; color: #fff !important; }
        .form-control:focus { border-color: #00f0ff !important; box-shadow: 0 0 10px rgba(0, 240, 255, 0.2); }
        .btn-portal { background: transparent; border: 1px solid #00f0ff; color: #00f0ff; font-weight: bold; border-radius: 6px; transition: all 0.2s; }
        .btn-portal:hover { background: #00f0ff; color: #0b0f19; box-shadow: 0 0 10px #00f0ff; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4 px-4 shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="directory.php">UnitePortal // Student Grid</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link text-info" href="directory.php">Directory</a>
                <a class="nav-link text-danger" href="login.php?logout=true">Disconnect</a>
            </div>
        </div>
    </nav>

    <div class="container" style="max-width: 600px; margin-top: 40px;">
        <div class="card p-4">
            <h3 class="text-white mb-1">Apply for Membership</h3>
            <h5 class="text-info mb-4 fs-6"><?php echo htmlspecialchars($org['org_name'] ?? ''); ?></h5>
            <?php echo $message; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label text-secondary small">Why do you want to join this organization?</label>
                    <textarea class="form-control" name="statement" rows="5" required placeholder="Write your statement of purpose here..."></textarea>
                </div>
                <button type="submit" class="btn btn-portal w-100 py-2">Transmit Application</button>
                <a href="directory.php" class="btn btn-outline-secondary w-100 mt-2 py-2 text-decoration-none">Return to Directory</a>
            </form>
        </div>
    </div>

    <script type="module" src="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-1a/pXj49ZQ1aHEmrJ+gMw1otqoVsYwlEnlD8mIfY2TV03r20Y0CN7uqx1tQogjPL" crossorigin="anonymous"></script>
</body>
</html>