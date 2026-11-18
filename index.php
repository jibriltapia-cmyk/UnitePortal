<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'officer' || $_SESSION['role'] === 'admin') {
        header("Location: officer_dashboard.php");
    } else {
        header("Location: directory.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to UnitePortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-B/GM4XqrwHnWXNOWMbloTmrYXZg10cakYGmpfsR/bbzQ6JAJI4ihuyADKLnBgrCe" crossorigin="anonymous">
    <style>
        body { background-color: #0b0f19 !important; color: #e2e8f0 !important; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .portal-title { color: #00f0ff; text-shadow: 0 0 20px rgba(0, 240, 255, 0.4); }
        .portal-card { background-color: #111827 !important; border: 1px solid #1f2937 !important; border-radius: 12px; transition: all 0.3s ease; }
        .portal-card:hover { transform: translateY(-5px); border-color: #00f0ff !important; box-shadow: 0 0 25px rgba(0, 240, 255, 0.2); }
        .portal-card-orange:hover { border-color: #ff9900 !important; box-shadow: 0 0 25px rgba(255, 153, 0, 0.2); }
        .btn-portal-blue { background: transparent; border: 2px solid #00f0ff; color: #00f0ff; font-weight: 600; border-radius: 8px; transition: all 0.2s; }
        .btn-portal-blue:hover { background: #00f0ff; color: #0b0f19; box-shadow: 0 0 15px #00f0ff; }
        .btn-portal-orange { background: transparent; border: 2px solid #ff9900; color: #ff9900; font-weight: 600; border-radius: 8px; transition: all 0.2s; }
        .btn-portal-orange:hover { background: #ff9900; color: #0b0f19; box-shadow: 0 0 15px #ff9900; }
        .icon-box { width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; flex-shrink: 0; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="container text-center" style="max-width: 750px;">
        <h1 class="display-3 fw-bold mb-3 portal-title">UnitePortal</h1>
        <p class="lead text-secondary mb-5">Student Organization Membership Portal. Select your portal gateway to initialize:</p>
        
        <div class="row g-4">
            <!-- Student Portal Card (Blue Portal) -->
            <div class="col-md-6">
                <div class="card portal-card p-4 h-100 shadow text-center">
                    <div class="card-body d-flex flex-column justify-content-between p-0">
                        <div>
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="icon-box bg-info bg-opacity-10 text-info me-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-mortarboard-fill" viewBox="0 0 16 16">
                                        <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h1v-2a2 2 0 0 1 2-2V6.434a.5.5 0 0 0-.025-.917z"/>
                                        <path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466z"/>
                                    </svg>
                                </div>
                                <h3 class="fw-bold text-info mb-0 fs-4">Student Portal</h3>
                            </div>
                            <p class="text-muted small">Browse active campus organizations, view parameters, and transmit membership applications.</p>
                        </div>
                        <a href="login.php?role=applicant" class="btn btn-portal-blue w-100 mt-4 text-center">Enter Blue Portal</a>
                    </div>
                </div>
            </div>

            <!-- Officer Portal Card (Orange Portal) -->
            <div class="col-md-6">
                <div class="card portal-card portal-card-orange p-4 h-100 shadow text-center">
                    <div class="card-body d-flex flex-column justify-content-between p-0">
                        <div>
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="icon-box bg-warning bg-opacity-10 text-warning me-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-shield-lock-fill" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.228.655-2.87.87A1.94 1.94 0 0 0 1.5 3.355v3.656C1.5 10.324 4.5 14.5 8 16c3.5-1.5 6.5-5.676 6.5-8.99V3.355c0-.962-.737-1.752-1.732-1.925-.642-.215-1.76-.57-2.87-.87C9.843.265 8.69 0 8 0m0 7a1.5 1.5 0 0 1 .5 2.915l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99A1.5 1.5 0 0 1 8 7"/>
                                    </svg>
                                </div>
                                <h3 class="fw-bold text-warning mb-0 fs-4">Officer Portal</h3>
                            </div>
                            <p class="text-muted small">Review incoming application data streams, execute approval protocols, and manage rosters.</p>
                        </div>
                        <a href="login.php?role=officer" class="btn btn-portal-orange w-100 mt-4 text-center">Enter Orange Portal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="module" src="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-1a/pXj49ZQ1aHEmrJ+gMw1otqoVsYwlEnlD8mIfY2TV03r20Y0CN7uqx1tQogjPL" crossorigin="anonymous"></script>
</body>
</html>