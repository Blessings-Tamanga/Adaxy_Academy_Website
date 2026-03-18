<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Adaxy Academy · Teacher Dashboard</title>
  <!-- Bootstrap 5 (grid, utilities, dropdowns) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    /* === your exact design system (preserved) === */
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
    }

    h1, h2, h3, h4, h5 {
      font-family: var(--font-head);
      line-height: 1.2;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    /* === reusable components from your site === */
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
      display: inline-flex;
      align-items: center;
      gap: 8px;
      white-space: nowrap;
    }

    .btn-enroll:hover {
      background: var(--gold-light);
      box-shadow: 0 4px 16px rgba(37,99,235,.35);
      color: var(--navy);
    }

    .btn-outline-gold {
      background: transparent;
      border: 1.5px solid var(--gold);
      color: var(--navy);
      font-weight: 500;
      padding: 8px 20px;
      border-radius: 50px;
      font-size: 13px;
      transition: all var(--transition);
    }

    .btn-outline-gold:hover {
      background: var(--gold);
      color: var(--white);
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
      margin: 10px 0 20px;
    }

    /* stats cards */
    .stat-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 20px;
      box-shadow: var(--shadow);
      transition: transform var(--transition), box-shadow var(--transition);
      height: 100%;
    }

    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg);
    }

    .stat-icon {
      width: 48px;
      height: 48px;
      background: var(--navy);
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 16px;
    }

    .stat-icon i {
      color: var(--gold);
      font-size: 20px;
    }

    .stat-value {
      font-size: 28px;
      font-weight: 700;
      color: var(--navy);
      line-height: 1.2;
    }

    .stat-label {
      font-size: 13px;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: .05em;
    }

    /* notification items */
    .notif-item {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 16px;
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 14px;
      transition: all var(--transition);
    }

    .notif-item:hover {
      background: var(--cream);
      border-color: var(--gold-light);
    }

    .notif-icon {
      width: 40px;
      height: 40px;
      background: rgba(37,99,235,.1);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--gold);
      font-size: 18px;
    }

    /* dropdown cards (actions, assessments) */
    .dropdown-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: 18px;
      padding: 22px;
      box-shadow: var(--shadow);
      height: 100%;
    }

    .dropdown-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid var(--border);
      padding-bottom: 14px;
      margin-bottom: 16px;
    }

    .dropdown-header h4 {
      font-size: 18px;
      margin: 0;
      color: var(--navy);
    }

    .dropdown-menu-custom {
      list-style: none;
      padding: 0;
    }

    .dropdown-menu-custom li {
      margin-bottom: 8px;
    }

    .dropdown-menu-custom a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      border-radius: 12px;
      background: var(--cream);
      color: var(--navy);
      font-size: 14px;
      font-weight: 500;
      transition: all var(--transition);
      border: 1px solid transparent;
    }

    .dropdown-menu-custom a:hover {
      background: var(--white);
      border-color: var(--gold);
      color: var(--gold);
      transform: translateX(4px);
    }

    .dropdown-menu-custom i {
      color: var(--gold);
      width: 20px;
      text-align: center;
    }

    /* teacher workload card */
    .workload-card {
      background: linear-gradient(135deg, #1a2a44 0%, var(--navy) 100%);
      border-radius: 20px;
      padding: 28px;
      color: var(--white);
    }

    .workload-row {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid rgba(255,255,255,.1);
    }

    .workload-row:last-child {
      border-bottom: none;
    }

    .workload-label {
      opacity: .7;
      font-size: 14px;
    }

    .workload-value {
      font-weight: 700;
      font-size: 16px;
    }

    .badge-gold {
      background: var(--gold);
      color: var(--navy);
      border-radius: 50px;
      padding: 4px 12px;
      font-size: 12px;
      font-weight: 700;
    }

    /* sidebar */
    .dashboard-wrapper {
      display: flex;
      min-height: 100vh;
    }

    .sidebar {
      width: 280px;
      background: var(--navy);
      color: rgba(255,255,255,.7);
      transition: all 0.3s;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow-y: auto;
      flex-shrink: 0;
    }

    .sidebar .brand-emblem {
      margin: 0 auto 8px;
    }

    .sidebar-header {
      padding: 32px 20px 20px;
      text-align: center;
      border-bottom: 1px solid rgba(255,255,255,.1);
    }

    .sidebar .nav-link {
      color: rgba(255,255,255,.65);
      padding: 14px 24px;
      font-size: 14px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 14px;
      transition: all var(--transition);
      border-left: 3px solid transparent;
    }

    .sidebar .nav-link i {
      color: var(--gold);
      width: 22px;
      font-size: 16px;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background: rgba(255,255,255,.04);
      color: var(--white);
      border-left-color: var(--gold);
    }

    .sidebar .nav-link.active i {
      color: var(--gold-light);
    }

    .main-content {
      flex: 1;
      padding: 30px 32px;
      background: var(--cream);
      overflow-x: hidden;
    }

    /* toggle button for mobile */
    .sidebar-toggle {
      display: none;
      background: var(--navy);
      color: white;
      border: none;
      padding: 12px 20px;
      font-size: 20px;
      width: 100%;
      text-align: left;
    }

    @media (max-width: 992px) {
      .dashboard-wrapper {
        flex-direction: column;
      }
      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        display: none;
      }
      .sidebar.show {
        display: block;
      }
      .sidebar-toggle {
        display: block;
      }
      .main-content {
        padding: 20px;
      }
    }

    /* fade utility */
    .fade-up {
      opacity: 0;
      transform: translateY(28px);
      transition: opacity .55s ease, transform .55s ease;
    }

    .fade-up.visible {
      opacity: 1;
      transform: translateY(0);
    }
  </style>
</head>
<body>

<!-- mobile toggle button -->
<button class="sidebar-toggle" id="sidebarToggle">
  <i class="fa fa-bars me-2"></i> Teacher Menu
</button>

<div class="dashboard-wrapper">
  <!-- LEFT SIDEBAR (teacher) -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="brand-emblem mx-auto">
        <i class="fa fa-chalkboard-teacher"></i>
      </div>
      <div style="color: var(--white); font-size: 18px; font-weight: 700; margin-top: 8px;">Adaxy Academy</div>
      <div style="font-size: 12px; color: var(--gold);">Staff · Science Dept.</div>
    </div>

    <nav class="nav flex-column mt-3">
      <a class="nav-link active" href="#"><i class="fa fa-chart-pie"></i> Dashboard</a>
      <a class="nav-link" href="#"><i class="fa fa-user-tie"></i> My Profile</a>
      <a class="nav-link" href="#"><i class="fa fa-users"></i> My Classes</a>
      <a class="nav-link" href="#"><i class="fa fa-calendar-alt"></i> Timetable</a>
      <a class="nav-link" href="#"><i class="fa fa-tasks"></i> Assignments</a>
      <a class="nav-link" href="#"><i class="fa fa-graduation-cap"></i> Student Progress</a>
      <a class="nav-link" href="#"><i class="fa fa-cog"></i> Settings</a>
      <a class="nav-link" href="index.html"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </nav>

    <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,.1); margin-top: 20px;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <img src="https://placehold.co/40x40/2563EB/white?text=BM" style="border-radius: 50%; width: 40px; height: 40px; object-fit: cover;" alt="avatar">
        <div>
          <div style="color: white; font-size: 14px;">Mr. Bernard Mwale</div>
          <div style="color: rgba(255,255,255,.45); font-size: 12px;">b.mwale@adaxy.mw</div>
        </div>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT (TEACHER DASHBOARD) -->
  <div class="main-content">
    <!-- welcome row -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <div>
        <h2 class="section-title" style="font-size: 26px; margin-bottom: 4px;">Welcome, Mr. Mwale 👨‍🏫</h2>
        <p style="color: var(--muted);">Science department · Form 4 & 5 classes</p>
      </div>
      <div class="d-flex gap-2">
        <a href="#" class="btn-outline-gold"><i class="fa fa-download me-2"></i>Reports</a>
        <a href="#" class="btn-enroll"><i class="fa fa-message"></i> Staff room</a>
      </div>
    </div>

    <!-- STATS CARDS (classes, students, pending, years teaching) -->
    <div class="row g-4 mb-5 fade-up visible">
      <!-- Classes -->
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
          <div class="stat-icon"><i class="fa fa-book-open"></i></div>
          <div class="stat-value">4</div>
          <div class="stat-label">active classes</div>
          <div style="font-size: 12px; color: var(--muted); margin-top: 8px;">Form 4A, 4B, 5A, 5B</div>
        </div>
      </div>
      <!-- Students -->
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
          <div class="stat-icon"><i class="fa fa-users"></i></div>
          <div class="stat-value">112</div>
          <div class="stat-label">total students</div>
          <div style="font-size: 12px; color: var(--muted);">56 juniors · 56 seniors</div>
        </div>
      </div>
      <!-- Pending tasks -->
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
          <div class="stat-icon"><i class="fa fa-check-double"></i></div>
          <div class="stat-value">8</div>
          <div class="stat-label">pending tasks</div>
          <div style="font-size: 12px; color: #dc2626; margin-top: 8px;">3 need grading</div>
        </div>
      </div>
      <!-- Years at school -->
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
          <div class="stat-icon"><i class="fa fa-clock"></i></div>
          <div class="stat-value">6 <span style="font-size: 16px;">yrs</span></div>
          <div class="stat-label">at Adaxy</div>
          <div style="font-size: 12px; color: var(--gold);">since 2019</div>
        </div>
      </div>
    </div>

    <!-- NOTIFICATIONS (staff notices) -->
    <div class="dropdown-card mb-5 fade-up visible">
      <div class="dropdown-header">
        <h4><i class="fa fa-bell me-2" style="color:var(--gold);"></i> Staff notifications</h4>
        <span class="badge-gold">4 new</span>
      </div>
      <div class="notif-item">
        <div class="notif-icon"><i class="fa fa-chalkboard"></i></div>
        <div>
          <div style="font-weight: 600; color: var(--navy);">Department meeting (Science)</div>
          <div style="font-size: 13px; color: var(--muted);">Today 14:00 in Lab 3</div>
        </div>
        <div style="margin-left: auto; font-size: 12px; color: var(--gold);">1h</div>
      </div>
      <div class="notif-item">
        <div class="notif-icon"><i class="fa fa-file-pen"></i></div>
        <div>
          <div style="font-weight: 600; color: var(--navy);">Grade submission deadline</div>
          <div style="font-size: 13px; color: var(--muted);">End of term reports due 10 Apr</div>
        </div>
        <div style="margin-left: auto; font-size: 12px; color: var(--gold);">2d</div>
      </div>
      <div class="notif-item">
        <div class="notif-icon"><i class="fa fa-wallet"></i></div>
        <div>
          <div style="font-weight: 600; color: var(--navy);">Salary advance available</div>
          <div style="font-size: 13px; color: var(--muted);">Apply by 15 April</div>
        </div>
        <div style="margin-left: auto; font-size: 12px; color: var(--gold);">3d</div>
      </div>
    </div>

    <!-- ROW: Teaching Actions + Assessment Tools + Class Load & Funding -->
    <div class="row g-4">
      <!-- LEFT: Teaching actions dropdown (Attendance, Lesson plans, etc.) -->
      <div class="col-lg-4 fade-up visible">
        <div class="dropdown-card h-100">
          <div class="dropdown-header">
            <h4><i class="fa fa-chalkboard-user me-2" style="color:var(--gold);"></i> Teaching actions</h4>
            <i class="fa fa-chevron-down" style="color: var(--muted);"></i>
          </div>
          <ul class="dropdown-menu-custom">
            <li><a href="#"><i class="fa fa-check-circle"></i> Take attendance</a></li>
            <li><a href="#"><i class="fa fa-book"></i> Lesson plans</a></li>
            <li><a href="#"><i class="fa fa-hand-holding-heart"></i> Raise concern (student)</a></li>
            <li><a href="#"><i class="fa fa-users-between-lines"></i> Join staff meeting</a></li>
          </ul>
          <div class="mt-3 small text-muted ps-3">Quick access to daily tasks.</div>
        </div>
      </div>

      <!-- CENTER: Assessment tools (dropdown) -->
      <div class="col-lg-4 fade-up visible">
        <div class="dropdown-card h-100">
          <div class="dropdown-header">
            <h4><i class="fa fa-clipboard-list me-2" style="color:var(--gold);"></i> Assessment tools</h4>
            <i class="fa fa-chevron-down" style="color: var(--muted);"></i>
          </div>
          <ul class="dropdown-menu-custom">
            <li><a href="#"><i class="fa fa-pencil"></i> Enter test scores</a></li>
            <li><a href="#"><i class="fa fa-chart-line"></i> End of term results</a></li>
            <li><a href="#"><i class="fa fa-calculator"></i> Calculate final grades</a></li>
            <li><a href="#"><i class="fa fa-print"></i> Print report cards</a></li>
          </ul>
          <!-- quick preview -->
          <div style="background: var(--cream); border-radius: 12px; padding: 12px; margin-top: 10px;">
            <div style="font-size: 13px; font-weight: 600;">Pending: 23 papers to grade</div>
            <div style="font-size: 12px;">Physics (Form 4A) · 14 submissions</div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Class load & funding (similar to fee card but teacher version) -->
      <div class="col-lg-4 fade-up visible">
        <div class="workload-card h-100">
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fa fa-briefcase" style="color: var(--gold); font-size: 24px;"></i>
            <h4 style="color: white; margin: 0;">Class load & funding</h4>
          </div>
          <div class="workload-row">
            <span class="workload-label">Weekly teaching hours</span>
            <span class="workload-value">24 / 30</span>
          </div>
          <div class="workload-row">
            <span class="workload-label">Students under supervision</span>
            <span class="workload-value">112</span>
          </div>
          <div class="workload-row">
            <span class="workload-label">Department budget (2025)</span>
            <span class="workload-value">MWK 4.2M</span>
          </div>
          <div class="workload-row">
            <span class="workload-label">Requested supplies</span>
            <span class="workload-value">MWK 1.1M</span>
          </div>
          <div style="margin-top: 20px;">
            <span class="badge-gold"><i class="fa fa-hourglass me-1"></i> Budget review: 12 Apr</span>
            <span class="badge-gold ms-2" style="background: transparent; border:1px solid var(--gold); color:var(--gold);">Apply for grant</span>
          </div>
          <a href="#" class="btn-enroll w-100 mt-4 justify-content-center" style="background: var(--gold-light); color: var(--navy) !important;">
            <i class="fa fa-coins"></i> Funding details
          </a>
        </div>
      </div>
    </div>

    <!-- extra footer note -->
    <div style="margin-top: 40px; color: var(--muted); font-size: 12px; text-align: center; border-top: 1px dashed var(--border); padding-top: 24px;">
      <i class="fa fa-lock me-1" style="color: var(--gold);"></i> secure teacher dashboard · Adaxy Academy
    </div>
  </div>
</div>

<!-- scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  (function() {
    // sidebar toggle for mobile
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('show');
      });
    }

    // fade observer
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

    // demo alert for interactive elements (optional)
    document.querySelectorAll('.dropdown-menu-custom a, .badge-gold, .btn-enroll, .btn-outline-gold').forEach(link => {
      link.addEventListener('click', (e) => {
        if (link.classList.contains('btn-enroll') || link.classList.contains('btn-outline-gold') || link.closest('.badge-gold')) {
          e.preventDefault();
          alert(`🔁 Demo: "${link.innerText}" action (simulated).`);
        }
      });
    });
  })();
</script>
</body>
</html>