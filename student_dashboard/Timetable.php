<?php
// ============================================================
//  Adaxy Academy · Student Timetable
//  Simple, clean class schedule view
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

$full_name  = htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
$first_name = htmlspecialchars($student['first_name']);
$class_name = htmlspecialchars($student['class_name'] ?? 'Not Assigned');
$class_id   = (int)$student['class_id'];
$form_level = (int)($student['form_level'] ?? 1);
$programme  = $student['programme'] ?? 'JCE';
$stream     = htmlspecialchars($student['stream'] ?? '');
$initials   = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));

// ── Fetch Timetable ──────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT 
        t.*,
        sub.subject_name,
        sub.subject_code,
        CONCAT(te.first_name, ' ', te.last_name) AS teacher_name
    FROM timetable t
    INNER JOIN subjects sub ON sub.subject_id = t.subject_id
    INNER JOIN teachers te ON te.teacher_id = t.teacher_id
    WHERE t.class_id = ?
    ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), t.period_no
");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$all_periods = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Group by Day ─────────────────────────────────────────────
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$timetable = [];
foreach ($days as $day) {
    $timetable[$day] = array_values(array_filter($all_periods, fn($p) => $p['day_of_week'] === $day));
}

// ── Current Time Info ────────────────────────────────────────
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

// ── Statistics ───────────────────────────────────────────────
$total_periods = count($all_periods);
$unique_subjects = count(array_unique(array_column($all_periods, 'subject_name')));

$conn->close();
$page_title = 'Timetable';
include 'includes/header.php';
?>

<style>
    .timetable-header {
        background: linear-gradient(135deg, #0F2B3D 0%, #1A4A6F 100%);
        border-radius: 24px;
        padding: 24px 32px;
        margin-bottom: 28px;
    }
    
    .day-card {
        background: white;
        border-radius: 20px;
        margin-bottom: 24px;
        border: 1px solid #E5E7EB;
        overflow: hidden;
    }
    
    .day-card.today {
        border: 2px solid #2563EB;
    }
    
    .day-header {
        padding: 16px 24px;
        background: #F9FAFB;
        border-bottom: 1px solid #E5E7EB;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .day-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .day-title h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #0F172A;
    }
    
    .today-badge {
        background: #2563EB;
        color: white;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 40px;
    }
    
    .period-count {
        font-size: 13px;
        color: #6B7280;
    }
    
    .timetable-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .timetable-table th {
        padding: 12px 20px;
        background: #FFFFFF;
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
        background: #F9FAFB;
    }
    
    .period-badge {
        background: #0F172A;
        color: #FFD966;
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 700;
        display: inline-block;
    }
    
    .subject-code {
        background: #EFF6FF;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        color: #2563EB;
        display: inline-block;
        margin-right: 10px;
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
    
    .current-class {
        background: linear-gradient(135deg, #2563EB 0%, #1E40AF 100%);
        border-radius: 20px;
        padding: 20px 24px;
        margin-bottom: 28px;
        color: white;
    }
    
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 16px;
        text-align: center;
        border: 1px solid #E5E7EB;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 24px;
        background: white;
        border-radius: 20px;
        border: 1px solid #E5E7EB;
    }
    
    .fade-up {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.5s ease, transform 0.5s ease;
    }
    
    .fade-up.visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    @media (max-width: 768px) {
        .timetable-header { padding: 20px; }
        .day-header { padding: 12px 16px; }
        .timetable-table th, .timetable-table td { padding: 10px 12px; font-size: 12px; }
        .current-class { padding: 16px 20px; }
    }
</style>

<div class="timetable-container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px 40px;">

    <!-- Header -->
    <div class="timetable-header fade-up">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div class="section-tag" style="color: #FFD966;">Schedule</div>
                <h1 style="color: white; margin: 8px 0 4px; font-size: 28px;">My Timetable</h1>
                <p style="color: #B0C4DE; margin: 0;">
                    <?= $class_name ?> <?= $stream ? "({$stream})" : '' ?> · <?= $programme ?> Form <?= $form_level ?>
                </p>
            </div>
            <div style="background: rgba(255,255,255,0.15); padding: 8px 20px; border-radius: 50px;">
                <i class="fa fa-clock" style="color: #FFD966;"></i>
                <span style="color: white; margin-left: 8px;" id="liveClock"><?= date('H:i') ?></span>
                <span style="color: #B0C4DE; margin-left: 8px;"><?= $today ?></span>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-6 fade-up">
            <div class="stat-card">
                <div style="font-size: 28px; font-weight: 700; color: #2563EB;"><?= $total_periods ?></div>
                <div class="text-muted small">Total Periods/Week</div>
            </div>
        </div>
        <div class="col-md-4 col-6 fade-up" style="transition-delay: 0.05s">
            <div class="stat-card">
                <div style="font-size: 28px; font-weight: 700; color: #10B981;"><?= $unique_subjects ?></div>
                <div class="text-muted small">Subjects</div>
            </div>
        </div>
        <div class="col-md-4 col-6 fade-up" style="transition-delay: 0.1s">
            <div class="stat-card">
                <div style="font-size: 28px; font-weight: 700; color: #F59E0B;"><?= round($total_periods / 5, 1) ?></div>
                <div class="text-muted small">Avg Periods/Day</div>
            </div>
        </div>
    </div>

    <!-- Current Class -->
    <?php if ($current_period): ?>
    <div class="current-class fade-up">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="font-size: 12px; opacity: 0.8; margin-bottom: 8px;">
                    <i class="fa fa-play-circle"></i> CURRENTLY IN SESSION
                </div>
                <h3 style="margin: 0 0 4px; font-size: 20px;"><?= htmlspecialchars($current_period['subject_name']) ?></h3>
                <div style="font-size: 13px; opacity: 0.9;">
                    <?= htmlspecialchars($current_period['teacher_name']) ?> · 
                    Room <?= htmlspecialchars($current_period['room_no'] ?? 'TBD') ?>
                </div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 28px; font-weight: 700;">Period <?= $current_period['period_no'] ?></div>
                <div style="font-size: 12px; opacity: 0.8;">
                    <?= date('H:i', strtotime($current_period['start_time'])) ?> - <?= date('H:i', strtotime($current_period['end_time'])) ?>
                </div>
                <span class="now-badge" style="background: rgba(255,255,255,0.2); color: white; margin-top: 8px; display: inline-block;">LIVE NOW</span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Timetable by Day -->
    <?php if ($all_periods): ?>
        <?php foreach ($days as $day):
            $periods = $timetable[$day];
            $is_today = ($day === $today);
        ?>
        <div class="day-card <?= $is_today ? 'today' : '' ?> fade-up">
            <div class="day-header">
                <div class="day-title">
                    <h3><?= $day ?></h3>
                    <?php if ($is_today): ?>
                        <span class="today-badge">TODAY</span>
                    <?php endif; ?>
                </div>
                <div class="period-count">
                    <i class="fa fa-clock"></i> <?= count($periods) ?> period<?= count($periods) !== 1 ? 's' : '' ?>
                </div>
            </div>
            
            <?php if ($periods): ?>
            <div style="overflow-x: auto;">
                <table class="timetable-table">
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
                            $is_current = $is_today && $current_period && $p['period_no'] == $current_period['period_no'];
                        ?>
                        <tr style="<?= $is_current ? 'background: #EFF6FF;' : '' ?>">
                            <td><span class="period-badge">P<?= $p['period_no'] ?></span></td>
                            <td>
                                <span class="subject-code"><?= htmlspecialchars($p['subject_code']) ?></span>
                                <?= htmlspecialchars($p['subject_name']) ?>
                                <?php if ($is_current): ?>
                                    <span class="now-badge">IN PROGRESS</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($p['teacher_name']) ?></td>
                            <td><?= date('H:i', strtotime($p['start_time'])) ?> – <?= date('H:i', strtotime($p['end_time'])) ?></td>
                            <td><?= htmlspecialchars($p['room_no'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div style="padding: 32px 24px; text-align: center; color: #9CA3AF;">
                <i class="fa fa-bed" style="font-size: 28px; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                <p>No classes scheduled</p>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        
        <!-- Reminder Note -->
        <div class="card-box mt-3 fade-up" style="background: #F8FAFE;">
            <div class="d-flex align-items-center gap-3 flex-wrap justify-content-center">
                <div><i class="fa fa-info-circle" style="color: #2563EB;"></i> Arrive 5 minutes early</div>
                <div><i class="fa fa-book" style="color: #2563EB;"></i> Bring your materials</div>
                <div><i class="fa fa-bell" style="color: #2563EB;"></i> Break: 10:30 AM · Lunch: 12:30 PM</div>
            </div>
        </div>
        
    <?php else: ?>
        <div class="empty-state fade-up">
            <i class="fa fa-calendar-xmark" style="font-size: 48px; color: #CBD5E1; margin-bottom: 16px;"></i>
            <h4 style="color: #4B5563;">No Timetable Available</h4>
            <p style="color: #9CA3AF;">Your timetable hasn't been set up yet. Contact your class teacher.</p>
            <a href="index.php" class="btn-enroll mt-3" style="display: inline-flex; background: #2563EB;">
                <i class="fa fa-home"></i> Back to Dashboard
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

