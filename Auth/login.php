<?php
session_start();
include('../config/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the user is an admin
$adminQuery = "SELECT * FROM admin WHERE UserName=? AND Password=?";
    $adminStmt = $conn->prepare($adminQuery);
    $adminStmt->bind_param("ss", $username, $password);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();
    if ($adminResult->num_rows > 0) {
        $_SESSION['alogin'] = $username;
        $_SESSION['role'] = 'admin';
        header("Location: ../management_dashboard/");
        exit;
    }

    // Check if the user is a teacher
    $teacherQuery = "SELECT * FROM tblteachers WHERE Email=:username AND Password=:password";
    $teacherStmt = $dbh->prepare($teacherQuery);
    $teacherStmt->bindParam(':username', $username, PDO::PARAM_STR);
    $teacherStmt->bindParam(':password', $password, PDO::PARAM_STR);
    $teacherStmt->execute();

    if ($teacherStmt->rowCount() > 0) {
        $teacherData = $teacherStmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['tlogin'] = $teacherData['Email'];
        $_SESSION['role'] = 'teacher';
        header("Location: teacher-dashboard.php");
        exit;
    }

    // Check if the user is a student
$studentQuery = "SELECT * FROM tblstudents WHERE RollId=? AND Password=?";
    $studentStmt = $conn->prepare($studentQuery);
    $studentStmt->bind_param("ss", $username, $password);
    $studentStmt->execute();
    $studentResult = $studentStmt->get_result();
    if ($studentResult->num_rows > 0) {
        $studentData = $studentResult->fetch_assoc();
        $_SESSION['slogin'] = $studentData['RollId'];
        $_SESSION['role'] = 'student';
        header("Location: ../student_dashboard/");
        exit;
    }

    // If no user is found, display an error
    $error = "Invalid username or password";
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
    /* ----- your exact design system (unchanged) ----- */
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

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: var(--font-body);
      background: var(--cream);
      color: var(--text);
      font-size: 15px;
      line-height: 1.7;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* top bar simulation (same style) */
    .topbar-sim {
      background: var(--navy);
      color: rgba(255,255,255,.7);
      font-size: 12.5px;
      padding: 7px 0;
      letter-spacing: .02em;
    }

    /* mini navbar (exact copy of your brand style) */
    .nav-sim {
      background: var(--white);
      border-bottom: 1px solid var(--border);
      padding: 10px 0;
      box-shadow: var(--shadow);
    }

    .brand-emblem {
      width: 44px;
      height: 44px;
      background: var(--navy);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .brand-emblem i {
      color: var(--gold);
      font-size: 20px;
    }

    .brand-text-top {
      font-family: var(--font-head);
      font-size: 18px;
      color: var(--navy);
      font-weight: 700;
      line-height: 1.1;
    }

    .brand-text-sub {
      font-size: 10.5px;
      color: var(--muted);
      letter-spacing: .1em;
      text-transform: uppercase;
    }

    /* portal card – exactly as used in your #portals section */
    .portal-card {
      border-radius: 20px;
      padding: 3rem 2.5rem;
      background: var(--white);
      border: 1px solid var(--border);
      box-shadow: var(--shadow-lg);
    }

    .section-tag {
      display: inline-block;
      color: var(--gold);
      font-size: 12px;
      font-weight: 600;
      letter-spacing: .12em;
      text-transform: uppercase;
      margin-bottom: 12px;
    }

    .section-tag::before {
      content: '—';
      margin-right: 8px;
    }

    .divider-gold {
      width: 52px;
      height: 3px;
      background: var(--gold);
      border-radius: 2px;
      margin: 20px 0 20px;
    }

    /* buttons */
    .btn-enroll {
      background: var(--gold);
      color: var(--navy) !important;
      font-weight: 600;
      font-size: 13px;
      padding: 9px 22px;
      border-radius: 50px;
      letter-spacing: .02em;
      transition: background var(--transition), box-shadow var(--transition);
      border: none;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-enroll:hover {
      background: var(--gold-light);
      box-shadow: 0 4px 16px rgba(37,99,235,.35);
      color: var(--navy);
    }

    /* form controls (consistent with your input styles) */
    .form-control, .form-select {
      border: 1.5px solid var(--border);
      border-radius: 14px;
      padding: 12px 16px;
      font-size: 14px;
      transition: all var(--transition);
      background: var(--white);
    }

    .form-control:focus, .form-select:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 4px rgba(37,99,235,.15);
      outline: none;
    }

    .input-group-text {
      background: var(--cream);
      border: 1.5px solid var(--border);
      border-radius: 14px 0 0 14px;
      color: var(--muted);
      padding: 0 18px;
    }

    /* role badge (non‑interactive, only display) */
    .role-badge {
      background: var(--cream);
      border: 1px dashed var(--gold);
      color: var(--navy);
      font-size: 13px;
      padding: 8px 16px;
      border-radius: 40px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }

    .role-badge i {
      color: var(--gold);
    }

    /* small note */
    .small-note {
      font-size: 12.5px;
      color: var(--muted);
      margin-top: 16px;
    }

    /* footer */
    footer {
      background: var(--navy);
      color: rgba(255,255,255,.65);
      padding: 40px 0 20px;
      margin-top: 60px;
    }

    .footer-brand {
      font-family: var(--font-head);
      color: var(--white);
      font-size: 18px;
    }

    /* fade effect (same as your .fade-up) */
    .fade-up {
      opacity: 0;
      transform: translateY(28px);
      transition: opacity .55s ease, transform .55s ease;
    }

    .fade-up.visible {
      opacity: 1;
      transform: translateY(0);
    }

    /* no register tabs, only one form */
  </style>
</head>
<body>

<!-- mini topbar (familiar style) -->
<div class="topbar-sim d-none d-md-block">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center">
      <span><i class="fa fa-map-marker-alt me-1" style="color:#2563EB;"></i> P.O. Box xxx, Lilongwe, Malawi</span>
      <span><i class="fa fa-clock me-1"></i>Mon–Fri: 07:30–17:00</span>
    </div>
  </div>
</div>

<!-- simple navbar with back link (preserves navigation) -->
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

<!-- MAIN LOGIN CARD (strictly login, no register) -->
<section style="padding: 60px 0;" class="fade-up" id="loginSection">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-8">
        <div class="portal-card">

          <!-- fixed role indicator (read‑only, no register link) -->
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="section-tag" style="margin-bottom:0;">Secure access</span>
            <span class="role-badge" id="roleBadge">
              <i class="fa" id="roleIcon"></i> <span id="roleDisplay">Student</span>
              <i class="fa fa-lock" style="font-size: 11px; opacity: 0.7;"></i>
            </span>
          </div>

          <!-- hidden field to hold role (set by URL param) -->
          <input type="hidden" id="roleInput" value="student">

          <h2 class="section-title" style="font-size: 28px; margin-bottom: 0;">Portal login</h2>
          <div class="divider-gold" style="margin-top: 8px;"></div>

          <!-- ONLY LOGIN FIELDS – no tabs, no register -->
          <form id="loginForm" class="mt-3" action="#" method="post">
            <div class="mb-4">
              <label class="form-label fw-semibold text-navy">Email address</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                <input type="email" class="form-control" placeholder="name@example.com" required autofocus>
              </div>
            </div>
            <div class="mb-4">
              <label class="form-label fw-semibold text-navy">Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-lock"></i></span>
                <input type="password" class="form-control" placeholder="••••••••" required>
              </div>
            </div>

            <!-- optional remember & forgot (exactly your style) -->
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember">
                <label class="form-check-label" style="font-size:14px;">Remember me</label>
              </div>
              <a href="#" style="color: var(--gold); font-size:14px;">Forgot password?</a>
            </div>

            <!-- submit button – dynamic role text (student/teacher) -->
            <button type="submit" class="btn-enroll w-100 py-3 d-flex justify-content-center align-items-center gap-2">
              <i class="fa fa-arrow-right-to-bracket"></i> Sign in as <span id="submitRoleLabel">Student</span>
            </button>

            <!-- no register links / hints – strictly login -->
            <div class="small-note text-center mt-4">
              <i class="fa fa-shield-alt me-1" style="color:var(--gold);"></i> 
              Authorised personnel only. Adaxy Academy uses multi‑factor security.
            </div>
          </form>

          <!-- additional portal links (same style as your quick links) -->
          <div class="d-flex justify-content-center gap-4 mt-4 pt-2">
            <a href="#" style="color: var(--muted); font-size:13px;"><i class="fa fa-question-circle me-1"></i>Help</a>
            <a href="#" style="color: var(--muted); font-size:13px;"><i class="fa fa-shield-alt me-1"></i>Privacy</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- mini footer (consistent) -->
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
    // ---------- ROLE DETERMINATION (from URL param) ----------
    // Exactly as requested: role is set by ?role=student or ?role=teacher, and cannot be changed.
    const urlParams = new URLSearchParams(window.location.search);
    let role = urlParams.get('role') || 'student';   // default student
    if (role !== 'student' && role !== 'teacher') role = 'student';  // sanitize

    // references
    const roleDisplay = document.getElementById('roleDisplay');
    const roleIcon = document.getElementById('roleIcon');
    const submitRoleLabel = document.getElementById('submitRoleLabel');
    const roleInput = document.getElementById('roleInput');
    roleInput.value = role;   // store hidden, if needed

    // apply role visuals
    if (role === 'teacher') {
      roleDisplay.innerText = 'Teacher';
      roleIcon.className = 'fa fa-chalkboard-teacher';
      submitRoleLabel.innerText = 'Teacher';
    } else {
      roleDisplay.innerText = 'Student';
      roleIcon.className = 'fa fa-user-graduate';
      submitRoleLabel.innerText = 'Student';
    }

    // (Optional) you could also set a different badge color, but we keep gold style.

    // ---------- INTERSECTION OBSERVER (fade‑up, exactly like your site) ----------
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

    // ---------- PREVENT FORM SUBMIT FOR DEMO (show role) ----------
    const loginForm = document.getElementById('loginForm');
    loginForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const roleText = (role === 'teacher') ? 'Teacher' : 'Student';
      alert(`🔐 Secure login attempt as ${roleText}.\n(Validation would occur server‑side.)`);
      // In a real implementation, you'd process the form.
    });

    // ---------- NO REGISTER TABS, NO WAY TO CHANGE ROLE ----------
    // The role badge is purely informational; there is no switch.
    // The interface is strictly login.
  })();
</script>
</body>
</html>
