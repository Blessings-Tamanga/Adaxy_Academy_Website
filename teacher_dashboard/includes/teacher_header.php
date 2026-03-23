<?php
// ============================================================
//  Adaxy Academy · Teacher Header
// ============================================================

$current_page = basename($_SERVER['PHP_SELF']);

function time_ago($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return round($diff/60) . 'm ago';
    if ($diff < 86400) return round($diff/3600) . 'h ago';
    return round($diff/86400) . 'd ago';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adaxy Academy · <?= $page_title ?? 'Teacher Dashboard' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
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
            --shadow: 0 6px 24px rgba(15,23,42,.08);
            --shadow-lg: 0 18px 48px rgba(15,23,42,.16);
            --transition: .25s ease;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, "Segoe UI", Arial, sans-serif; background: var(--cream); color: var(--text); font-size: 15px; line-height: 1.7; }
        
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: var(--navy);
            color: rgba(255,255,255,.7);
            transition: all .3s;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            flex-shrink: 0;
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
        
        .sidebar .nav-link i { color: var(--gold); width: 22px; font-size: 16px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,.04);
            color: var(--white);
            border-left-color: var(--gold);
        }
        
        .main-content { flex: 1; padding: 30px 32px; background: var(--cream); overflow-x: hidden; }
        .sidebar-toggle {
            display: none;
            background: var(--navy);
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 20px;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }
        
        @media(max-width:992px){
            .dashboard-wrapper { flex-direction: column; }
            .sidebar { width: 100%; height: auto; position: relative; display: none; }
            .sidebar.show { display: block; }
            .sidebar-toggle { display: block; }
            .main-content { padding: 20px; }
        }
        
        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            border-radius: 28px;
            padding: 28px 36px;
            margin-bottom: 28px;
        }
        
        .welcome-content { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .greeting-badge {
            background: rgba(37,99,235,0.2);
            display: inline-block;
            padding: 6px 16px;
            border-radius: 40px;
            font-size: 12px;
            color: #60A5FA;
            margin-bottom: 12px;
        }
        .welcome-section h1 { color: white; font-size: 28px; margin-bottom: 8px; }
        .welcome-section p { color: #94A3B8; margin-bottom: 0; }
        .avatar-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #2563EB, #60A5FA);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 600;
            color: white;
        }
        .avatar-badge {
            text-align: center;
            margin-top: 10px;
            font-size: 12px;
            color: #60A5FA;
            background: rgba(37,99,235,0.15);
            padding: 4px 12px;
            border-radius: 40px;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            border: 1px solid #E5E7EB;
            transition: all 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .stat-icon {
            width: 52px;
            height: 52px;
            background: #EFF6FF;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #2563EB;
        }
        .stat-info h3 { font-size: 28px; font-weight: 700; color: #0F172A; margin-bottom: 4px; }
        .stat-info p { font-size: 13px; color: #6B7280; margin: 0; }
        
        /* Dashboard Grid */
        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
        .dashboard-card {
            background: white;
            border-radius: 24px;
            border: 1px solid #E5E7EB;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #EFF3F8;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .header-title { display: flex; align-items: center; gap: 12px; }
        .header-title i { font-size: 20px; color: #2563EB; }
        .header-title h3 { font-size: 18px; font-weight: 600; color: #0F172A; margin: 0; }
        .card-link { font-size: 13px; color: #2563EB; text-decoration: none; display: flex; align-items: center; gap: 6px; }
        
        /* Classes List */
        .classes-list { padding: 8px 0; }
        .class-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            border-bottom: 1px solid #F0F2F5;
        }
        .class-name { font-weight: 600; color: #0F172A; margin-bottom: 4px; }
        .class-meta { font-size: 12px; color: #6B7280; }
        .class-actions { display: flex; gap: 12px; }
        .btn-sm {
            padding: 6px 14px;
            background: #EFF6FF;
            color: #2563EB;
            border-radius: 40px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
        }
        .btn-outline { background: transparent; border: 1px solid #2563EB; }
        
        /* Actions Row */
        .actions-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 24px; }
        .action-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            border: 1px solid #E5E7EB;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
        }
        .action-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .action-card i { font-size: 32px; color: #2563EB; margin-bottom: 12px; display: block; }
        .action-card h4 { font-size: 16px; font-weight: 600; margin-bottom: 8px; }
        .action-card p { font-size: 12px; color: #6B7280; margin-bottom: 0; }
        
        /* Notices List */
        .notices-list { padding: 8px 0; }
        .notice-item {
            display: flex;
            gap: 14px;
            padding: 16px 24px;
            border-bottom: 1px solid #F0F2F5;
        }
        .notice-icon {
            width: 40px;
            height: 40px;
            background: #EFF6FF;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .notice-title { font-weight: 600; font-size: 14px; color: #0F172A; margin-bottom: 4px; }
        .notice-time { font-size: 11px; color: #9CA3AF; }
        .badge-new {
            background: #2563EB;
            color: white;
            padding: 4px 12px;
            border-radius: 40px;
            font-size: 12px;
            font-weight: 600;
        }
        
        /* Subjects List */
        .subjects-list { padding: 16px 24px; display: flex; flex-wrap: wrap; gap: 10px; }
        .subject-tag {
            background: #F1F5F9;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            color: #475569;
        }
        
        /* Stats Mini */
        .stats-mini { display: flex; justify-content: space-around; padding: 20px; }
        .stat-mini { text-align: center; }
        .stat-mini-value { display: block; font-size: 24px; font-weight: 700; color: #2563EB; }
        .stat-mini-label { font-size: 11px; color: #6B7280; }
        
        /* Empty State */
        .empty-state { text-align: center; padding: 48px 24px; color: #9CA3AF; }
        .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.5; }
        .empty-state.small { padding: 32px 24px; }
        
        /* Footer */
        .dashboard-footer { margin-top: 40px; padding-top: 24px; border-top: 1px solid #EFF3F8; }
        .footer-content { display: flex; justify-content: space-between; font-size: 12px; color: #9CA3AF; }
        
        /* Animations */
        .fade-up { opacity: 0; transform: translateY(24px); transition: opacity 0.55s ease, transform 0.55s ease; }
        .fade-up.visible { opacity: 1; transform: translateY(0); }
        
        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .dashboard-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .welcome-content { flex-direction: column; text-align: center; }
            .actions-row { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<button class="sidebar-toggle" id="sidebarToggle"><i class="fa fa-bars me-2"></i> Teacher Menu</button>

<div class="dashboard-wrapper">
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div style="width:52px;height:52px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
            <i class="fa fa-chalkboard-user" style="color:var(--navy);font-size:22px;"></i>
        </div>
        <div style="color:var(--white);font-size:18px;font-weight:700;">Adaxy Academy</div>
        <div style="font-size:12px;color:var(--gold);">Teacher · <?= htmlspecialchars($department ?? 'Staff') ?></div>
    </div>
    <nav class="nav flex-column mt-3">
        <a class="nav-link <?= $current_page==='index.php'?'active':'' ?>" href="index.php"><i class="fa fa-chart-pie"></i> Dashboard</a>
        <a class="nav-link" href="profile.php"><i class="fa fa-user-tie"></i> My Profile</a>
        <a class="nav-link" href="my_classes.php"><i class="fa fa-users"></i> My Classes</a>
        <a class="nav-link" href="timetable.php"><i class="fa fa-calendar-alt"></i> Timetable</a>
        <a class="nav-link" href="grade_entry.php"><i class="fa fa-graduation-cap"></i> Enter Grades</a>
        <a class="nav-link" href="attendance.php"><i class="fa fa-check-circle"></i> Attendance</a>
        <a class="nav-link" href="notices.php"><i class="fa fa-bell"></i> Notices</a>
        <a class="nav-link" href="settings.php"><i class="fa fa-cog"></i> Settings</a>
        <a class="nav-link" href="../Auth/logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </nav>
    <div style="padding:20px;border-top:1px solid rgba(255,255,255,.1);margin-top:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--navy);font-size:15px;">
                <?= $initials ?? 'TE' ?>
            </div>
            <div>
                <div style="color:white;font-size:14px;font-weight:600;"><?= $full_name ?? 'Teacher' ?></div>
                <div style="color:rgba(255,255,255,.45);font-size:12px;"><?= $teacher['employee_no'] ?? 'EMP001' ?></div>
            </div>
        </div>
    </div>
</div>
<div class="main-content">