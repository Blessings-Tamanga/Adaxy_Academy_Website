<?php
// ============================================================
//  Adaxy Academy · Teacher Timetable
//  View your weekly teaching schedule
// ============================================================

session_start();
include('../config/db_connect.php');

// ── Authentication Guard ─────────────────────────────────────
if (empty($_SESSION['tlogin'])) {
    header('Location: ../Auth/login.php?role=teacher');
    exit;
}

$username = $_SESSION['tlogin'];

// ── Fetch Teacher Information ────────────────────────────────
$stmt = $conn->prepare("
    SELECT t.*, d.department_name
    FROM teachers t
    LEFT JOIN departments d ON d.department_id = t.department_id
    WHERE t.username = ?
    LIMIT 1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$teacher) {
    session_destroy();
    header('Location: ../Auth/login.php?role=teacher');
    exit;
}

$teacher_id = (int)$teacher['teacher_id'];
$full_name = $teacher['first_name'] . ' ' . $teacher['last_name'];
$first_name = $teacher['first_name'];
$department = $teacher['department_name'] ?? 'Not Assigned';
$initials = strtoupper(substr($teacher['first_name'],0,1) . substr($teacher['last_name'],0,1));

// ── Fetch Teacher's Timetable ─────────────────────────────────
$stmt = $conn->prepare("
    SELECT 
        t.*,
        c.class_name,
        c.form_level,
        c.programme,
        sub.subject_name,
        sub.subject_code,
        sub.department_id
    FROM timetable t
    INNER JOIN classes c ON c.class_id = t.class_id
    INNER JOIN subjects sub ON sub.subject_id = t.subject_id
    WHERE t.teacher_id = ?
    ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), t.period_no
");
$stmt->bind_param("i", $teacher_id);
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
$today = date('l');
$current_time = date('H:i');
$current_period = null;

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
$unique_classes = count(array_unique(array_column($all_periods, 'class_name')));

$days_with_classes = 0;
foreach ($days as $day) {
    if (!empty($timetable[$day])) $days_with_classes++;
}

// ── Day Colors ───────────────────────────────────────────────
$day_colors = [
    'Monday'    => ['bg' => '#EFF6FF', 'border' => '#3B82F6', 'icon' => 'fa-sun'],
    'Tuesday'   => ['bg' => '#ECFDF5', 'border' => '#10B981', 'icon' => 'fa-calendar-day'],
    'Wednesday' => ['bg' => '#FEFCE8', 'border' => '#EAB308', 'icon' => 'fa-cloud-sun'],
    'Thursday'  => ['bg' => '#F5F3FF', 'border' => '#8B5CF6', 'icon' => 'fa-moon'],
    'Friday'    => ['bg' => '#FFF7ED', 'border' => '#F97316', 'icon' => 'fa-calendar-week'],
];

$conn->close();
$page_title = 'My Timetable';
include 'includes/teacher_header.php';
?>

<style>
    .timetable-container {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .current-class-card {
        background: linear-gradient(135deg, #2563EB 0%, #1E40AF 100%);
        border-radius: 24px;
        padding: 24px 28px;
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
        padding: 24px 28px;
        margin-bottom: 28px;
    }
    
    .day-card {
        background: white;
        border-radius: 24px;
        margin-bottom: 24px;
        overflow: hidden;
        border: 1px solid #E5E7EB;
        transition: all 0.2s;
    }
    
    .day-card.today {
        border: 2px solid #2563EB;
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
        background: #2563EB;
        color: white;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 40px;
    }
    
    .timetable-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .timetable-table th {
        padding: 14px 20px;
        background: #F9FAFB;
        font-size: 12px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        border-bottom: 1px solid #E5E7EB;
        text-align: left;
    }
    
    .timetable-table td {
        padding: 14px 20px;
        border-bottom: 1px solid #F0F2F5;
        color: #374151;
    }
    
    .timetable-table tr:last-child td {
        border-bottom: none;
    }
    
    .timetable-table tr:hover td {
        background: #FAFDFF;
    }
    
    .period-badge {
        background: #0F172A;
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
        color: #2563EB;
        display: inline-block;
        margin-right: 10px;
    }
    
    .stat-card-mini {
        background: white;
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        border: 1px solid #E5E7EB;
        transition: all 0.2s;
    }
    
    .stat-card-mini:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }
    
    .time-indicator {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(4px);
        padding: 8px 20px;
        border-radius: 50px;
    }
    
    .empty-timetable {
        text-align: center;
        padding: 60px 24px;
        background: white;
        border-radius: 24px;
        border: 1px solid #E5E7EB;
    }
    
    .info-box {
        background: #F8FAFE;
        border-radius: 20px;
        padding: 20px;
        border: 1px solid #E5E7EB;
    }
    
    @media (max-width: 768px) {
        .day-header {
            padding: 14px 20px;
        }
        .timetable-table th,
        .timetable-table td {
            padding: 10px 12px;
            font-size: 12px;
        }
        .current-class-card,
        .next-class-card {
            padding: 16px 20px;
        }
        .time-indicator {
            padding: 6px 16px;
        }
    }
    
    @media (max-width: 576px) {
        .timetable-table th:nth-child(4),
        .timetable-table td:nth-child(4) {
            display: none;
        }
    }
</style>

<div class="timetable-container" style="padding: 0 20px 40px;">

    <!-- Header -->
    <div class="welcome-section fade-up">
        <div class="welcome-content">
            <div>
                <div class="greeting-badge">
                    <i class="fas fa-calendar-alt"></i> Teaching Schedule
                </div>
                <h1>My Timetable</h1>
                <p>Your weekly teaching schedule</p>
            </div>
            <div class="teacher-avatar">
                <div class="avatar-circle">
                    <span><?= $initials ?></span>
                </div>
                <div class="avatar-badge">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($department) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6 fade-up">
            <div class="stat-card-mini">
                <div style="font-size: 32px; font-weight: 700; color: #2563EB;"><?= $total_periods ?></div>
                <div class="text-muted small mt-1">Total Periods/Week</div>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.05s">
            <div class="stat-card-mini">
                <div style="font-size: 32px; font-weight: 700; color: #10B981;"><?= $unique_subjects ?></div>
                <div class="text-muted small mt-1">Subjects</div>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.1s">
            <div class="stat-card-mini">
                <div style="font-size: 32px; font-weight: 700; color: #F59E0B;"><?= $unique_classes ?></div>
                <div class="text-muted small mt-1">Classes</div>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.15s">
            <div class="stat-card-mini">
                <div style="font-size: 32px; font-weight: 700; color: #8B5CF6;"><?= $days_with_classes ?></div>
                <div class="text-muted small mt-1">Teaching Days</div>
            </div>
        </div>
    </div>

    <!-- Current & Next Class Cards -->
    <?php if ($current_period): ?>
    <div class="current-class-card fade-up">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div style="font-size: 13px; font-weight: 500; opacity: 0.8; margin-bottom: 8px;">
                    <i class="fas fa-play-circle"></i> CURRENTLY TEACHING
                </div>
                <div style="font-size: 24px; font-weight: 700; margin-bottom: 4px;">
                    <?= htmlspecialchars($current_period['subject_name']) ?>
                </div>
                <div style="font-size: 14px; opacity: 0.9;">
                    <i class="fas fa-users"></i> <?= htmlspecialchars($current_period['class_name']) ?> · 
                    <i class="fas fa-location-dot"></i> <?= htmlspecialchars($current_period['room_no'] ?? 'Room TBD') ?>
                </div>
            </div>
            <div class="text-center">
                <div style="font-size: 32px; font-weight: 700;">Period <?= $current_period['period_no'] ?></div>
                <div style="font-size: 13px; opacity: 0.8;">
                    <?= date('H:i', strtotime($current_period['start_time'])) ?> - <?= date('H:i', strtotime($current_period['end_time'])) ?>
                </div>
                <div class="mt-2">
                    <span class="now-badge" style="background: rgba(255,255,255,0.2); color: white;">IN PROGRESS</span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($next_class): ?>
    <div class="next-class-card fade-up" style="transition-delay: 0.05s">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div style="font-size: 13px; font-weight: 500; opacity: 0.8; margin-bottom: 8px;">
                    <i class="fas fa-clock"></i> UP NEXT
                </div>
                <div style="font-size: 20px; font-weight: 700; margin-bottom: 4px;">
                    <?= htmlspecialchars($next_class['subject_name']) ?>
                </div>
                <div style="font-size: 13px; opacity: 0.9;">
                    <i class="fas fa-calendar-day"></i> <?= $next_class['day'] ?> · 
                    <i class="fas fa-users"></i> <?= htmlspecialchars($next_class['class_name']) ?>
                </div>
            </div>
            <div class="text-center">
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
                        <i class="fas <?= $day_style['icon'] ?>"></i>
                    </div>
                    <h4 style="margin: 0; font-weight: 600; color: #1F2937;"><?= $day ?></h4>
                    <?php if ($is_today): ?>
                        <span class="today-badge">TODAY</span>
                    <?php endif; ?>
                </div>
                <div style="font-size: 13px; color: #6B7280;">
                    <i class="fas fa-clock"></i> <?= count($periods) ?> period<?= count($periods) !== 1 ? 's' : '' ?>
                </div>
            </div>

            <?php if ($periods): ?>
            <div style="overflow-x: auto;">
                <table class="timetable-table">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Subject</th>
                            <th>Class</th>
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
                                <i class="fas fa-users" style="color: #9CA3AF; margin-right: 6px;"></i>
                                <?= htmlspecialchars($p['class_name']) ?> (Form <?= $p['form_level'] ?>)
                            </td>
                            <td>
                                <i class="fas fa-clock" style="color: #9CA3AF; margin-right: 6px;"></i>
                                <?= date('H:i', strtotime($p['start_time'])) ?> – <?= date('H:i', strtotime($p['end_time'])) ?>
                            </td>
                            <td>
                                <i class="fas fa-location-dot" style="color: #9CA3AF; margin-right: 6px;"></i>
                                <?= htmlspecialchars($p['room_no'] ?? '—') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div style="padding: 32px 24px; text-align: center; color: #9CA3AF;">
                <i class="fas fa-bed" style="font-size: 28px; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                <p>No classes scheduled for <?= $day ?>.</p>
                <small>Enjoy your free time!</small>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        
        <!-- Info Box -->
        <div class="row g-4 mt-2">
            <div class="col-md-6">
                <div class="info-box">
                    <div class="d-flex align-items-center gap-3">
                        <div style="background: #EFF6FF; padding: 12px; border-radius: 16px;">
                            <i class="fas fa-circle-info" style="font-size: 24px; color: #2563EB;"></i>
                        </div>
                        <div>
                            <h5 style="margin: 0 0 4px; font-size: 14px; font-weight: 600;">Timetable Notes</h5>
                            <p style="margin: 0; font-size: 13px; color: #6B7280;">
                                • Arrive 5 minutes before class starts<br>
                                • Prepare lesson materials in advance<br>
                                • Check for any schedule changes
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <div class="d-flex align-items-center gap-3">
                        <div style="background: #FEF3C7; padding: 12px; border-radius: 16px;">
                            <i class="fas fa-bell" style="font-size: 24px; color: #F59E0B;"></i>
                        </div>
                        <div>
                            <h5 style="margin: 0 0 4px; font-size: 14px; font-weight: 600;">School Hours</h5>
                            <p style="margin: 0; font-size: 13px; color: #6B7280;">
                                • Morning Session: 07:30 - 12:30<br>
                                • Afternoon Session: 13:30 - 15:30<br>
                                • Break: 10:30 - 10:45 | Lunch: 12:30 - 13:30
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <div class="empty-timetable fade-up">
            <i class="fas fa-calendar-xmark" style="font-size: 56px; color: #CBD5E1; margin-bottom: 20px;"></i>
            <h4 style="color: #4B5563;">No Timetable Found</h4>
            <p style="color: #9CA3AF; margin-bottom: 20px;">Your timetable hasn't been set up yet. Please contact the academic office.</p>
            <a href="index.php" class="btn-sm" style="background: #2563EB; color: white; padding: 10px 24px; display: inline-block;">
                <i class="fas fa-home"></i> Return to Dashboard
            </a>
        </div>
    <?php endif; ?>

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

// Fade up animation
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
});
</script>

