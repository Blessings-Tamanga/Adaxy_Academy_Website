<?php
// ============================================================
//  Adaxy Academy · Student Timetable Portal
//  Interactive class schedule with real-time period tracking
// ============================================================

session_start();
include('../config/db_connect.php');

// ── Authentication Guard ─────────────────────────────────────
if (empty($_SESSION['slogin'])) {
    header('Location: ../Auth/login.php?role=student');
    exit;
}

$username = trim($_SESSION['slogin']);

// ── Fetch Student Information ────────────────────────────────
$stmt = $conn->prepare("
    SELECT s.*, c.class_name, c.form_level, c.programme, c.class_id, c.stream
    FROM   students s
    LEFT JOIN classes c ON c.class_id = s.class_id
    WHERE  s.username = ?
    LIMIT  1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    session_destroy();
    header('Location: ../Auth/login.php?role=student');
    exit;
}

// ── Student Data Initialization ──────────────────────────────
$full_name  = htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
$first_name = htmlspecialchars($student['first_name']);
$class_name = htmlspecialchars($student['class_name'] ?? 'Not Assigned');
$class_id   = (int)$student['class_id'];
$form_level = (int)($student['form_level'] ?? 1);
$programme  = $student['programme'] ?? 'JCE';
$stream     = htmlspecialchars($student['stream'] ?? '');
$initials   = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));

// ── Fetch Timetable with Enhanced Details ────────────────────
$stmt = $conn->prepare("
    SELECT 
        t.*,
        sub.subject_name,
        sub.subject_code,
        sub.department_id,
        d.department_name,
        CONCAT(te.first_name, ' ', te.last_name) AS teacher_name,
        te.email AS teacher_email,
        te.qualification
    FROM timetable t
    INNER JOIN subjects sub ON sub.subject_id = t.subject_id
    INNER JOIN teachers te ON te.teacher_id = t.teacher_id
    LEFT JOIN departments d ON d.department_id = sub.department_id
    WHERE t.class_id = ?
    ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), t.period_no
");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$all_periods = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Group Timetable by Day ───────────────────────────────────
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$timetable = [];
foreach ($days as $day) {
    $timetable[$day] = array_values(array_filter($all_periods, fn($p) => $p['day_of_week'] === $day));
}

// ── Current Time Information ─────────────────────────────────
$today = date('l'); // Monday, Tuesday, etc.
$current_time = date('H:i');
$current_period = null;

// Find current period if any
if (isset($timetable[$today])) {
    foreach ($timetable[$today] as $period) {
        $start = date('H:i', strtotime($period['start_time']));
        $end = date('H:i', strtotime($period['end_time']));
        if ($current_time >= $start && $current_time <= $end) {
            $current_period = $period;
            break;
        }
    }
}

// ── Next Class Calculation ───────────────────────────────────
$next_class = null;
$days_order = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$current_day_index = array_search($today, $days_order);

// Check remaining periods today
if ($current_period) {
    $current_period_no = $current_period['period_no'];
    foreach ($timetable[$today] as $period) {
        if ($period['period_no'] > $current_period_no) {
            $next_class = $period;
            $next_class['day'] = $today;
            break;
        }
    }
}

// If no more classes today, check next days
if (!$next_class) {
    for ($i = $current_day_index + 1; $i < count($days_order); $i++) {
        $next_day = $days_order[$i];
        if (!empty($timetable[$next_day])) {
            $next_class = $timetable[$next_day][0];
            $next_class['day'] = $next_day;
            break;
        }
    }
}

// ── Statistics ───────────────────────────────────────────────
$total_periods = count($all_periods);
$unique_subjects = count(array_unique(array_column($all_periods, 'subject_name')));
$days_with_classes = 0;
foreach ($days as $day) {
    if (!empty($timetable[$day])) $days_with_classes++;
}

// ── Day Colors for Visual Enhancement ────────────────────────
$day_colors = [
    'Monday'    => ['bg' => '#EFF6FF', 'border' => '#3B82F6', 'icon' => 'fa-sun'],
    'Tuesday'   => ['bg' => '#ECFDF5', 'border' => '#10B981', 'icon' => 'fa-calendar-day'],
    'Wednesday' => ['bg' => '#FEFCE8', 'border' => '#EAB308', 'icon' => 'fa-cloud-sun'],
    'Thursday'  => ['bg' => '#F5F3FF', 'border' => '#8B5CF6', 'icon' => 'fa-moon'],
    'Friday'    => ['bg' => '#FFF7ED', 'border' => '#F97316', 'icon' => 'fa-calendar-week'],
];

$conn->close();
$page_title = 'Timetable | Adaxy Academy';
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable - Adaxy Academy</title>
    <style>
        /* Custom Timetable Styles */
        :root {
            --timetable-primary: #0F2B3D;
            --timetable-accent: #2563EB;
            --timetable-success: #10B981;
            --timetable-warning: #F59E0B;
            --timetable-danger: #EF4444;
        }
        
        /* Hero Section */
        .timetable-hero {
            background: linear-gradient(135deg, var(--timetable-primary) 0%, #1A4A6F 100%);
            border-radius: 28px;
            padding: 28px 36px;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
        }
        
        .timetable-hero::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -5%;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(37,99,235,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        /* Today's Class Card */
        .current-class-card {
            background: linear-gradient(135deg, #2563EB 0%, #1E40AF 100%);
            border-radius: 24px;
            padding: 20px 28px;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
        }
        
        .current-class-card::after {
            content: 'LIVE';
            position: absolute;
            bottom: 15px;
            right: 20px;
            font-size: 48px;
            font-weight: 800;
            opacity: 0.08;
            pointer-events: none;
        }
        
        .next-class-card {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            border-radius: 24px;
            padding: 20px 28px;
            margin-bottom: 28px;
        }
        
        /* Day Cards */
        .day-card {
            background: white;
            border-radius: 24px;
            margin-bottom: 24px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            border: 1px solid #E5E7EB;
        }
        
        .day-card.today {
            border: 2px solid var(--timetable-accent);
            box-shadow: 0 8px 24px rgba(37,99,235,0.12);
        }
        
        .day-header {
            padding: 18px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            border-bottom: 1px solid #F0F2F5;
        }
        
        .day-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .day-icon {
            width: 40px;
            height: 40px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        
        .today-badge {
            background: var(--timetable-accent);
            color: white;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 40px;
            letter-spacing: 0.5px;
        }
        
        /* Period Table */
        .period-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .period-table th {
            padding: 14px 20px;
            background: #F9FAFB;
            font-size: 12px;
            font-weight: 600;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #E5E7EB;
        }
        
        .period-table td {
            padding: 14px 20px;
            border-bottom: 1px solid #F0F2F5;
            color: #374151;
        }
        
        .period-table tr:last-child td {
            border-bottom: none;
        }
        
        .period-table tr:hover td {
            background: #FAFDFF;
        }
        
        .period-badge {
            background: var(--timetable-primary);
            color: #FFD966;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 30px;
            display: inline-block;
        }
        
        .now-badge {
            background: #DCFCE7;
            color: #15803D;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 30px;
            margin-left: 10px;
            display: inline-block;
        }
        
        .subject-code-badge {
            background: #EFF6FF;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            color: var(--timetable-accent);
            display: inline-block;
            margin-right: 10px;
        }
        
        /* Stat Cards */
        .stat-card-mini {
            background: white;
            border-radius: 20px;
            padding: 16px 20px;
            text-align: center;
            transition: all 0.2s;
            border: 1px solid #E5E7EB;
        }
        
        .stat-card-mini:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        
        /* Empty State */
        .empty-timetable {
            text-align: center;
            padding: 60px 24px;
            background: white;
            border-radius: 24px;
            border: 1px solid #E5E7EB;
        }
        
        /* Time Indicator */
        .time-indicator {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(4px);
            padding: 8px 20px;
            border-radius: 50px;
        }
        
        /* Animations */
        .fade-up {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .fade-up.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .timetable-hero { padding: 20px; }
            .day-header { padding: 14px 20px; }
            .period-table th, .period-table td { padding: 10px 12px; font-size: 12px; }
            .current-class-card, .next-class-card { padding: 16px 20px; }
        }
        
        @media (max-width: 576px) {
            .period-table th:nth-child(3), .period-table td:nth-child(3),
            .period-table th:nth-child(5), .period-table td:nth-child(5) {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="timetable-container" style="max-width: 1400px; margin: 0 auto; padding: 0 24px 32px;">

    <!-- Hero Section -->
    <div class="timetable-hero fade-up">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
            <div>
                <div class="maneb-badge" style="background: rgba(255,255,255,0.15); display: inline-block; padding: 6px 16px; border-radius: 40px; font-size: 12px; color: #FFD966; margin-bottom: 16px;">
                    <i class="fa-regular fa-calendar-alt"></i> Academic Schedule
                </div>
                <h1 style="color: white; margin: 0 0 8px; font-size: 32px; font-weight: 700;">My Timetable</h1>
                <p style="color: #B0C4DE; margin-bottom: 0;">
                    <i class="fa-regular fa-building-columns"></i> <?= $class_name ?> <?= $stream ? "({$stream})" : '' ?> · 
                    <i class="fa-regular fa-graduation-cap"></i> <?= $programme ?> Form <?= $form_level ?> · 
                    <i class="fa-regular fa-calendar"></i> Academic Year <?= date('Y') ?>
                </p>
            </div>
            <div class="time-indicator">
                <i class="fa-regular fa-clock" style="color: #FFD966; font-size: 18px;"></i>
                <div>
                    <div style="font-size: 20px; font-weight: 600; color: white;" id="liveClock"><?= date('H:i') ?></div>
                    <div style="font-size: 11px; color: #B0C4DE;"><?= $today ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6 fade-up">
            <div class="stat-card-mini">
                <div style="font-size: 32px; font-weight: 700; color: var(--timetable-accent);"><?= $total_periods ?></div>
                <div class="text-muted small mt-1">Total Periods/Week</div>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.05s">
            <div class="stat-card-mini">
                <div style="font-size: 32px; font-weight: 700; color: var(--timetable-success);"><?= $unique_subjects ?></div>
                <div class="text-muted small mt-1">Subjects Enrolled</div>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.1s">
            <div class="stat-card-mini">
                <div style="font-size: 32px; font-weight: 700; color: var(--timetable-warning);"><?= $days_with_classes ?></div>
                <div class="text-muted small mt-1">Days with Classes</div>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.15s">
            <div class="stat-card-mini">
                <div style="font-size: 32px; font-weight: 700; color: var(--timetable-primary);"><?= round($total_periods / 5, 1) ?></div>
                <div class="text-muted small mt-1">Avg Periods/Day</div>
            </div>
        </div>
    </div>

    <!-- Current & Next Class Cards -->
    <?php if ($current_period): ?>
    <div class="current-class-card fade-up">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="font-size: 13px; font-weight: 500; opacity: 0.8; margin-bottom: 8px;">
                    <i class="fa-regular fa-play-circle"></i> CURRENTLY IN SESSION
                </div>
                <div style="font-size: 24px; font-weight: 700; margin-bottom: 4px;">
                    <?= htmlspecialchars($current_period['subject_name']) ?>
                </div>
                <div style="font-size: 14px; opacity: 0.9;">
                    <i class="fa-regular fa-user"></i> <?= htmlspecialchars($current_period['teacher_name']) ?> · 
                    <i class="fa-regular fa-location-dot"></i> <?= htmlspecialchars($current_period['room_no'] ?? 'Room TBD') ?>
                </div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 32px; font-weight: 700;">Period <?= $current_period['period_no'] ?></div>
                <div style="font-size: 13px; opacity: 0.8;">
                    <?= date('H:i', strtotime($current_period['start_time'])) ?> - <?= date('H:i', strtotime($current_period['end_time'])) ?>
                </div>
                <div style="margin-top: 8px;">
                    <span class="now-badge" style="background: rgba(255,255,255,0.2); color: white;">LIVE NOW</span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($next_class): ?>
    <div class="next-class-card fade-up" style="transition-delay: 0.05s">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="font-size: 13px; font-weight: 500; opacity: 0.8; margin-bottom: 8px;">
                    <i class="fa-regular fa-clock"></i> UP NEXT
                </div>
                <div style="font-size: 20px; font-weight: 700; margin-bottom: 4px;">
                    <?= htmlspecialchars($next_class['subject_name']) ?>
                </div>
                <div style="font-size: 13px; opacity: 0.9;">
                    <i class="fa-regular fa-calendar-day"></i> <?= $next_class['day'] ?> · 
                    Period <?= $next_class['period_no'] ?> · 
                    <i class="fa-regular fa-user"></i> <?= htmlspecialchars($next_class['teacher_name']) ?>
                </div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 28px; font-weight: 700;">
                    <?= date('H:i', strtotime($next_class['start_time'])) ?>
                </div>
                <div style="font-size: 12px; opacity: 0.8;">Starts at</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Timetable Grid -->
    <?php if ($all_periods): ?>
        <?php foreach ($days as $day):
            $periods = $timetable[$day];
            $is_today = ($day === $today);
            $day_style = $day_colors[$day] ?? ['bg' => '#F9FAFB', 'border' => '#E5E7EB', 'icon' => 'fa-calendar'];
        ?>
        <div class="day-card <?= $is_today ? 'today' : '' ?> fade-up" <?= $is_today ? 'style="transition-delay: 0.1s"' : '' ?>>
            <div class="day-header" style="background: <?= $day_style['bg'] ?>;">
                <div class="day-title">
                    <div class="day-icon" style="background: <?= $day_style['border'] ?>20; color: <?= $day_style['border'] ?>;">
                        <i class="fa-regular <?= $day_style['icon'] ?>"></i>
                    </div>
                    <h4 style="margin: 0; font-weight: 600; color: #1F2937;"><?= $day ?></h4>
                    <?php if ($is_today): ?>
                        <span class="today-badge">TODAY</span>
                    <?php endif; ?>
                </div>
                <div style="font-size: 13px; color: #6B7280;">
                    <i class="fa-regular fa-clock"></i> <?= count($periods) ?> period<?= count($periods) !== 1 ? 's' : '' ?>
                </div>
            </div>

            <?php if ($periods): ?>
            <div style="overflow-x: auto;">
                <table class="period-table">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Time</th>
                            <th>Room</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($periods as $p):
                            $is_current = $is_today && $current_period && 
                                         $p['period_no'] == $current_period['period_no'];
                        ?>
                        <tr style="<?= $is_current ? 'background: #EFF6FF;' : '' ?>">
                            <td style="width: 80px;">
                                <span class="period-badge">P<?= $p['period_no'] ?></span>
                            </td>
                            <td>
                                <span class="subject-code-badge"><?= htmlspecialchars($p['subject_code']) ?></span>
                                <?= htmlspecialchars($p['subject_name']) ?>
                                <?php if ($is_current): ?>
                                    <span class="now-badge">IN PROGRESS</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <i class="fa-regular fa-user-graduate" style="color: #9CA3AF; margin-right: 6px;"></i>
                                <?= htmlspecialchars($p['teacher_name']) ?>
                            </td>
                            <td>
                                <i class="fa-regular fa-clock" style="color: #9CA3AF; margin-right: 6px;"></i>
                                <?= date('H:i', strtotime($p['start_time'])) ?> – <?= date('H:i', strtotime($p['end_time'])) ?>
                            </td>
                            <td>
                                <i class="fa-regular fa-location-dot" style="color: #9CA3AF; margin-right: 6px;"></i>
                                <?= htmlspecialchars($p['room_no'] ?? '—') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div style="padding: 32px 24px; text-align: center; color: #9CA3AF;">
                <i class="fa-regular fa-bed" style="font-size: 28px; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                <p style="margin-bottom: 0;">No classes scheduled for <?= $day ?>.</p>
                <small>Enjoy your free time!</small>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-timetable fade-up">
            <i class="fa-regular fa-calendar-xmark" style="font-size: 56px; color: #CBD5E1; margin-bottom: 20px;"></i>
            <h4 style="color: #4B5563;">No Timetable Found</h4>
            <p style="color: #9CA3AF; margin-bottom: 20px;">Your class timetable hasn't been set up yet. Please contact your class teacher or academic office.</p>
            <a href="index.php" class="btn btn-primary rounded-pill px-4">
                <i class="fa-regular fa-home"></i> Return to Dashboard
            </a>
        </div>
    <?php endif; ?>

    <!-- Additional Information Footer -->
    <div class="row g-4 mt-3">
        <div class="col-md-6">
            <div class="dashboard-card" style="background: #F8FAFE; border-radius: 20px; padding: 20px;">
                <div class="d-flex align-items-center gap-3">
                    <div style="background: #EFF6FF; padding: 12px; border-radius: 16px;">
                        <i class="fa-regular fa-circle-info" style="font-size: 24px; color: #2563EB;"></i>
                    </div>
                    <div>
                        <h5 style="margin: 0 0 4px; font-size: 14px; font-weight: 600;">Timetable Notes</h5>
                        <p style="margin: 0; font-size: 13px; color: #6B7280;">
                            • Arrive 5 minutes before class starts<br>
                            • Bring your textbooks and notebook<br>
                            • Check for any changes announced by your class teacher
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dashboard-card" style="background: #F8FAFE; border-radius: 20px; padding: 20px;">
                <div class="d-flex align-items-center gap-3">
                    <div style="background: #FEF3C7; padding: 12px; border-radius: 16px;">
                        <i class="fa-regular fa-bell" style="font-size: 24px; color: #F59E0B;"></i>
                    </div>
                    <div>
                        <h5 style="margin: 0 0 4px; font-size: 14px; font-weight: 600;">Reminders</h5>
                        <p style="margin: 0; font-size: 13px; color: #6B7280;">
                            • Break: 10:30 - 10:45 AM<br>
                            • Lunch: 12:30 - 1:30 PM<br>
                            • School ends at 3:30 PM
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Live clock update
function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    const clockElement = document.getElementById('liveClock');
    if (clockElement) clockElement.textContent = timeString;
}
setInterval(updateClock, 1000);
updateClock();

// Fade up animation observer
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.05 });
    
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
});

// Optional: Highlight current period row on page load
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const currentHour = now.getHours();
    const currentMinute = now.getMinutes();
    const currentTime = currentHour * 60 + currentMinute;
    
    document.querySelectorAll('.period-table tbody tr').forEach(row => {
        const timeText = row.querySelector('td:nth-child(4)')?.textContent;
        if (timeText) {
            const times = timeText.match(/(\d{2}):(\d{2})/g);
            if (times && times.length >= 2) {
                const [start, end] = times;
                const [startHour, startMin] = start.split(':').map(Number);
                const [endHour, endMin] = end.split(':').map(Number);
                const startTotal = startHour * 60 + startMin;
                const endTotal = endHour * 60 + endMin;
                
                if (currentTime >= startTotal && currentTime <= endTotal) {
                    row.style.background = '#EFF6FF';
                    const badge = row.querySelector('.now-badge');
                    if (badge) badge.textContent = 'IN PROGRESS';
                }
            }
        }
    });
});
</script>


</body>
</html>