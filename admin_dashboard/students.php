<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Adaxy Academy · Student Dashboard</title>
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

    /* stats cards (like about-card but smaller) */
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

    /* notification cards */
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

    /* dropdown cards (actions, results) */
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

    /* fee card */
    .fee-card {
      background: linear-gradient(135deg, var(--navy) 0%, #1a2a44 100%);
      border-radius: 20px;
      padding: 28px;
      color: var(--white);
    }

    .fee-row {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid rgba(255,255,255,.1);
    }

    .fee-row:last-child {
      border-bottom: none;
    }

    .fee-label {
      opacity: .7;
      font-size: 14px;
    }

    .fee-value {
      font-weight: 700;
      font-size: 16px;
    }

    .fee-tag {
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

<!-- mobile toggle button (outside sidebar) -->
<button class="sidebar-toggle" id="sidebarToggle">
  <i class="fa fa-bars me-2"></i> Menu
</button>

<div class="dashboard-wrapper">
  <!-- LEFT SIDEBAR (responsive) -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="brand-emblem mx-auto">
        <i class="fa fa-graduation-cap"></i>
      </div>
      <div style="color: var(--white); font-size: 18px; font-weight: 700; margin-top: 8px;">Adaxy Academy</div>
      <div style="font-size: 12px; color: var(--gold);">Student · Form 4B</div>
    </div>

    <nav class="nav flex-column mt-3">
      <a class="nav-link active" href="#"><i class="fa fa-chart-pie"></i> Dashboard</a>
      <a class="nav-link" href="#"><i class="fa fa-user-graduate"></i> My Profile</a>
      <a class="nav-link" href="#"><i class="fa fa-book-open"></i> Courses</a>
      <a class="nav-link" href="#"><i class="fa fa-calendar-alt"></i> Timetable</a>
      <a class="nav-link" href="#"><i class="fa fa-tasks"></i> Assignments</a>
      <a class="nav-link" href="#"><i class="fa fa-wallet"></i> Fees & Funding</a>
      <a class="nav-link" href="#"><i class="fa fa-cog"></i> Settings</a>
      <a class="nav-link" href="index.html"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </nav>

    <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,.1); margin-top: 20px;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <img src="https://placehold.co/40x40/2563EB/white?text=JD" style="border-radius: 50%; width: 40px; height: 40px; object-fit: cover;" alt="avatar">
        <div>
          <div style="color: white; font-size: 14px;">John Doe</div>
          <div style="color: rgba(255,255,255,.45); font-size: 12px;">j.doe@adaxy.mw</div>
        </div>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT (DASHBOARD) -->
  <div class="main-content">
    <!-- top welcome row -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <div>
        <h2 class="section-title" style="font-size: 26px; margin-bottom: 4px;">Welcome back, John 👋</h2>
        <p style="color: var(--muted);">Here’s your academic overview for Term 1, 2025</p>
      </div>
      <div class="d-flex gap-2">
        <a href="#" class="btn-outline-gold"><i class="fa fa-download me-2"></i>Report</a>
        <a href="#" class="btn-enroll"><i class="fa fa-message"></i> Contact</a>
      </div>
    </div>

    <!-- STATS CARDS (GPA, years remaining, notification, actions placeholder) -->
    <div class="row g-4 mb-5 fade-up visible">
      <!-- GPA -->
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex flex-column">
          <div class="stat-icon"><i class="fa fa-star"></i></div>
          <div class="stat-value">3.85 <span style="font-size: 14px; color: var(--muted);">/4.0</span></div>
          <div class="stat-label">GPA · Term 1</div>
          <div style="font-size: 12px; color: #10b981; margin-top: 8px;"><i class="fa fa-arrow-up me-1"></i> +0.2 from last term</div>
        </div>
      </div>
      <!-- Years remaining -->
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
          <div class="stat-icon"><i class="fa fa-hourglass-half"></i></div>
          <div class="stat-value">2 <span style="font-size: 16px;">years</span></div>
          <div class="stat-label">until graduation</div>
          <div style="font-size: 12px; color: var(--muted); margin-top: 8px;">Form 4 · JCE track</div>
        </div>
      </div>
      <!-- Notifications (count) -->
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
          <div class="stat-icon"><i class="fa fa-bell"></i></div>
          <div class="stat-value">3 <span style="font-size: 16px;">new</span></div>
          <div class="stat-label">notifications</div>
          <div style="font-size: 12px; color: var(--gold); margin-top: 8px;">2 unread · <a href="#" style="text-decoration: underline;">view all</a></div>
        </div>
      </div>
      <!-- Quick action (placeholder) -->
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex flex-column justify-content-between">
          <div class="stat-icon"><i class="fa fa-bolt"></i></div>
          <div class="stat-value">⚡ 4</div>
          <div class="stat-label">pending actions</div>
          <div style="font-size: 12px; color: #dc2626; margin-top: 8px;"><i class="fa fa-circle-exclamation me-1"></i> 1 requires attention</div>
        </div>
      </div>
    </div>

    <!-- NOTIFICATION DETAILS (extra block) -->
    <div class="dropdown-card mb-5 fade-up visible">
      <div class="dropdown-header">
        <h4><i class="fa fa-bell me-2" style="color:var(--gold);"></i> Recent notifications</h4>
        <span class="fee-tag">3 unread</span>
      </div>
      <div class="notif-item">
        <div class="notif-icon"><i class="fa fa-file-invoice"></i></div>
        <div>
          <div style="font-weight: 600; color: var(--navy);">Fee payment deadline extended</div>
          <div style="font-size: 13px; color: var(--muted);">Due date now 15 April 2025</div>
        </div>
        <div style="margin-left: auto; font-size: 12px; color: var(--gold);">2h ago</div>
      </div>
      <div class="notif-item">
        <div class="notif-icon"><i class="fa fa-flask"></i></div>
        <div>
          <div style="font-weight: 600; color: var(--navy);">Science project submission</div>
          <div style="font-size: 13px; color: var(--muted);">Reminder: due this Friday</div>
        </div>
        <div style="margin-left: auto; font-size: 12px; color: var(--gold);">yesterday</div>
      </div>
      <div class="notif-item">
        <div class="notif-icon"><i class="fa fa-trophy"></i></div>
        <div>
          <div style="font-weight: 600; color: var(--navy);">Merit award ceremony</div>
          <div style="font-size: 13px; color: var(--muted);">You've been nominated! 10 April</div>
        </div>
        <div style="margin-left: auto; font-size: 12px; color: var(--gold);">3d ago</div>
      </div>
    </div>

    <!-- ROW: Actions dropdown + Exam Results dropdown + Fees & Funding -->
    <div class="row g-4">
      <!-- LEFT: Actions with dropdowns (Withdraw, Raise concern, Apply for Bursary, Join class) -->
      <div class="col-lg-4 fade-up visible">
        <div class="dropdown-card h-100">
          <div class="dropdown-header">
            <h4><i class="fa fa-gear me-2" style="color:var(--gold);"></i> Actions</h4>
            <i class="fa fa-chevron-down" style="color: var(--muted);"></i>
          </div>
          <ul class="dropdown-menu-custom">
            <li><a href="#"><i class="fa fa-arrow-right-from-bracket"></i> Withdraw from course</a></li>
            <li><a href="#"><i class="fa fa-triangle-exclamation"></i> Raise a concern</a></li>
            <li><a href="#"><i class="fa fa-hand-holding-heart"></i> Apply for bursary</a></li>
            <li><a href="#"><i class="fa fa-users"></i> Join class (group)</a></li>
          </ul>
          <div class="mt-3 small text-muted ps-3">Select an action to proceed.</div>
        </div>
      </div>

      <!-- CENTER: Exam Results with dropdowns (End of term, Test, Overall summary) -->
      <div class="col-lg-4 fade-up visible">
        <div class="dropdown-card h-100">
          <div class="dropdown-header">
            <h4><i class="fa fa-file-lines me-2" style="color:var(--gold);"></i> Exam results</h4>
            <i class="fa fa-chevron-down" style="color: var(--muted);"></i>
          </div>
          <ul class="dropdown-menu-custom">
            <li><a href="#"><i class="fa fa-calendar-check"></i> End of term results (Term 1)</a></li>
            <li><a href="#"><i class="fa fa-pencil"></i> Test & quiz scores</a></li>
            <li><a href="#"><i class="fa fa-chart-simple"></i> Overall summary (GPA trend)</a></li>
          </ul>
          <!-- quick preview -->
          <div style="background: var(--cream); border-radius: 12px; padding: 12px; margin-top: 10px;">
            <div style="font-size: 13px; font-weight: 600;">Latest: Mathematics 87% (A)</div>
            <div style="font-size: 12px;">English 74% · Physics 91%</div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Fees and Funding (summary card) -->
      <div class="col-lg-4 fade-up visible">
        <div class="fee-card h-100">
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fa fa-coins" style="color: var(--gold); font-size: 24px;"></i>
            <h4 style="color: white; margin: 0;">Fees & funding</h4>
          </div>
          <div class="fee-row">
            <span class="fee-label">Tuition (2025)</span>
            <span class="fee-value">MWK 650,000</span>
          </div>
          <div class="fee-row">
            <span class="fee-label">Paid to date</span>
            <span class="fee-value" style="color: #10b981;">MWK 400,000</span>
          </div>
          <div class="fee-row">
            <span class="fee-label">Outstanding</span>
            <span class="fee-value" style="color: #f97316;">MWK 250,000</span>
          </div>
          <div class="fee-row">
            <span class="fee-label">Bursary/scholarship</span>
            <span class="fee-value">MWK 100,000 (pending)</span>
          </div>
          <div style="margin-top: 20px;">
            <span class="fee-tag"><i class="fa fa-hourglass me-1"></i> Due 30 Apr</span>
            <span class="fee-tag ms-2" style="background: transparent; border:1px solid var(--gold); color:var(--gold);">Apply for funding</span>
          </div>
          <a href="#" class="btn-enroll w-100 mt-4 justify-content-center" style="background: var(--gold-light); color: var(--navy) !important;">
            <i class="fa fa-credit-card"></i> Make payment
          </a>
        </div>
      </div>
    </div>

    <!-- extra footer note (optional) -->
    <div style="margin-top: 40px; color: var(--muted); font-size: 12px; text-align: center; border-top: 1px dashed var(--border); padding-top: 24px;">
      <i class="fa fa-lock me-1" style="color: var(--gold);"></i> secure student dashboard · Adaxy Academy
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

    // fade observer (same as main site)
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

    // (optional) make all dropdown links demo alert
    document.querySelectorAll('.dropdown-menu-custom a, .fee-tag, .btn-enroll, .btn-outline-gold').forEach(link => {
      link.addEventListener('click', (e) => {
        if (link.classList.contains('btn-enroll') || link.classList.contains('btn-outline-gold') || link.closest('.fee-tag')) {
          e.preventDefault();
          alert(`🔁 Demo: "${link.innerText}" action (simulated).`);
        }
      });
    });
  })();
</script>
</body>
</html>