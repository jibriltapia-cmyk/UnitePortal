<?php
session_start();
include 'db.php';

// Security check: ensure only officers or admins can access
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'officer' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php?role=officer");
    exit;
}

$officer_org_id = $_SESSION['org_id'];
$message = "";

// Handle Review Actions (Approve / Reject Workflow)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'], $_POST['action'])) {
    $app_id = intval($_POST['app_id']);
    $new_status = $_POST['action']; // 'Approved' or 'Rejected'
    $officer_id = $_SESSION['user_id'];

    // Update application status and reviewer log
    $update = $conn->prepare("UPDATE Applications SET status = ?, reviewed_by = ? WHERE app_id = ?");
    $update->bind_param("sii", $new_status, $officer_id, $app_id);
    
    if ($update->execute()) {
        if ($new_status === 'Approved') {
            // Fetch student_id and org_id to add to active memberships roster
            $q = $conn->prepare("SELECT student_id, org_id FROM Applications WHERE app_id = ?");
            $q->bind_param("i", $app_id);
            $q->execute();
            if ($data = $q->get_result()->fetch_assoc()) {
                $student_id = $data['student_id'];
                $org_id = $data['org_id'];

                // Insert into active memberships roster
                $ins = $conn->prepare("INSERT INTO Memberships (student_id, org_id, status) VALUES (?, ?, 'Active')");
                $ins->bind_param("ii", $student_id, $org_id);
                $ins->execute();
            }
            $message = '<div class="alert alert-success py-2 mb-4">Application successfully <strong>Approved</strong>. Student added to active roster.</div>';
        } else {
            $message = '<div class="alert alert-warning py-2 mb-4">Application successfully <strong>Rejected</strong>.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger py-2 mb-4">Error processing review action.</div>';
    }
}

// Fetch pending applications SCOPED ONLY to this officer's specific organization ID
$query = "SELECT a.app_id, s.first_name, s.last_name, s.course, s.year_level, o.org_name, a.statement, a.submitted_at 
          FROM Applications a
          JOIN Students s ON a.student_id = s.student_id
          JOIN Organizations o ON a.org_id = o.org_id
          WHERE a.status = 'Pending' AND a.org_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $officer_org_id);
$stmt->execute();
$applications = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Officer Dashboard - UnitePortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-B/GM4XqrwHnWXNOWMbloTmrYXZg10cakYGmpfsR/bbzQ6JAJI4ihuyADKLnBgrCe" crossorigin="anonymous">
    <style>
        body { background-color: #0b0f19 !important; color: #e2e8f0 !important; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: #111827 !important; border-bottom: 1px solid #1f2937; }
        .navbar-brand { color: #ff9900 !important; text-shadow: 0 0 10px rgba(255, 153, 0, 0.4); }
        .card { background-color: #111827 !important; border: 1px solid #1f2937 !important; border-radius: 12px; }
        .table { color: #e2e8f0 !important; vertical-align: middle; }
        .table-dark-custom { background-color: #1f2937 !important; color: #fff !important; }
        .table-hover tbody tr:hover { background-color: #1a2234 !important; color: #fff !important; }
        .btn-approve { background: #10b981; color: #0b0f19; font-weight: bold; border: none; }
        .btn-approve:hover { background: #059669; box-shadow: 0 0 10px #10b981; }
        .btn-reject { background: #ef4444; color: #fff; font-weight: bold; border: none; }
        .btn-reject:hover { background: #dc2626; box-shadow: 0 0 10px #ef4444; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4 px-4 shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="officer_dashboard.php">UnitePortal // Officer Grid</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link text-warning active" href="officer_dashboard.php">Officer Dashboard</a>
                <a class="nav-link text-danger" href="login.php?logout=true">Disconnect</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h2 class="mb-1 text-light fw-bold">Club Officer Review Dashboard</h2>
        <p class="text-secondary mb-4 small">Manage incoming student membership applications for your assigned club stream.</p>
        
        <?php echo $message; ?>

        <div class="card p-3 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr class="table-dark-custom text-uppercase fs-7">
                            <th>Applicant Name</th>
                            <th>Course / Year</th>
                            <th>Organization</th>
                            <th>Statement of Purpose</th>
                            <th>Submitted At</th>
                            <th class="text-center">Action Protocol</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($applications && $applications->num_rows > 0): ?>
                            <?php while($row = $applications->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['course']) . ' (Yr ' . $row['year_level'] . ')'; ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['org_name']); ?></span></td>
                                <td style="max-width: 300px;"><?php echo nl2br(htmlspecialchars($row['statement'])); ?></td>
                                <td class="small text-muted"><?php echo $row['submitted_at']; ?></td>
                                <td class="text-center">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="app_id" value="<?php echo $row['app_id']; ?>">
                                        <button type="submit" name="action" value="Approved" class="btn btn-approve btn-sm px-3 me-1">Approve</button>
                                        <button type="submit" name="action" value="Rejected" class="btn btn-reject btn-sm px-3">Reject</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5 fst-italic">No pending membership applications found for your assigned organization stream.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script type="module" src="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-1a/pXj49ZQ1aHEmrJ+gMw1otqoVsYwlEnlD8mIfY2TV03r20Y0CN7uqx1tQogjPL" crossorigin="anonymous"></script>
</body>
</html>