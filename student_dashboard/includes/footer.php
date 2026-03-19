<?php
// student_dashboard/inc/header.php
// Include this at the top of every student page
// Requires $student, $class_name, $full_name, $initials to already be set

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Adaxy Academy · <?= $page_title ?? 'Student Dashboard' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    :root {
      --navy:#0F172A; --navy-mid:#1F2937; --gold:#2563EB; --gold-light:#60A5FA;
      --cream:#F8FAFC; --white:#FFFFFF; --text:#111827; --muted:#6B7280;
      --border:#E5E7EB; --radius:10px;
      --shadow:0 6px 24px rgba(15,23,42,.08);
      --shadow-lg:0 18px 48px rgba(15,23,42,.16);
      --font-head:system-ui,-apple-system,"Segoe UI",Arial,sans-serif;
      --font-body:system-ui,-apple-system,"Segoe UI",Arial,sans-serif;
      --transition:.25s ease;
    }
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:var(--font-body);background:var(--cream);color:var(--text);font-size:15px;line-height:1.7;}
    h1,h2,h3,h4,h5{font-family:var(--font-head);line-height:1.2;}
    a{text-decoration:none;color:inherit;}

    .btn-enroll{background:var(--gold);color:var(--navy)!important;font-weight:600;font-size:13px;padding:9px 22px;border-radius:50px;letter-spacing:.02em;transition:background var(--transition),box-shadow var(--transition);border:none;display:inline-flex;align-items:center;gap:8px;white-space:nowrap;}
    .btn-enroll:hover{background:var(--gold-light);box-shadow:0 4px 16px rgba(37,99,235,.35);color:var(--navy);}
    .btn-outline-gold{background:transparent;border:1.5px solid var(--gold);color:var(--navy);font-weight:500;padding:8px 20px;border-radius:50px;font-size:13px;transition:all var(--transition);cursor:pointer;}
    .btn-outline-gold:hover{background:var(--gold);color:var(--white);}
    .btn-danger-soft{background:#fee2e2;color:#b91c1c;border:1.5px solid #fecaca;font-weight:500;padding:8px 20px;border-radius:50px;font-size:13px;transition:all var(--transition);cursor:pointer;}
    .btn-danger-soft:hover{background:#b91c1c;color:var(--white);}

    .section-tag{display:inline-block;color:var(--gold);font-size:12px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;margin-bottom:12px;}
    .section-tag::before{content:'—';margin-right:8px;}
    .divider-gold{width:52px;height:3px;background:var(--gold);border-radius:2px;margin:10px 0 20px;}

    .card-box{background:var(--white);border:1px solid var(--border);border-radius:18px;padding:28px;box-shadow:var(--shadow);}
    .card-box-header{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border);padding-bottom:16px;margin-bottom:22px;}
    .card-box-header h4{font-size:18px;margin:0;color:var(--navy);}

    .form-label{font-size:13.5px;font-weight:600;color:var(--navy);margin-bottom:6px;}
    .form-control,.form-select{border:1.5px solid var(--border);border-radius:12px;padding:11px 14px;font-size:14px;transition:all var(--transition);background:var(--white);}
    .form-control:focus,.form-select:focus{border-color:var(--gold);box-shadow:0 0 0 4px rgba(37,99,235,.12);outline:none;}

    .alert-success-custom{background:#dcfce7;border:1px solid #86efac;color:#15803d;border-radius:12px;padding:14px 18px;font-size:14px;margin-bottom:20px;}
    .alert-error-custom{background:#fee2e2;border:1px solid #fca5a5;color:#b91c1c;border-radius:12px;padding:14px 18px;font-size:14px;margin-bottom:20px;}

    /* sidebar */
    .dashboard-wrapper{display:flex;min-height:100vh;}
    .sidebar{width:280px;background:var(--navy);color:rgba(255,255,255,.7);transition:all .3s;position:sticky;top:0;height:100vh;overflow-y:auto;flex-shrink:0;}
    .sidebar-header{padding:32px 20px 20px;text-align:center;border-bottom:1px solid rgba(255,255,255,.1);}
    .sidebar .nav-link{color:rgba(255,255,255,.65);padding:14px 24px;font-size:14px;font-weight:500;display:flex;align-items:center;gap:14px;transition:all var(--transition);border-left:3px solid transparent;}
    .sidebar .nav-link i{color:var(--gold);width:22px;font-size:16px;}
    .sidebar .nav-link:hover,.sidebar .nav-link.active{background:rgba(255,255,255,.04);color:var(--white);border-left-color:var(--gold);}
    .sidebar .nav-link.active i{color:var(--gold-light);}
    .main-content{flex:1;padding:30px 32px;background:var(--cream);overflow-x:hidden;}
    .sidebar-toggle{display:none;background:var(--navy);color:white;border:none;padding:12px 20px;font-size:20px;width:100%;text-align:left;}

    @media(max-width:992px){
      .dashboard-wrapper{flex-direction:column;}
      .sidebar{width:100%;height:auto;position:relative;display:none;}
      .sidebar.show{display:block;}
      .sidebar-toggle{display:block;}
      .main-content{padding:20px;}
    }

    .fade-up{opacity:0;transform:translateY(28px);transition:opacity .55s ease,transform .55s ease;}
    .fade-up.visible{opacity:1;transform:translateY(0);}

    .info-row{display:flex;justify-content:space-between;align-items:center;padding:13px 0;border-bottom:1px solid var(--border);}
    .info-row:last-child{border-bottom:none;}
    .info-label{font-size:13px;color:var(--muted);font-weight:500;}
    .info-value{font-size:14px;color:var(--navy);font-weight:600;}

    .badge-pill{display:inline-block;padding:3px 12px;border-radius:50px;font-size:12px;font-weight:700;}
    .badge-a{background:#dcfce7;color:#15803d;}
    .badge-b{background:#dbeafe;color:#1d4ed8;}
    .badge-c{background:#fef9c3;color:#854d0e;}
    .badge-d{background:#ffedd5;color:#c2410c;}
    .badge-f{background:#fee2e2;color:#b91c1c;}
    .badge-active{background:#dcfce7;color:#15803d;}
    .badge-pending{background:#fef9c3;color:#854d0e;}
    .badge-closed{background:#f1f5f9;color:#64748b;}
  </style>
</head>
<body>

<button class="sidebar-toggle" id="sidebarToggle"><i class="fa fa-bars me-2"></i> Menu</button>

<div class="dashboard-wrapper">
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div style="width:52px;height:52px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
      <i class="fa fa-graduation-cap" style="color:var(--navy);font-size:22px;"></i>
    </div>
    <div style="color:var(--white);font-size:18px;font-weight:700;">Adaxy Academy</div>
    <div style="font-size:12px;color:var(--gold);">Student · <?= htmlspecialchars($class_name) ?></div>
  </div>
  <nav class="nav flex-column mt-3">
    <a class="nav-link <?= $current_page==='index.php'?'active':'' ?>" href="index.php"><i class="fa fa-chart-pie"></i> Dashboard</a>
    <a class="nav-link <?= $current_page==='profile.php'?'active':'' ?>" href="profile.php"><i class="fa fa-user-graduate"></i> My Profile</a>
    <a class="nav-link <?= $current_page==='grades.php'?'active':'' ?>" href="grades.php"><i class="fa fa-file-lines"></i> My Grades</a>
    <a class="nav-link <?= $current_page==='timetable.php'?'active':'' ?>" href="timetable.php"><i class="fa fa-calendar-alt"></i> Timetable</a>
    <a class="nav-link <?= $current_page==='notices.php'?'active':'' ?>" href="notices.php"><i class="fa fa-bell"></i> Notices</a>
    <a class="nav-link <?= $current_page==='concern.php'?'active':'' ?>" href="concern.php"><i class="fa fa-triangle-exclamation"></i> Raise Concern</a>
    <a class="nav-link <?= $current_page==='bursary.php'?'active':'' ?>" href="bursary.php"><i class="fa fa-hand-holding-heart"></i> Bursary</a>
    <a class="nav-link <?= $current_page==='settings.php'?'active':'' ?>" href="settings.php"><i class="fa fa-cog"></i> Settings</a>
    <a class="nav-link" href="../Auth/logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
  </nav>
  <div style="padding:20px;border-top:1px solid rgba(255,255,255,.1);margin-top:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
      <div style="width:40px;height:40px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--navy);font-size:15px;flex-shrink:0;">
        <?= $initials ?>
      </div>
      <div>
        <div style="color:white;font-size:14px;font-weight:600;"><?= htmlspecialchars($full_name) ?></div>
        <div style="color:rgba(255,255,255,.45);font-size:12px;"><?= htmlspecialchars($student['roll_number']) ?></div>
      </div>
    </div>
  </div>
</div>
<div class="main-content">