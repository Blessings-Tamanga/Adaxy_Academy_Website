<?php
// ============================================================
//  Adaxy Academy · Student Dashboard
// ============================================================
session_start();
include('../config/db_connect.php');

// ── Auth guard ───────────────────────────────────────────────
if (empty($_SESSION['slogin'])) {
    header('Location: ../Auth/login.php?role=student');
    exit;
}

$username = $_SESSION['slogin'];

// ── Fetch student + class info ───────────────────────────────
$stmt = $conn->prepare("
    SELECT s.*, c.class_name, c.form_level, c.programme
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

$student_id = (int)$student['student_id'];
$full_name  = $student['first_name'] . ' ' . $student['last_name'];
$first_name = $student['first_name'];
$class_name = $student['class_name'] ?? 'N/A';
$programme  = $student['programme'];
$form_level = (int)$student['form_level'];
$initials   = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));

// ── Years until graduation ───────────────────────────────────
$end_form       = ($programme === 'JCE') ? 4 : 6;
$years_left     = max(0, $end_form - $form_level);

// ── Test grades (CA) this term ───────────────────────────────
$stmt = $conn->prepare("
    SELECT g.*, s.subject_name, s.subject_code
    FROM   grades g
    JOIN   subjects s ON s.subject_id = g.subject_id
    WHERE  g.student_id = ?
      AND  g.grade_type = 'test'
    ORDER  BY g.created_at DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$test_grades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── End of term grades ───────────────────────────────────────
$stmt = $conn->prepare("
    SELECT g.*, s.subject_name, s.subject_code
    FROM   grades g
    JOIN   subjects s ON s.subject_id = g.subject_id
    WHERE  g.student_id = ?
      AND  g.grade_type = 'end_of_term'
    ORDER  BY g.created_at DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$eot_grades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── GPA calculation (average of total_score / 25 capped at 4.0) ─
$all_grades   = array_merge($test_grades, $eot_grades);
$gpa          = 0;
if (count($all_grades) > 0) {
    $avg_score = array_sum(array_column($all_grades, 'total_score')) / count($all_grades);
    $gpa       = round(min(4.0, $avg_score / 25), 2);
}

// ── Latest test grade for quick preview ──────────────────────
$latest_grade = !empty($test_grades) ? $test_grades[0] : null;

// ── Notices for students ─────────────────────────────────────
$notices = $conn->query("
    SELECT * FROM notices
    WHERE  is_published = 1
      AND  (audience = 'students' OR audience = 'all')
    ORDER  BY created_at DESC
    LIMIT  5
")->fetch_all(MYSQLI_ASSOC);

// ── Pending actions = unread notices ─────────────────────────
$pending_count   = count($notices);
$notice_count    = count($notices);

// ── Academic summary statistics ──────────────────────────────
$total_subjects = count(array_unique(array_column($all_grades, 'subject_name')));
$avg_score = 0;
if (count($all_grades) > 0) {
    $avg_score = round(array_sum(array_column($all_grades, 'total_score')) / count($all_grades), 1);
}
$passing_count = count(array_filter($all_grades, function($g) { return $g['total_score'] >= 50; }));
$pass_rate = count($all_grades) > 0 ? round(($passing_count / count($all_grades)) * 100) : 0;

// ── Recent activities (simulated from notices and grades) ────
$recent_activities = [];
foreach (array_slice($notices, 0, 3) as $notice) {
    $recent_activities[] = [
        'type' => 'notice',
        'title' => $notice['title'],
        'date' => $notice['created_at'],
        'icon' => 'fa-bell'
    ];
}
foreach (array_slice($test_grades, 0, 2) as $grade) {
    $recent_activities[] = [
        'type' => 'grade',
        'title' => "{$grade['subject_name']} test score: {$grade['total_score']}%",
        'date' => $grade['created_at'],
        'icon' => 'fa-pencil'
    ];
}
usort($recent_activities, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
$recent_activities = array_slice($recent_activities, 0, 5);

// ── Helper: time ago ─────────────────────────────────────────
function time_ago(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600)  return round($diff / 60) . 'm ago';
    if ($diff < 86400) return round($diff / 3600) . 'h ago';
    if ($diff < 604800) return round($diff / 86400) . 'd ago';
    return date('M j', strtotime($datetime));
}

// ── Notice icons ─────────────────────────────────────────────
$notice_icons = ['fa-bullhorn', 'fa-chalkboard-user', 'fa-calendar-check', 'fa-award', 'fa-book-open'];

$conn->close();

$page_title = 'Dashboard';

?>
<?php include "includes/header.php"; ?>

<div class="dashboard-container">

    <!-- Welcome Hero Section -->
    <div class="welcome-hero fade-up">
        <div class="hero-content">
            <div class="hero-text">
                <div class="greeting-badge">
                    <i class="fa-regular fa-sun"></i> Welcome back, <?= htmlspecialchars($first_name) ?>!
                </div>
                <h1>Your Academic Journey</h1>
                <p class="hero-subtitle">Track your progress, stay informed, and achieve excellence at Adaxy Academy.</p>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="stat-value"><?= $total_subjects ?: '—' ?></span>
                        <span class="stat-label">Subjects Enrolled</span>
                    </div>
                    <div class="hero-stat">
                        <span class="stat-value"><?= $gpa ?></span>
                        <span class="stat-label">Current GPA</span>
                    </div>
                    <div class="hero-stat">
                        <span class="stat-value"><?= $years_left ?></span>
                        <span class="stat-label">Years to Graduate</span>
                    </div>
                </div>
            </div>
            <div class="hero-avatar">
                <div class="avatar-circle">
                    <span><?= $initials ?></span>
                </div>
                <div class="avatar-badge">
                    <i class="fa-regular fa-circle-check"></i> Active
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="stats-grid">
        <div class="stat-card stat-card-primary fade-up">
            <div class="stat-icon">
                <i class="fa-regular fa-file-lines"></i>
            </div>
            <div class="stat-info">
                <h3><?= count($all_grades) ?></h3>
                <p>Total Assessments</p>
            </div>
            <div class="stat-trend positive">
                <i class="fa-solid fa-arrow-up"></i> <?= $pass_rate ?>% pass rate
            </div>
        </div>
        <div class="stat-card stat-card-success fade-up" style="transition-delay: 0.05s">
            <div class="stat-icon">
                <i class="fa-regular fa-star"></i>
            </div>
            <div class="stat-info">
                <h3><?= $gpa ?: '—' ?></h3>
                <p>GPA (4.0 Scale)</p>
            </div>
            <div class="stat-trend">
                <i class="fa-regular fa-chart-line"></i> Academic standing
            </div>
        </div>
        <div class="stat-card stat-card-warning fade-up" style="transition-delay: 0.1s">
            <div class="stat-icon">
                <i class="fa-regular fa-bell"></i>
            </div>
            <div class="stat-info">
                <h3><?= $notice_count ?></h3>
                <p>New Notices</p>
            </div>
            <div class="stat-trend">
                <i class="fa-regular fa-envelope"></i> Check updates
            </div>
        </div>
        <div class="stat-card stat-card-info fade-up" style="transition-delay: 0.15s">
            <div class="stat-icon">
                <i class="fa-regular fa-calendar"></i>
            </div>
            <div class="stat-info">
                <h3>Term <?= date('n') <= 3 ? 1 : (date('n') <= 6 ? 2 : 3) ?></h3>
                <p>Current Term</p>
            </div>
            <div class="stat-trend">
                <i class="fa-regular fa-clock"></i> In Progress
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-grid">
        
        <!-- Left Column: Grades & Performance -->
        <div class="grid-col grid-col-2-3">
            <!-- Grades Preview Card -->
            <div class="dashboard-card grades-preview fade-up">
                <div class="card-header">
                    <div class="header-title">
                        <i class="fa-regular fa-graduation-cap"></i>
                        <h3>Academic Performance</h3>
                    </div>
                    <a href="grades.php" class="card-link">View All Grades <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                
                <?php if ($test_grades || $eot_grades): ?>
                <div class="grades-summary">
                    <div class="gpa-circle">
                        <svg viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="45" fill="none" stroke="#e2e8f0" stroke-width="8"/>
                            <circle cx="50" cy="50" r="45" fill="none" stroke="#2563EB" stroke-width="8" 
                                    stroke-dasharray="283" stroke-dashoffset="<?= 283 - (283 * ($gpa/4)) ?>" 
                                    stroke-linecap="round" transform="rotate(-90 50 50)"/>
                        </svg>
                        <div class="gpa-text">
                            <span class="gpa-value"><?= $gpa ?: '—' ?></span>
                            <span class="gpa-max">/4.0</span>
                        </div>
                    </div>
                    <div class="grades-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?= $avg_score ?>%</span>
                            <span class="stat-desc">Average Score</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= $total_subjects ?></span>
                            <span class="stat-desc">Subjects</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= $pass_rate ?>%</span>
                            <span class="stat-desc">Pass Rate</span>
                        </div>
                    </div>
                </div>

                <div class="grades-table-container">
                    <table class="grades-mini-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Type</th>
                                <th>Score</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $display_grades = array_slice(array_merge($eot_grades, $test_grades), 0, 5);
                            foreach ($display_grades as $g):
                                $letter = $g['letter_grade'] ?? 'N/A';
                                $badge  = match($letter) {
                                    'A'   => 'grade-a',
                                    'B'   => 'grade-b',
                                    'C'   => 'grade-c',
                                    'D'   => 'grade-d',
                                    default => 'grade-f'
                                };
                            ?>
                            <tr>
                                <td class="subject-cell">
                                    <span class="subject-code"><?= htmlspecialchars($g['subject_code'] ?? substr($g['subject_name'], 0, 3)) ?></span>
                                    <span class="subject-name"><?= htmlspecialchars($g['subject_name']) ?></span>
                                </td>
                                <td class="type-cell"><?= $g['grade_type'] === 'end_of_term' ? 'Term' : 'Test' ?></td>
                                <td class="score-cell"><strong><?= $g['total_score'] ?>%</strong></td>
                                <td class="grade-cell"><span class="grade-badge <?= $badge ?>"><?= $letter ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fa-regular fa-chart-line"></i>
                    <p>No grades recorded yet. Your academic performance will appear here.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Activity Feed -->
            <div class="dashboard-card activity-feed fade-up" style="transition-delay: 0.1s">
                <div class="card-header">
                    <div class="header-title">
                        <i class="fa-regular fa-clock"></i>
                        <h3>Recent Activity</h3>
                    </div>
                    <span class="badge-update">Live feed</span>
                </div>
                <div class="activity-list">
                    <?php if (!empty($recent_activities)): ?>
                        <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon <?= $activity['type'] ?>">
                                <i class="fa-regular <?= $activity['icon'] ?>"></i>
                            </div>
                            <div class="activity-content">
                                <p><?= htmlspecialchars($activity['title']) ?></p>
                                <span class="activity-time"><?= time_ago($activity['date']) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state small">
                            <i class="fa-regular fa-inbox"></i>
                            <p>No recent activity to display.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Notices & Actions -->
        <div class="grid-col grid-col-1-3">
            <!-- Notices Panel -->
            <div class="dashboard-card notices-panel fade-up">
                <div class="card-header">
                    <div class="header-title">
                        <i class="fa-regular fa-bell"></i>
                        <h3>Announcements</h3>
                    </div>
                    <a href="notices.php" class="card-link">All <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <div class="notices-list">
                    <?php if ($notices): ?>
                        <?php foreach ($notices as $i => $notice): ?>
                        <div class="notice-item">
                            <div class="notice-icon" style="background: rgba(37, 99, 235, 0.1);">
                                <i class="fa-solid <?= $notice_icons[$i % count($notice_icons)] ?>" style="color: #2563EB;"></i>
                            </div>
                            <div class="notice-content">
                                <div class="notice-title"><?= htmlspecialchars($notice['title']) ?></div>
                                <div class="notice-preview"><?= htmlspecialchars(mb_substr($notice['content'], 0, 65)) ?>…</div>
                                <div class="notice-time">
                                    <i class="fa-regular fa-clock"></i> <?= time_ago($notice['created_at']) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state small">
                            <i class="fa-regular fa-check-circle"></i>
                            <p>No new announcements. You're all caught up!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions Panel -->
            <div class="dashboard-card actions-panel fade-up" style="transition-delay: 0.1s">
                <div class="card-header">
                    <div class="header-title">
                        <i class="fa-regular fa-bolt"></i>
                        <h3>Quick Actions</h3>
                    </div>
                </div>
                <div class="actions-grid">
                    <a href="grades.php" class="action-btn">
                        <i class="fa-regular fa-chart-simple"></i>
                        <span>View Grades</span>
                    </a>
                    <a href="timetable.php" class="action-btn">
                        <i class="fa-regular fa-calendar"></i>
                        <span>My Timetable</span>
                    </a>
                    <a href="concern.php" class="action-btn">
                        <i class="fa-regular fa-message"></i>
                        <span>Raise Concern</span>
                    </a>
                    <a href="bursary.php" class="action-btn">
                        <i class="fa-regular fa-hand-holding-heart"></i>
                        <span>Bursary</span>
                    </a>
                    <a href="withdraw.php" class="action-btn warning">
                        <i class="fa-regular fa-arrow-right-from-bracket"></i>
                        <span>Withdraw</span>
                    </a>
                    <a href="../Auth/logout.php" class="action-btn secondary">
                        <i class="fa-regular fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>

            <!-- Student Info Card -->
            <div class="dashboard-card student-info fade-up" style="transition-delay: 0.15s">
                <div class="card-header">
                    <div class="header-title">
                        <i class="fa-regular fa-id-card"></i>
                        <h3>Student Information</h3>
                    </div>
                </div>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Full Name</span>
                        <span class="info-value"><?= htmlspecialchars($full_name) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Roll Number</span>
                        <span class="info-value"><?= htmlspecialchars($student['roll_number']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Programme</span>
                        <span class="info-value"><?= htmlspecialchars($programme) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Class</span>
                        <span class="info-value"><?= htmlspecialchars($class_name) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Form Level</span>
                        <span class="info-value">Form <?= $form_level ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value status-active">
                            <i class="fa-regular fa-circle-check"></i> Active
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="dashboard-footer">
        <div class="footer-content">
            <span><i class="fa-regular fa-shield"></i> Secure Student Portal</span>
            <span>Adaxy Academy · <?= date('Y') ?></span>
            <span><i class="fa-regular fa-clock"></i> Last login: <?= date('M d, H:i') ?></span>
        </div>
    </div>

</div>

<!-- Custom CSS for Enhanced Dashboard -->
<style>
    /* Additional Dashboard Styles */
    .dashboard-container {
        max-width: 1600px;
        margin: 0 auto;
        padding: 0 24px 24px;
    }
    
    /* Welcome Hero */
    .welcome-hero {
        background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
        border-radius: 28px;
        padding: 32px 40px;
        margin-bottom: 32px;
        position: relative;
        overflow: hidden;
    }
    .welcome-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(37, 99, 235, 0.1);
        border-radius: 50%;
        pointer-events: none;
    }
    .hero-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 24px;
        position: relative;
        z-index: 1;
    }
    .greeting-badge {
        background: rgba(37, 99, 235, 0.2);
        display: inline-block;
        padding: 6px 16px;
        border-radius: 40px;
        font-size: 13px;
        font-weight: 500;
        color: #60A5FA;
        margin-bottom: 16px;
    }
    .hero-text h1 {
        font-size: 32px;
        font-weight: 700;
        color: white;
        margin-bottom: 12px;
    }
    .hero-subtitle {
        color: #94A3B8;
        font-size: 15px;
        margin-bottom: 24px;
        max-width: 450px;
    }
    .hero-stats {
        display: flex;
        gap: 32px;
    }
    .hero-stat {
        display: flex;
        flex-direction: column;
    }
    .hero-stat .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: white;
    }
    .hero-stat .stat-label {
        font-size: 12px;
        color: #94A3B8;
    }
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
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
    }
    .avatar-badge {
        text-align: center;
        margin-top: 10px;
        font-size: 12px;
        color: #60A5FA;
        background: rgba(37, 99, 235, 0.15);
        padding: 4px 12px;
        border-radius: 40px;
        display: inline-block;
    }
    
    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }
    .stat-card {
        background: white;
        border-radius: 24px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.2s ease;
        border: 1px solid #E5E7EB;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.08);
    }
    .stat-icon {
        width: 52px;
        height: 52px;
        background: #EFF6FF;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #2563EB;
    }
    .stat-info h3 {
        font-size: 28px;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 4px;
        line-height: 1.2;
    }
    .stat-info p {
        font-size: 13px;
        color: #6B7280;
        margin: 0;
    }
    .stat-trend {
        margin-left: auto;
        font-size: 12px;
        background: #F1F5F9;
        padding: 4px 10px;
        border-radius: 40px;
        color: #475569;
    }
    .stat-trend.positive {
        background: #DCFCE7;
        color: #15803D;
    }
    
    /* Dashboard Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 28px;
    }
    .grid-col-2-3 {
        display: flex;
        flex-direction: column;
        gap: 28px;
    }
    .grid-col-1-3 {
        display: flex;
        flex-direction: column;
        gap: 28px;
    }
    .dashboard-card {
        background: white;
        border-radius: 24px;
        border: 1px solid #E5E7EB;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    .card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #EFF3F8;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }
    .header-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .header-title i {
        font-size: 20px;
        color: #2563EB;
    }
    .header-title h3 {
        font-size: 18px;
        font-weight: 600;
        color: #0F172A;
        margin: 0;
    }
    .card-link {
        font-size: 13px;
        font-weight: 500;
        color: #2563EB;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: gap 0.2s;
    }
    .card-link:hover {
        gap: 10px;
        color: #1D4ED8;
    }
    .badge-update {
        font-size: 11px;
        background: #EFF6FF;
        color: #2563EB;
        padding: 4px 10px;
        border-radius: 40px;
        font-weight: 500;
    }
    
    /* Grades Preview */
    .grades-summary {
        display: flex;
        align-items: center;
        gap: 32px;
        padding: 20px 24px;
        background: #FAFDFF;
        border-bottom: 1px solid #EFF3F8;
    }
    .gpa-circle {
        position: relative;
        width: 100px;
        height: 100px;
    }
    .gpa-circle svg {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }
    .gpa-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }
    .gpa-value {
        font-size: 28px;
        font-weight: 700;
        color: #0F172A;
    }
    .gpa-max {
        font-size: 12px;
        color: #6B7280;
    }
    .grades-stats {
        display: flex;
        gap: 32px;
    }
    .stat-item {
        text-align: center;
    }
    .stat-number {
        display: block;
        font-size: 24px;
        font-weight: 700;
        color: #0F172A;
    }
    .stat-desc {
        font-size: 12px;
        color: #6B7280;
    }
    .grades-mini-table {
        width: 100%;
        border-collapse: collapse;
    }
    .grades-mini-table th {
        text-align: left;
        padding: 14px 20px;
        font-size: 12px;
        font-weight: 600;
        color: #6B7280;
        background: #F9FAFB;
        border-bottom: 1px solid #EFF3F8;
    }
    .grades-mini-table td {
        padding: 14px 20px;
        font-size: 14px;
        border-bottom: 1px solid #F0F2F5;
    }
    .subject-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .subject-code {
        font-size: 11px;
        font-weight: 600;
        background: #F1F5F9;
        padding: 3px 8px;
        border-radius: 6px;
        color: #475569;
    }
    .grade-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 40px;
        font-size: 12px;
        font-weight: 600;
    }
    .grade-a { background: #DCFCE7; color: #15803D; }
    .grade-b { background: #DBEAFE; color: #1E40AF; }
    .grade-c { background: #FEF9C3; color: #854D0E; }
    .grade-d { background: #FFEDD5; color: #C2410C; }
    .grade-f { background: #FEE2E2; color: #B91C1C; }
    
    /* Activity Feed */
    .activity-list {
        padding: 8px 0;
    }
    .activity-item {
        display: flex;
        gap: 14px;
        padding: 16px 24px;
        border-bottom: 1px solid #F0F2F5;
        transition: background 0.2s;
    }
    .activity-item:hover {
        background: #FAFDFF;
    }
    .activity-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }
    .activity-icon.notice { background: #EFF6FF; color: #2563EB; }
    .activity-icon.grade { background: #E6F7EC; color: #059669; }
    .activity-content p {
        font-size: 14px;
        font-weight: 500;
        color: #1F2937;
        margin: 0 0 4px;
    }
    .activity-time {
        font-size: 11px;
        color: #9CA3AF;
    }
    
    /* Notices Panel */
    .notices-list {
        padding: 8px 0;
    }
    .notice-item {
        display: flex;
        gap: 14px;
        padding: 16px 24px;
        border-bottom: 1px solid #F0F2F5;
    }
    .notice-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .notice-content {
        flex: 1;
    }
    .notice-title {
        font-size: 14px;
        font-weight: 600;
        color: #0F172A;
        margin-bottom: 6px;
    }
    .notice-preview {
        font-size: 13px;
        color: #6B7280;
        margin-bottom: 6px;
        line-height: 1.4;
    }
    .notice-time {
        font-size: 11px;
        color: #9CA3AF;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    /* Actions Grid */
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        padding: 20px 24px;
    }
    .action-btn {
        background: #F9FAFB;
        border: 1px solid #EFF3F8;
        border-radius: 16px;
        padding: 14px 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        text-align: center;
        transition: all 0.2s;
        color: #1F2937;
    }
    .action-btn i {
        font-size: 20px;
        color: #2563EB;
    }
    .action-btn span {
        font-size: 12px;
        font-weight: 500;
    }
    .action-btn:hover {
        background: #EFF6FF;
        border-color: #2563EB;
        transform: translateY(-2px);
    }
    .action-btn.warning i {
        color: #DC2626;
    }
    .action-btn.warning:hover {
        background: #FEF2F2;
        border-color: #DC2626;
    }
    .action-btn.secondary i {
        color: #6B7280;
    }
    
    /* Student Info */
    .info-grid {
        padding: 8px 24px 24px;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #F0F2F5;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-size: 13px;
        color: #6B7280;
    }
    .info-value {
        font-size: 14px;
        font-weight: 500;
        color: #0F172A;
    }
    .status-active {
        color: #059669;
        background: #E6F7EC;
        padding: 4px 10px;
        border-radius: 40px;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    /* Empty States */
    .empty-state {
        text-align: center;
        padding: 48px 24px;
        color: #9CA3AF;
    }
    .empty-state i {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }
    .empty-state.small {
        padding: 32px 24px;
    }
    .empty-state.small i {
        font-size: 32px;
    }
    
    /* Footer */
    .dashboard-footer {
        margin-top: 48px;
        padding-top: 24px;
        border-top: 1px solid #EFF3F8;
    }
    .footer-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: #9CA3AF;
    }
    .footer-content span {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    /* Responsive */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 0 16px 16px;
        }
        .welcome-hero {
            padding: 24px;
        }
        .hero-text h1 {
            font-size: 24px;
        }
        .hero-stats {
            flex-wrap: wrap;
            gap: 16px;
        }
        .grades-summary {
            flex-direction: column;
            text-align: center;
        }
        .grades-stats {
            justify-content: center;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const btn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (btn) btn.addEventListener('click', () => sidebar.classList.toggle('show'));

    const obs = new IntersectionObserver((entries) => {
        entries.forEach(en => {
            if (en.isIntersecting) { 
                en.target.classList.add('visible'); 
                obs.unobserve(en.target); 
            }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.fade-up').forEach(el => obs.observe(el));
})();
</script>
</body>
</html>