<?php
session_start();
include 'db.php';

// Security check: ensure only logged-in students/applicants can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'applicant') {
    header("Location: login.php?role=applicant");
    exit;
}

// Fetch all active organizations along with their live active member count
$query = "SELECT o.org_id, o.org_name, o.description, o.category, o.max_capacity,
                 COUNT(m.membership_id) AS active_count
          FROM Organizations o
          LEFT JOIN Memberships m ON o.org_id = m.org_id AND m.status = 'Active'
          WHERE o.status = 'Active'
          GROUP BY o.org_id
          ORDER BY o.org_id ASC";
$organizations = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UnitePortal - Organization Directory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-B/GM4XqrwHnWXNOWMbloTmrYXZg10cakYGmpfsR/bbzQ6JAJI4ihuyADKLnBgrCe" crossorigin="anonymous">
    <style>
        body { background-color: #0b0f19 !important; color: #e2e8f0 !important; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: #111827 !important; border-bottom: 1px solid #1f2937; }
        .navbar-brand { color: #00f0ff !important; text-shadow: 0 0 10px rgba(0, 240, 255, 0.4); }
        .org-card { background-color: #111827 !important; border: 1px solid #1f2937 !important; border-radius: 12px; transition: all 0.25s ease; }
        .org-card:hover { border-color: #00f0ff !important; box-shadow: 0 0 20px rgba(0, 240, 255, 0.15); }
        .category-badge { background: rgba(0, 240, 255, 0.1); color: #00f0ff; border: 1px solid rgba(0, 240, 255, 0.35); border-radius: 20px; padding: 3px 12px; font-size: 0.78rem; font-weight: 600; display: inline-block; }
        .btn-portal { background: transparent; border: 1px solid #00f0ff; color: #00f0ff; font-weight: 600; border-radius: 8px; transition: all 0.2s; }
        .btn-portal:hover { background: #00f0ff; color: #0b0f19; box-shadow: 0 0 12px #00f0ff; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4 px-4 shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="directory.php">UnitePortal // Student Grid</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link text-info active" href="directory.php">Directory</a>
                <a class="nav-link text-danger" href="login.php?logout=true">Disconnect</a>
            </div>
        </div>
    </nav>

    <div class="container py-3" style="max-width: 960px;">
        <h2 class="text-white fw-bold mb-4">Active Campus Organizations</h2>

        <div class="d-flex flex-column gap-4">
            <?php if ($organizations && $organizations->num_rows > 0): ?>
                <?php while ($org = $organizations->fetch_assoc()): ?>
                    <?php
                        $is_full = ($org['active_count'] >= $org['max_capacity']);
                    ?>
                    <div class="card org-card p-4 shadow-sm">
                        <div class="mb-2">
                            <span class="category-badge"><?php echo htmlspecialchars($org['category']); ?></span>
                        </div>
                        <h4 class="text-white fw-bold mb-1"><?php echo htmlspecialchars($org['org_name']); ?></h4>
                        <p class="text-secondary small mb-3"><?php echo htmlspecialchars($org['description']); ?></p>
                        
                        <div class="small text-light mb-3">
                            Active Roster: <strong><?php echo intval($org['active_count']); ?> / <?php echo intval($org['max_capacity']); ?></strong>
                        </div>

                        <?php if ($is_full): ?>
                            <button class="btn btn-secondary w-100" disabled>Roster at Full Capacity</button>
                        <?php else: ?>
                            <a href="apply.php?org_id=<?php echo intval($org['org_id']); ?>" class="btn btn-portal w-100">Initialize Application</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card org-card p-4 text-center text-muted fst-italic">
                    No active organizations available in the directory right now.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script type="module" src="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-1a/pXj49ZQ1aHEmrJ+gMw1otqoVsYwlEnlD8mIfY2TV03r20Y0CN7uqx1tQogjPL" crossorigin="anonymous"></script>
</body>
</html>