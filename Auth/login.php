<?php
session_start();
include('../config/db_connect.php');

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role     = $_POST['role'] ?? 'student';

    // -------- ADMIN --------
    if ($role === 'admin') {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['alogin'] = $admin['username'];
            $_SESSION['role']   = 'admin';
            header("Location: ../admin_dashboard/");
            exit;
        }
    }

    // -------- MANAGEMENT --------
    if ($role === 'management') {
        $stmt = $conn->prepare("SELECT * FROM management WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $management = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($management && password_verify($password, $management['password'])) {
            $_SESSION['mlogin'] = $management['username'];
            $_SESSION['role']   = 'management';
            header("Location: ../management_dashboard/");
            exit;
        }
    }

    // -------- TEACHER --------
    if ($role === 'teacher') {
        $stmt = $conn->prepare("SELECT * FROM teachers WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $teacher = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($teacher && password_verify($password, $teacher['password'])) {
            $_SESSION['tlogin'] = $teacher['username'];
            $_SESSION['role']   = 'teacher';
            header("Location: ../teacher_dashboard/");
            exit;
        }
    }

    // -------- STUDENT --------
    if ($role === 'student') {
        $stmt = $conn->prepare("SELECT * FROM students WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($student && password_verify($password, $student['password'])) {
            $_SESSION['slogin'] = $student['username'];
            $_SESSION['role']   = 'student';
            header("Location: ../student_dashboard/");
            exit;
        }
    }

    $error = "Invalid username or password!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Adaxy Academy · secure login</title>

  <!-- Bootstrap 5 (grid & utilities only) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    /* ----- full design system preserved ----- */
    :root {
      --navy: #0F172A;
      --navy-mid: #1F2937;
      --gold: #2563EB;
      --gold-light: #60A5FA;
      --cream: #F8FAFC;
      --white: #FFFFFF;
      --text: #111827;
      --muted: #6B7280;
      --border: #E5E7EB;
      --radius: 10px;
      --shadow: 0 6px 24px rgba(15, 23, 42, .08);
      --shadow-lg: 0 18px 48px rgba(15, 23, 42, .16);
      --font-head: system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
      --font-body: system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
      --transition: .25s ease;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: var(--font-body); background: var(--cream); color: var(--text); font-size: 15px; line-height: 1.7; min-height: 100vh; display: flex; flex-direction: column; }

    .topbar-sim { background: var(--navy); color: rgba(255,255,255,.7); font-size: 12.5px; padding: 7px 0; letter-spacing: .02em; }
    .nav-sim { background: var(--white); border-bottom: 1px solid var(--border); padding: 10px 0; box-shadow: var(--shadow); }

    .brand-emblem { width: 44px; height: 44px; background: var(--navy); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .brand-emblem i { color: var(--gold); font-size: 20px; }
    .brand-text-top { font-family: var(--font-head); font-size: 18px; color: var(--navy); font-weight: 700; line-height: 1.1; }
    .brand-text-sub { font-size: 10.5px; color: var(--muted); letter-spacing: .1em; text-transform: uppercase; }

    .portal-card { border-radius: 20px; padding: 3rem 2.5rem; background: var(--white); border: 1px solid var(--border); box-shadow: var(--shadow-lg); }
    .section-tag { display: inline-block; color: var(--gold); font-size: 12px; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; margin-bottom: 12px; }
    .section-tag::before { content: '—'; margin-right: 8px; }
    .divider-gold { width: 52px; height: 3px; background: var(--gold); border-radius: 2px; margin: 20px 0 20px; }

    .btn-enroll { background: var(--gold); color: var(--navy) !important; font-weight: 600; font-size: 13px; padding: 9px 22px; border-radius: 50px; letter-spacing: .02em; transition: background var(--transition), box-shadow var(--transition); border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
    .btn-enroll:hover { background: var(--gold-light); box-shadow: 0 4px 16px rgba(37,99,235,.35); color: var(--navy); }

    .form-control, .form-select { border: 1.5px solid var(--border); border-radius: 14px; padding: 12px 16px; font-size: 14px; transition: all var(--transition); background: var(--white); }
    .form-control:focus, .form-select:focus { border-color: var(--gold); box-shadow: 0 0 0 4px rgba(37,99,235,.15); outline: none; }
    .input-group-text { background: var(--cream); border: 1.5px solid var(--border); border-radius: 14px 0 0 14px; color: var(--muted); padding: 0 18px; }

    .role-badge { background: var(--cream); border: 1px dashed var(--gold); color: var(--navy); font-size: 13px; padding: 8px 16px; border-radius: 40px; display: inline-flex; align-items: center; gap: 10px; }
    .role-badge i { color: var(--gold); }

    .small-note { font-size: 12.5px; color: var(--muted); margin-top: 16px; }

    footer { background: var(--navy); color: rgba(255,255,255,.65); padding: 40px 0 20px; margin-top: 60px; }
    .footer-brand { font-family: var(--font-head); color: var(--white); font-size: 18px; }

    .fade-up { opacity: 0; transform: translateY(28px); transition: opacity .55s ease, transform .55s ease; }
    .fade-up.visible { opacity: 1; transform: translateY(0); }
  </style>
</head>
<body>

<div class="topbar-sim d-none d-md-block">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center">
      <span><i class="fa fa-map-marker-alt me-1" style="color:#2563EB;"></i> P.O. Box xxx, Lilongwe, Malawi</span>
      <span><i class="fa fa-clock me-1"></i>Mon–Fri: 07:30–17:00</span>
    </div>
  </div>
</div>

<div class="nav-sim">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between">
      <a class="navbar-brand d-flex align-items-center gap-3" href="#" style="text-decoration: none;">
        <div class="brand-emblem"><i class="fa fa-graduation-cap"></i></div>
        <div>
          <div class="brand-text-top">Adaxy Academy</div>
          <div class="brand-text-sub">Est. 2026 · Lilongwe</div>
        </div>
      </a>
      <a href="index.php" class="btn-enroll d-inline-flex align-items-center gap-2">
        <i class="fa fa-arrow-left"></i> Back to Home
      </a>
    </div>
  </div>
</div>

<section style="padding: 60px 0;" class="fade-up" id="loginSection">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-8">
        <div class="portal-card">

          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="section-tag" style="margin-bottom:0;">Secure access</span>
            <span class="role-badge" id="roleBadge">
              <i class="fa" id="roleIcon"></i> <span id="roleDisplay">Student</span>
              <i class="fa fa-lock" style="font-size: 11px; opacity: 0.7;"></i>
            </span>
          </div>

          <input type="hidden" id="roleInput" name="role" value="student">

          <h2 class="section-title" style="font-size: 28px; margin-bottom: 0;">Portal login</h2>
          <div class="divider-gold" style="margin-top: 8px;"></div>

          <?php if($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <form id="loginForm" class="mt-3" method="post">
            <div class="mb-4">
              <label class="form-label fw-semibold text-navy">Email / Roll ID</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                <input type="text" class="form-control" name="username" placeholder="Enter your username" required autofocus>
              </div>
            </div>
            <div class="mb-4">
              <label class="form-label fw-semibold text-navy">Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-lock"></i></span>
                <input type="password" class="form-control" name="password" placeholder="••••••••" required>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember">
                <label class="form-check-label" style="font-size:14px;">Remember me</label>
              </div>
              <a href="#" style="color: var(--gold); font-size:14px;">Forgot password?</a>
            </div>

            <button type="submit" class="btn-enroll w-100 py-3 d-flex justify-content-center align-items-center gap-2">
              <i class="fa fa-arrow-right-to-bracket"></i> Sign in as <span id="submitRoleLabel">Student</span>
            </button>

            <div class="small-note text-center mt-4">
              <i class="fa fa-shield-alt me-1" style="color:var(--gold);"></i> 
              Authorised personnel only. Adaxy Academy uses multi‑factor security.
            </div>
          </form>

          <div class="d-flex justify-content-center gap-4 mt-4 pt-2">
            <a href="#" style="color: var(--muted); font-size:13px;"><i class="fa fa-question-circle me-1"></i>Help</a>
            <a href="#" style="color: var(--muted); font-size:13px;"><i class="fa fa-shield-alt me-1"></i>Privacy</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<footer>
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <div class="brand-emblem" style="width:36px; height:36px;"><i class="fa fa-graduation-cap" style="font-size:16px;"></i></div>
        <span class="footer-brand">Adaxy Academy · secure portal</span>
      </div>
      <div style="color:rgba(255,255,255,.3); font-size:12px;">© 2025 • encrypted login</div>
    </div>
  </div>
</footer>

<script>
(function() {
    const urlParams = new URLSearchParams(window.location.search);
    let role = urlParams.get('role') || 'student';
    if (!['student','teacher','admin'].includes(role)) role = 'student';

    const roleDisplay = document.getElementById('roleDisplay');
    const roleIcon = document.getElementById('roleIcon');
    const submitRoleLabel = document.getElementById('submitRoleLabel');
    const roleInput = document.getElementById('roleInput');
    roleInput.value = role;

    if(role==='teacher'){ roleDisplay.innerText='Teacher'; roleIcon.className='fa fa-chalkboard-teacher'; submitRoleLabel.innerText='Teacher'; }
    else if(role==='admin'){ roleDisplay.innerText='Admin'; roleIcon.className='fa fa-user-shield'; submitRoleLabel.innerText='Admin'; }
    else { roleDisplay.innerText='Student'; roleIcon.className='fa fa-user-graduate'; submitRoleLabel.innerText='Student'; }

    const observer = new IntersectionObserver((entries)=>{
        entries.forEach(entry=>{ if(entry.isIntersecting){ entry.target.classList.add('visible'); observer.unobserve(entry.target); } });
    }, {threshold:0.12});
    document.querySelectorAll('.fade-up').forEach(el=>observer.observe(el));
})();
</script>
</body>
</html>