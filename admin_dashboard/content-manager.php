<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Adaxy Academy · Admin Dashboard</title>
  <!-- Bootstrap 5 (grid, utilities) -->
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

    /* notification items (dual purpose) */
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

    /* dropdown cards (admin actions) */
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

    /* admin summary card */
    .summary-card {
      background: linear-gradient(135deg, #1a2a44 0%, var(--navy) 100%);
      border-radius: 20px;
      padding: 28px;
      color: var(--white);
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid rgba(255,255,255,.1);
    }

    .summary-row:last-child {
      border-bottom: none;
    }

    .summary-label {
      opacity: .7;
      font-size: 14px;
    }

    .summary-value {
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

    /* dual highlight */
    .stat-sub {
      font-size: 12px;
      margin-top: 6px;
      color: var(--muted);
    }
  </style>
</head>
<body>

<!-- mobile toggle button -->
<button class="sidebar-toggle" id="sidebarToggle">
  <i class="fa fa-bars me-2"></i> Admin Menu
</button>

<div class="dashboard-wrapper">
  <!-- LEFT SIDEBAR (admin) -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="brand-emblem mx-auto">
        <i class="fa fa-user-tie"></i>
      </div>
      <div style="color: var(--white); font-size: 18px; font-weight: 700; margin-top: 8px;">Adaxy Academy</div>
      <div style="font-size: 12px; color: var(--gold);">Head of Administration</div>
    </div>

    <nav class="nav flex-column mt-3">
      <a class="nav-link active" href="#"><i class="fa fa-chart-pie"></i> Dashboard</a>
      <a class="nav-link" href="#"><i class="fa fa-users"></i> All Students</a>
      <a class="nav-link" href="#"><i class="fa fa-chalkboard-user"></i> All Teachers</a>
      <a class="nav-link" href="#"><i class="fa fa-wallet"></i> Finance & Fees</a>
      <a class="nav-link" href="#"><i class="fa fa-calendar-alt"></i> Academic Calendar</a>
      <a class="nav-link" href="#"><i class="fa fa-file-invoice"></i> Reports</a>
      <a class="nav-link" href="#"><i class="fa fa-cog"></i> Settings</a>
      <a class="nav-link" href="index.html"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </nav>

    <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,.1); margin-top: 20px;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <img src="https://placehold.co/40x40/2563EB/white?text=AS" style="border-radius: 50%; width: 40px; height: 40px; object-fit: cover;" alt="avatar">
        <div>
          <div style="color: white; font-size: 14px;">Mrs. Alile Soko</div>
          <div style="color: rgba(255,255,255,.45); font-size: 12px;">a.soko@adaxy.mw</div>
        </div>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT (ADMIN DASHBOARD) -->
  <div class="main-content">
    <!-- welcome row -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <div>
        <h2 class="section-title" style="font-size: 26px; margin-bottom: 4px;">Welcome, Mrs. Soko 📋</h2>
        <p style="color: var(--muted);">Complete oversight · students, teachers, and institutional health</p>
      </div>
      <div class="d-flex gap-2">
        <a href="#" class="btn-outline-gold"><i class="fa fa-download me-2"></i>Export</a>
        <a href="#" class="btn-enroll"><i class="fa fa-plus-circle"></i> New entry</a>
      </div>
    </div>

    <!-- STATS CARDS (monitor everything) -->
    <div class="row g-4 mb-5 fade-up visible">
      <!-- Total students -->
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
          <div class="stat-icon"><i class="fa fa-user-graduate"></i></div>
          <div class="stat-value">486</div>
          <div class="stat-label">enrolled students</div>
          <div class="stat-sub">♂ 252 · ♀ 234 · +12 from 2024</div>
        </div>
      </div>
      <!-- Total teachers -->
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
          <div class="stat-icon"><i class="fa fa-chalkboard-user"></i></div>
          <div class="stat-value">38</div>
          <div class="stat-label">teaching staff</div>
          <div class="stat-sub">full‑time 32 · part‑time 6</div>
        </div>
      </div>
      <!-- Financial summary (fees collected) -->
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
          <div class="stat-icon"><i class="fa fa-coins"></i></div>
          <div class="stat-value">MWK 14.2M</div>
          <div class="stat-label">fees collected (2025)</div>
          <div class="stat-sub">82% of target · MWK 3.1M outstanding</div>
        </div>
      </div>
      <!-- Pending items (applications/concerns) -->
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
          <div class="stat-icon"><i class="fa fa-clock"></i></div>
          <div class="stat-value">23</div>
          <div class="stat-label">pending requests</div>
          <div class="stat-sub">12 bursary · 8 transfers · 3 concerns</div>
        </div>
      </div>
    </div>

    <!-- NOTIFICATION PANELS (students & teachers combined) -->
    <div class="row g-4 mb-5">
      <!-- Student notifications -->
      <div class="col-md-6 fade-up visible">
        <div class="dropdown-card">
          <div class="dropdown-header">
            <h4><i class="fa fa-bell me-2" style="color:var(--gold);"></i> Student updates</h4>
            <span class="badge-gold">7 new</span>
          </div>
          <div class="notif-item">
            <div class="notif-icon"><i class="fa fa-file-signature"></i></div>
            <div>
              <div style="font-weight: 600; color: var(--navy);">Bursary applications</div>
              <div style="font-size: 13px; color: var(--muted);">5 new submissions pending review</div>
            </div>
            <div style="margin-left: auto; font-size: 12px; color: var(--gold);">today</div>
          </div>
          <div class="notif-item">
            <div class="notif-icon"><i class="fa fa-arrow-right-from-bracket"></i></div>
            <div>
              <div style="font-weight: 600; color: var(--navy);">Withdrawal requests</div>
              <div style="font-size: 13px; color: var(--muted);">2 students (Form 4A, 5B)</div>
            </div>
            <div style="margin-left: auto; font-size: 12px; color: var(--gold);">yesterday</div>
          </div>
          <div class="notif-item">
            <div class="notif-icon"><i class="fa fa-triangle-exclamation"></i></div>
            <div>
              <div style="font-weight: 600; color: var(--navy);">Raised concerns</div>
              <div style="font-size: 13px; color: var(--muted);">3 active (academic/personal)</div>
            </div>
            <div style="margin-left: auto; font-size: 12px; color: var(--gold);">2d</div>
          </div>
        </div>
      </div>
      <!-- Teacher notifications -->
      <div class="col-md-6 fade-up visible">
        <div class="dropdown-card">
          <div class="dropdown-header">
            <h4><i class="fa fa-chalkboard me-2" style="color:var(--gold);"></i> Teacher updates</h4>
            <span class="badge-gold">4 new</span>
          </div>
          <div class="notif-item">
            <div class="notif-icon"><i class="fa fa-clock"></i></div>
            <div>
              <div style="font-weight: 600; color: var(--navy);">Leave requests</div>
              <div style="font-size: 13px; color: var(--muted);">2 pending approval</div>
            </div>
            <div style="margin-left: auto; font-size: 12px; color: var(--gold);">1h</div>
          </div>
          <div class="notif-item">
            <div class="notif-icon"><i class="fa fa-chart-line"></i></div>
            <div>
              <div style="font-weight: 600; color: var(--navy);">Grade submissions</div>
              <div style="font-size: 13px; color: var(--muted);">4 classes still missing</div>
            </div>
            <div style="margin-left: auto; font-size: 12px; color: var(--gold);">3d</div>
          </div>
          <div class="notif-item">
            <div class="notif-icon"><i class="fa fa-briefcase"></i></div>
            <div>
              <div style="font-weight: 600; color: var(--navy);">New vacancy applications</div>
              <div style="font-size: 13px; color: var(--muted);">6 applicants for Physics post</div>
            </div>
            <div style="margin-left: auto; font-size: 12px; color: var(--gold);">5d</div>
          </div>
        </div>
      </div>
    </div>

    <!-- ROW: Student Affairs dropdown + Teacher/Staff Actions + Institutional Summary -->
    <div class="row g-4">
      <!-- LEFT: Student Affairs dropdown (withdraw, bursary, join class, raise concern) -->
      <div class="col-lg-4 fade-up visible">
        <div class="dropdown-card h-100">
          <div class="dropdown-header">
            <h4><i class="fa fa-user-graduate me-2" style="color:var(--gold);"></i> Student affairs</h4>
            <i class="fa fa-chevron-down" style="color: var(--muted);"></i>
          </div>
          <ul class="dropdown-menu-custom">
            <li><a href="#"><i class="fa fa-arrow-right-from-bracket"></i> Process withdrawal</a></li>
            <li><a href="#"><i class="fa fa-hand-holding-heart"></i> Review bursary apps</a></li>
            <li><a href="#"><i class="fa fa-users-between-lines"></i> Join class / transfer</a></li>
            <li><a href="#"><i class="fa fa-triangle-exclamation"></i> Handle concern</a></li>
          </ul>
          <div class="mt-3 small text-muted ps-3">Quick actions for student management.</div>
        </div>
      </div>

      <!-- CENTER: Teacher/Staff Actions dropdown -->
      <div class="col-lg-4 fade-up visible">
        <div class="dropdown-card h-100">
          <div class="dropdown-header">
            <h4><i class="fa fa-chalkboard-user me-2" style="color:var(--gold);"></i> Teacher/staff actions</h4>
            <i class="fa fa-chevron-down" style="color: var(--muted);"></i>
          </div>
          <ul class="dropdown-menu-custom">
            <li><a href="#"><i class="fa fa-user-plus"></i> Hire / assign teacher</a></li>
            <li><a href="#"><i class="fa fa-calendar-check"></i> Approve leave</a></li>
            <li><a href="#"><i class="fa fa-chart-simple"></i> Monitor performance</a></li>
            <li><a href="#"><i class="fa fa-file-invoice"></i> Salary processing</a></li>
          </ul>
          <!-- quick preview -->
          <div style="background: var(--cream); border-radius: 12px; padding: 12px; margin-top: 10px;">
            <div style="font-size: 13px; font-weight: 600;">Next payroll: 28 March 2025</div>
            <div style="font-size: 12px;">38 staff · total MWK 21.4M</div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Institutional summary (fees, funding, everything) -->
      <div class="col-lg-4 fade-up visible">
        <div class="summary-card h-100">
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fa fa-landmark" style="color: var(--gold); font-size: 24px;"></i>
            <h4 style="color: white; margin: 0;">Institutional summary</h4>
          </div>
          <div class="summary-row">
            <span class="summary-label">Total students (active)</span>
            <span class="summary-value">486</span>
          </div>
          <div class="summary-row">
            <span class="summary-label">Total teachers</span>
            <span class="summary-value">38</span>
          </div>
          <div class="summary-row">
            <span class="summary-label">Student/teacher ratio</span>
            <span class="summary-value">12.8 : 1</span>
          </div>
          <div class="summary-row">
            <span class="summary-label">Annual budget (2025)</span>
            <span class="summary-value">MWK 87.2M</span>
          </div>
          <div class="summary-row">
            <span class="summary-label">Fees collected</span>
            <span class="summary-value">MWK 14.2M</span>
          </div>
          <div class="summary-row">
            <span class="summary-label">Government grant</span>
            <span class="summary-value">MWK 22.5M</span>
          </div>
          <div style="margin-top: 20px;">
            <span class="badge-gold"><i class="fa fa-hourglass me-1"></i> Audit due 30 Apr</span>
            <span class="badge-gold ms-2" style="background: transparent; border:1px solid var(--gold); color:var(--gold);">Funding report</span>
          </div>
          <a href="#" class="btn-enroll w-100 mt-4 justify-content-center" style="background: var(--gold-light); color: var(--navy) !important;">
            <i class="fa fa-file-invoice"></i> Full financial overview
          </a>
        </div>
      </div>
    </div>

    <!-- footer note -->
    <div style="margin-top: 40px; color: var(--muted); font-size: 12px; text-align: center; border-top: 1px dashed var(--border); padding-top: 24px;">
      <i class="fa fa-lock me-1" style="color: var(--gold);"></i> secure admin dashboard · Adaxy Academy · head of administration
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

    // demo alert for interactive elements
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