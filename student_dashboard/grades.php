<?php
// ============================================================
//  Adaxy Academy · Academic Performance Portal
//  MANEB-Aligned Student Performance Tracking System
// ============================================================

session_start();
include('../config/db_connect.php');

// ── Authentication & Security ─────────────────────────────────
if (empty($_SESSION['slogin'])) {
    header('Location: ../Auth/login.php?role=student');
    exit;
}

$username = trim($_SESSION['slogin']);

// ── Student Information Fetch with Error Handling ─────────────
$stmt = $conn->prepare("
    SELECT s.*, c.class_name, c.form_level, c.programme, c.stream
    FROM students s
    LEFT JOIN classes c ON c.class_id = s.class_id
    WHERE s.username = ? 
    LIMIT 1
");

if (!$stmt) {
    error_log("Database prepare failed: " . $conn->error);
    die("System error. Please contact administrator.");
}

$stmt->bind_param("s", $username);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    session_destroy();
    header('Location: ../Auth/login.php?role=student');
    exit;
}

// ── Student Data Initialization ───────────────────────────────
$student_id = (int)$student['student_id'];
$full_name = htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
$first_name = htmlspecialchars($student['first_name']);
$class_name = htmlspecialchars($student['class_name'] ?? 'Not Assigned');
$programme = $student['programme'] ?? 'JCE';
$form_level = (int)($student['form_level'] ?? 1);
$stream = htmlspecialchars($student['stream'] ?? '');
$initials = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));

// ── Filter Parameters with Sanitization ───────────────────────
$subject_filter = isset($_GET['subject']) && is_numeric($_GET['subject']) ? (int)$_GET['subject'] : 'all';
$exam_type = isset($_GET['exam_type']) ? preg_replace('/[^a-z_]/', '', $_GET['exam_type']) : 'all';
$paper_filter = isset($_GET['paper']) ? preg_replace('/[^a-z0-9]/', '', $_GET['paper']) : 'all';
$term_filter = isset($_GET['term']) && is_numeric($_GET['term']) ? (int)$_GET['term'] : 'all';
$year_filter = isset($_GET['year']) ? preg_replace('/[^0-9]/', '', $_GET['year']) : date('Y');

// Allowed values for validation
$allowed_exam_types = ['all', 'test', 'end_of_term', 'mock'];
$exam_type = in_array($exam_type, $allowed_exam_types) ? $exam_type : 'all';

// ── Build Dynamic SQL Query with Prepared Statements ──────────
$sql = "
    SELECT 
        g.*,
        sub.subject_name,
        sub.subject_code,
        sub.department_id,
        d.department_name,
        ep.paper_id,
        ep.paper_number,
        ep.paper_title,
        ep.total_marks as paper_total,
        ep.duration_minutes,
        es.section_id,
        es.section_letter,
        es.section_name,
        es.question_type,
        pb.grade_letter as standard_grade,
        pb.min_percentage,
        pb.max_percentage,
        pb.description as grade_description
    FROM grades g
    INNER JOIN subjects sub ON sub.subject_id = g.subject_id
    LEFT JOIN departments d ON d.department_id = sub.department_id
    LEFT JOIN exam_papers ep ON ep.paper_id = g.paper_id AND ep.programme = ?
    LEFT JOIN exam_sections es ON es.section_id = g.section_id
    LEFT JOIN performance_benchmarks pb ON pb.subject_id = g.subject_id 
        AND pb.programme = ? 
        AND g.total_score BETWEEN pb.min_percentage AND pb.max_percentage
    WHERE g.student_id = ?
";

$params = [$programme, $programme, $student_id];
$types = "ssi";

// Apply filters conditionally
if ($subject_filter !== 'all') {
    $sql .= " AND sub.subject_id = ?";
    $params[] = $subject_filter;
    $types .= "i";
}

if ($exam_type !== 'all') {
    $sql .= " AND g.grade_type = ?";
    $params[] = $exam_type;
    $types .= "s";
}

if ($paper_filter !== 'all') {
    $sql .= " AND ep.paper_number = ?";
    $params[] = $paper_filter;
    $types .= "s";
}

if ($term_filter !== 'all') {
    $sql .= " AND g.term = ?";
    $params[] = $term_filter;
    $types .= "i";
}

if ($year_filter !== 'all') {
    $sql .= " AND g.academic_year = ?";
    $params[] = $year_filter;
    $types .= "i";
}

$sql .= " ORDER BY g.academic_year DESC, g.term DESC, sub.subject_name ASC, ep.paper_number ASC, es.section_letter ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Grades query prepare failed: " . $conn->error);
    $grades = [];
} else {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $grades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// ── Fetch Filter Options ──────────────────────────────────────
$subjects = $conn->query("
    SELECT subject_id, subject_name, subject_code 
    FROM subjects 
    ORDER BY subject_name
")->fetch_all(MYSQLI_ASSOC) ?: [];

$papers = $conn->query("
    SELECT DISTINCT paper_number, paper_title 
    FROM exam_papers 
    WHERE programme = '" . $conn->real_escape_string($programme) . "'
    ORDER BY paper_number
")->fetch_all(MYSQLI_ASSOC) ?: [];

// Available academic years from student's grades
$available_years = array_unique(array_column($grades, 'academic_year'));
rsort($available_years);

// Available terms from student's grades
$available_terms = array_unique(array_column($grades, 'term'));
sort($available_terms);

// ── Performance Calculations ──────────────────────────────────
$total_grades = count($grades);
$total_scores = array_column($grades, 'total_score');
$avg_score = $total_grades > 0 ? round(array_sum($total_scores) / $total_grades, 1) : 0;

// Grade distribution with MANEB standards
$grade_distribution = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
$subject_performance = [];
$paper_performance = [];

foreach ($grades as $g) {
    // Grade distribution
    $letter = $g['standard_grade'] ?? ($g['letter_grade'] ?? 'F');
    if (isset($grade_distribution[$letter])) {
        $grade_distribution[$letter]++;
    }
    
    // Subject performance tracking
    $subject = $g['subject_name'];
    if (!isset($subject_performance[$subject])) {
        $subject_performance[$subject] = [
            'scores' => [],
            'best' => 0,
            'worst' => 100,
            'count' => 0,
            'subject_code' => $g['subject_code']
        ];
    }
    $score = (float)$g['total_score'];
    $subject_performance[$subject]['scores'][] = $score;
    $subject_performance[$subject]['best'] = max($subject_performance[$subject]['best'], $score);
    $subject_performance[$subject]['worst'] = min($subject_performance[$subject]['worst'], $score);
    $subject_performance[$subject]['count']++;
    
    // Paper performance tracking
    $paper = $g['paper_number'] ?? 'General';
    if (!isset($paper_performance[$paper])) {
        $paper_performance[$paper] = ['scores' => [], 'count' => 0, 'title' => $g['paper_title'] ?? ''];
    }
    $paper_performance[$paper]['scores'][] = $score;
    $paper_performance[$paper]['count']++;
}

// Calculate averages for subjects and papers
foreach ($subject_performance as $subject => $data) {
    $subject_performance[$subject]['avg'] = round(array_sum($data['scores']) / $data['count'], 1);
}
foreach ($paper_performance as $paper => $data) {
    $paper_performance[$paper]['avg'] = round(array_sum($data['scores']) / $data['count'], 1);
}

// Sort subjects by performance
uasort($subject_performance, function($a, $b) {
    return $b['avg'] <=> $a['avg'];
});

// ── Performance Trend Analysis ────────────────────────────────
$trend_data = array_slice(array_reverse($grades), 0, 8);
$trend_scores = array_reverse(array_column($trend_data, 'total_score'));
$trend_subjects = array_reverse(array_column($trend_data, 'subject_code'));

// GPA Calculation (4.0 scale - MANEB standard)
$gpa = $total_grades > 0 ? round(min(4.0, ($avg_score / 25)), 2) : 0;

// ── Helper Functions ──────────────────────────────────────────
function getManebGradeClass($letter): string {
    return match($letter) {
        'A' => 'grade-a',
        'B' => 'grade-b', 
        'C' => 'grade-c',
        'D' => 'grade-d',
        default => 'grade-f'
    };
}

function getManebGradeColor($letter): string {
    return match($letter) {
        'A' => '#10b981',
        'B' => '#3b82f6',
        'C' => '#f59e0b',
        'D' => '#f97316',
        default => '#ef4444'
    };
}

function getPerformanceInsight($avg, $programme): array {
    if ($programme === 'MSCE') {
        if ($avg >= 75) return ['message' => 'Excellent! You\'re on track for a Distinction at MSCE. Keep up the outstanding work! 🎓', 'type' => 'excellent'];
        if ($avg >= 65) return ['message' => 'Good performance! You\'re on track for a Credit. Focus on improving weaker areas. 📚', 'type' => 'good'];
        if ($avg >= 50) return ['message' => 'Satisfactory. You\'re at a Pass level. Dedicate more time to challenging subjects. 📖', 'type' => 'satisfactory'];
        if ($avg >= 40) return ['message' => 'Fair performance. You\'re at a Marginal Pass level. Seek additional support. ⚠️', 'type' => 'warning'];
        return ['message' => 'Needs improvement. Please consult with your teachers for academic support. 🤝', 'type' => 'danger'];
    } else {
        if ($avg >= 70) return ['message' => 'Excellent performance! You\'re doing great. Keep building strong foundations for MSCE. 🌟', 'type' => 'excellent'];
        if ($avg >= 60) return ['message' => 'Very good! You\'re showing solid understanding. Aim for even higher scores. 📈', 'type' => 'good'];
        if ($avg >= 45) return ['message' => 'Good effort. You\'re meeting expectations. Focus on consistent improvement. 📚', 'type' => 'satisfactory'];
        if ($avg >= 35) return ['message' => 'Fair performance. Some areas need attention. Don\'t hesitate to ask for help. 📖', 'type' => 'warning'];
        return ['message' => 'Needs improvement. Let\'s work together to boost your performance. 🤝', 'type' => 'danger'];
    }
}

function predictMSCEGrade($avg): array {
    if ($avg >= 75) return ['grade' => 'A', 'description' => 'Distinction', 'color' => '#10b981'];
    if ($avg >= 65) return ['grade' => 'B', 'description' => 'Credit', 'color' => '#3b82f6'];
    if ($avg >= 50) return ['grade' => 'C', 'description' => 'Pass', 'color' => '#f59e0b'];
    if ($avg >= 40) return ['grade' => 'D', 'description' => 'Marginal Pass', 'color' => '#f97316'];
    return ['grade' => 'F', 'description' => 'Fail', 'color' => '#ef4444'];
}

$insight = getPerformanceInsight($avg_score, $programme);
$predicted = $programme === 'MSCE' ? predictMSCEGrade($avg_score) : null;

$conn->close();
$page_title = 'Academic Performance | Adaxy Academy';
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Performance - Adaxy Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Custom Variables */
        :root {
            --maneb-primary: #0F2B3D;
            --maneb-secondary: #1A4A6F;
            --maneb-gold: #FFD966;
            --maneb-blue: #2563EB;
            --maneb-green: #10b981;
            --maneb-orange: #f59e0b;
            --maneb-red: #ef4444;
            --maneb-purple: #8b5cf6;
        }
        
        /* MANEB Header Styles */
        .maneb-header {
            background: linear-gradient(135deg, var(--maneb-primary) 0%, var(--maneb-secondary) 100%);
            border-radius: 28px;
            padding: 32px 36px;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        
        .maneb-header::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -5%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,217,102,0.1) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        
        .maneb-badge {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(4px);
            padding: 6px 16px;
            border-radius: 40px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--maneb-gold);
            letter-spacing: 0.3px;
        }
        
        /* Filter Bar */
        .filter-bar {
            background: white;
            border-radius: 20px;
            padding: 20px 24px;
            margin-bottom: 28px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            border: 1px solid #E5E7EB;
        }
        
        .filter-badge {
            background: #F3F4F6;
            border-radius: 40px;
            padding: 6px 18px;
            font-size: 13px;
            font-weight: 500;
            color: #4B5563;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-badge:hover {
            background: #E5E7EB;
            color: #1F2937;
            text-decoration: none;
        }
        
        .filter-badge.active {
            background: var(--maneb-blue);
            color: white;
        }
        
        /* Stats Cards */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            transition: all 0.2s ease;
            border: 1px solid #E5E7EB;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            line-height: 1.2;
        }
        
        /* Prediction Card */
        .prediction-card {
            background: linear-gradient(135deg, #1E3A8A, var(--maneb-blue));
            border-radius: 24px;
            padding: 24px 32px;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .prediction-card::after {
            content: 'MANEB';
            position: absolute;
            bottom: 10px;
            right: 20px;
            font-size: 48px;
            font-weight: 800;
            opacity: 0.08;
            pointer-events: none;
        }
        
        /* Exam Paper Cards */
        .exam-paper-card {
            background: white;
            border-radius: 20px;
            border-left: 4px solid var(--maneb-blue);
            padding: 18px 20px;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            margin-bottom: 0;
        }
        
        .exam-paper-card:hover {
            transform: translateX(6px);
            box-shadow: 0 8px 20px rgba(37,99,235,0.12);
        }
        
        .paper-badge {
            background: #EFF6FF;
            padding: 4px 14px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
            color: var(--maneb-blue);
        }
        
        .section-badge {
            background: #FEF3C7;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 500;
            color: #B45309;
        }
        
        /* Grade Badges */
        .maneb-grade {
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 30px;
            display: inline-block;
            font-size: 13px;
        }
        
        .grade-a { background: #DCFCE7; color: #15803D; }
        .grade-b { background: #DBEAFE; color: #1E40AF; }
        .grade-c { background: #FEF9C3; color: #854D0E; }
        .grade-d { background: #FFEDD5; color: #C2410C; }
        .grade-f { background: #FEE2E2; color: #B91C1C; }
        
        /* Subject Card */
        .subject-card {
            background: #F9FAFB;
            border-radius: 16px;
            padding: 16px;
            transition: all 0.2s;
            border: 1px solid #F0F2F5;
        }
        
        .subject-card:hover {
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            border-color: #E5E7EB;
        }
        
        /* Progress Bar */
        .progress-bar-custom {
            height: 6px;
            background: #E5E7EB;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        /* Trend Chart */
        .trend-chart {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            padding: 20px 0;
        }
        
        .trend-bar {
            flex: 1;
            text-align: center;
        }
        
        .trend-bar .bar {
            height: 0;
            min-height: 4px;
            border-radius: 8px;
            transition: height 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            margin-bottom: 8px;
        }
        
        /* Dashboard Card */
        .dashboard-card {
            background: white;
            border-radius: 24px;
            border: 1px solid #E5E7EB;
            overflow: hidden;
            margin-bottom: 28px;
        }
        
        .card-header-custom {
            padding: 20px 24px;
            border-bottom: 1px solid #F0F2F5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .header-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header-title i {
            font-size: 22px;
            color: var(--maneb-blue);
        }
        
        .header-title h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            color: #1F2937;
        }
        
        /* Grade Table */
        .grade-table-container {
            overflow-x: auto;
        }
        
        .grade-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        .grade-table th {
            padding: 16px 16px;
            background: #F9FAFB;
            font-size: 12px;
            font-weight: 600;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #E5E7EB;
            text-align: left;
        }
        
        .grade-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #F0F2F5;
            color: #374151;
        }
        
        .grade-table tr:hover td {
            background: #FAFDFF;
        }
        
        .subject-code-badge {
            background: #EFF6FF;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            color: var(--maneb-blue);
            display: inline-block;
            margin-right: 10px;
        }
        
        .score-high { color: #10b981; font-weight: 600; }
        .score-mid { color: #f59e0b; font-weight: 600; }
        .score-low { color: #ef4444; font-weight: 600; }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 24px;
            color: #9CA3AF;
        }
        
        .empty-state i {
            font-size: 56px;
            margin-bottom: 20px;
            opacity: 0.5;
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
            .maneb-header { padding: 24px; }
            .filter-bar { padding: 16px; }
            .stat-value { font-size: 28px; }
            .grade-table th, .grade-table td { padding: 10px 12px; font-size: 12px; }
            .card-header-custom { padding: 16px 20px; }
        }
        
        @media (max-width: 576px) {
            .filter-badge { padding: 4px 12px; font-size: 11px; }
        }
    </style>
</head>
<body>

<div class="academic-container" style="max-width: 1600px; margin: 0 auto; padding: 0 24px 32px;">

    <!-- MANEB Header Section -->
    <div class="maneb-header fade-up">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 24px;">
            <div>
                <div class="maneb-badge">
                    <i class="fa-regular fa-building-columns"></i> MANEB Standards & Assessment
                </div>
                <h1 style="color: white; margin: 16px 0 8px; font-size: 32px; font-weight: 700;">Academic Performance</h1>
                <p style="color: #B0C4DE; margin-bottom: 0; font-size: 15px;">
                    <i class="fa-regular fa-school"></i> <?= $class_name ?> <?= $stream ? "({$stream})" : '' ?> · 
                    <i class="fa-regular fa-graduation-cap"></i> <?= $programme ?> Programme · 
                    <i class="fa-regular fa-layer-group"></i> Form <?= $form_level ?>
                </p>
            </div>
            <div class="text-end">
                <div style="font-size: 48px; font-weight: 800; color: var(--maneb-gold);"><?= $avg_score ?>%</div>
                <div style="color: #B0C4DE; font-size: 13px; letter-spacing: 0.5px;">OVERALL ACADEMIC AVERAGE</div>
            </div>
        </div>
    </div>

    <!-- MSCE Prediction Card (if applicable) -->
    <?php if ($programme === 'MSCE' && $predicted): ?>
    <div class="prediction-card fade-up mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="text-start">
                <i class="fa-regular fa-chart-line" style="font-size: 32px; opacity: 0.9;"></i>
                <h3 style="margin: 12px 0 4px; font-size: 20px;">MSCE Performance Prediction</h3>
                <p style="margin-bottom: 0; opacity: 0.8;">Based on current academic performance (MANEB grading standards)</p>
            </div>
            <div class="text-center">
                <div style="font-size: 56px; font-weight: 800; line-height: 1;"><?= $predicted['grade'] ?></div>
                <div style="font-size: 14px; font-weight: 500;"><?= $predicted['description'] ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Performance Insight Card -->
    <div class="dashboard-card fade-up" style="background: linear-gradient(135deg, #F8FAFE, #FFFFFF); border: 1px solid #E5E7EB;">
        <div class="card-header-custom" style="border-bottom: none; padding-bottom: 0;">
            <div class="header-title">
                <i class="fa-regular fa-lightbulb" style="color: var(--maneb-orange);"></i>
                <h3>Performance Insight</h3>
            </div>
        </div>
        <div class="p-4 pt-0">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="p-3 rounded-4" style="background: <?= $insight['type'] === 'excellent' ? '#DCFCE7' : ($insight['type'] === 'good' ? '#DBEAFE' : ($insight['type'] === 'satisfactory' ? '#FEF3C7' : '#FEE2E2')) ?>;">
                    <i class="fa-regular fa-<?= $insight['type'] === 'excellent' ? 'crown' : ($insight['type'] === 'good' ? 'chart-line' : ($insight['type'] === 'satisfactory' ? 'book' : 'triangle-exclamation')) ?>" style="font-size: 32px; color: <?= $insight['type'] === 'excellent' ? '#15803D' : ($insight['type'] === 'good' ? '#1E40AF' : ($insight['type'] === 'satisfactory' ? '#B45309' : '#B91C1C')) ?>;"></i>
                </div>
                <div style="flex: 1;">
                    <p style="margin-bottom: 0; font-size: 15px; line-height: 1.5; color: #374151;"><?= $insight['message'] ?></p>
                </div>
                <?php if ($avg_score < 50): ?>
                <a href="concern.php" class="btn btn-outline-danger btn-sm rounded-pill px-4">
                    <i class="fa-regular fa-message"></i> Request Support
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Enhanced Filter Bar -->
    <div class="filter-bar fade-up">
        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="d-flex flex-wrap gap-2">
                <span class="small text-muted me-2"><i class="fa-regular fa-filter"></i> Subject:</span>
                <a href="?subject=all&exam_type=<?= $exam_type ?>&paper=<?= $paper_filter ?>&term=<?= $term_filter ?>&year=<?= $year_filter ?>" 
                   class="filter-badge <?= $subject_filter === 'all' ? 'active' : '' ?>">
                    <i class="fa-regular fa-table-cells-large"></i> All Subjects
                </a>
                <?php foreach ($subjects as $sub): ?>
                <a href="?subject=<?= $sub['subject_id'] ?>&exam_type=<?= $exam_type ?>&paper=<?= $paper_filter ?>&term=<?= $term_filter ?>&year=<?= $year_filter ?>" 
                   class="filter-badge <?= $subject_filter == $sub['subject_id'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($sub['subject_code']) ?>
                </a>
                <?php endforeach; ?>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <select class="form-select form-select-sm rounded-pill" style="width: auto; padding: 6px 16px; font-size: 13px; border-color: #E5E7EB;" 
                        onchange="window.location.href=updateQueryParam('exam_type', this.value)">
                    <option value="all" <?= $exam_type === 'all' ? 'selected' : '' ?>>📋 All Assessments</option>
                    <option value="test" <?= $exam_type === 'test' ? 'selected' : '' ?>>✏️ Tests & CA</option>
                    <option value="end_of_term" <?= $exam_type === 'end_of_term' ? 'selected' : '' ?>>📅 End of Term</option>
                </select>
                
                <?php if (!empty($available_terms)): ?>
                <select class="form-select form-select-sm rounded-pill" style="width: auto; padding: 6px 16px; font-size: 13px; border-color: #E5E7EB;" 
                        onchange="window.location.href=updateQueryParam('term', this.value)">
                    <option value="all" <?= $term_filter === 'all' ? 'selected' : '' ?>>All Terms</option>
                    <?php foreach ($available_terms as $term): ?>
                    <option value="<?= $term ?>" <?= $term_filter == $term ? 'selected' : '' ?>>Term <?= $term ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                
                <?php if (!empty($available_years)): ?>
                <select class="form-select form-select-sm rounded-pill" style="width: auto; padding: 6px 16px; font-size: 13px; border-color: #E5E7EB;" 
                        onchange="window.location.href=updateQueryParam('year', this.value)">
                    <option value="all" <?= $year_filter === 'all' ? 'selected' : '' ?>>All Years</option>
                    <?php foreach ($available_years as $year): ?>
                    <option value="<?= $year ?>" <?= $year_filter == $year ? 'selected' : '' ?>><?= $year ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6 fade-up">
            <div class="stat-card">
                <div class="stat-value" style="color: var(--maneb-blue);"><?= $total_grades ?></div>
                <div class="text-muted small mt-1">Total Assessments</div>
                <div class="mt-2"><i class="fa-regular fa-file-lines text-muted"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.05s">
            <div class="stat-card">
                <div class="stat-value" style="color: var(--maneb-green);"><?= $grade_distribution['A'] ?></div>
                <div class="text-muted small mt-1">A Grades Earned</div>
                <div class="mt-2"><i class="fa-regular fa-star text-warning"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.1s">
            <div class="stat-card">
                <div class="stat-value" style="color: var(--maneb-orange);"><?= $gpa ?></div>
                <div class="text-muted small mt-1">GPA (4.0 Scale)</div>
                <div class="mt-2"><i class="fa-regular fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-6 fade-up" style="transition-delay: 0.15s">
            <div class="stat-card">
                <div class="stat-value" style="color: var(--maneb-purple);"><?= round(($grade_distribution['A'] + $grade_distribution['B']) / max(1, $total_grades) * 100) ?>%</div>
                <div class="text-muted small mt-1">A-B Success Rate</div>
                <div class="mt-2"><i class="fa-regular fa-chart-simple"></i></div>
            </div>
        </div>
    </div>

    <!-- Performance Trend Chart -->
    <?php if (!empty($trend_scores)): ?>
    <div class="dashboard-card fade-up">
        <div class="card-header-custom">
            <div class="header-title">
                <i class="fa-regular fa-chart-line"></i>
                <h3>Recent Performance Trend</h3>
            </div>
            <span class="badge bg-light text-dark rounded-pill px-3 py-1 small">Last <?= count($trend_scores) ?> assessments</span>
        </div>
        <div class="p-4 pt-0">
            <div class="trend-chart">
                <?php foreach ($trend_scores as $i => $score): 
                    $height = max(45, min(120, ($score / 100) * 100));
                    $barColor = $score >= 70 ? '#10b981' : ($score >= 50 ? '#f59e0b' : '#ef4444');
                ?>
                <div class="trend-bar">
                    <div class="bar" style="height: <?= $height ?>px; background: <?= $barColor ?>;"></div>
                    <div class="score" style="font-size: 11px; font-weight: 500; color: <?= $barColor ?>;"><?= $score ?>%</div>
                    <div style="font-size: 9px; color: #9CA3AF; margin-top: 4px;"><?= $trend_subjects[$i] ?? '' ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Subject Performance Breakdown -->
    <?php if (!empty($subject_performance)): ?>
    <div class="dashboard-card fade-up">
        <div class="card-header-custom">
            <div class="header-title">
                <i class="fa-regular fa-chart-simple"></i>
                <h3>Subject Performance Summary</h3>
            </div>
            <span class="badge bg-light text-dark rounded-pill px-3 py-1 small">Sorted by performance</span>
        </div>
        <div class="p-4">
            <div class="row g-3">
                <?php foreach ($subject_performance as $subject => $data): 
                    $avg_color = $data['avg'] >= 70 ? '#10b981' : ($data['avg'] >= 50 ? '#f59e0b' : '#ef4444');
                ?>
                <div class="col-md-4 col-sm-6">
                    <div class="subject-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <div>
                                <span class="subject-code-badge"><?= htmlspecialchars($data['subject_code']) ?></span>
                                <span style="font-weight: 600; color: #1F2937; margin-left: 6px;"><?= htmlspecialchars($subject) ?></span>
                            </div>
                            <span style="font-size: 24px; font-weight: 700; color: <?= $avg_color ?>;"><?= $data['avg'] ?>%</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2" style="font-size: 12px; color: #6B7280;">
                            <span><i class="fa-regular fa-arrow-up"></i> Best: <?= $data['best'] ?>%</span>
                            <span><i class="fa-regular fa-arrow-down"></i> Worst: <?= $data['worst'] ?>%</span>
                            <span><i class="fa-regular fa-chart-line"></i> <?= $data['count'] ?> assessments</span>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width: <?= $data['avg'] ?>%; background: <?= $avg_color ?>;"></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Exam Paper Performance -->
    <?php if (!empty($paper_performance) && count($paper_performance) > 1): ?>
    <div class="dashboard-card fade-up">
        <div class="card-header-custom">
            <div class="header-title">
                <i class="fa-regular fa-file-lines"></i>
                <h3>Exam Paper Analysis</h3>
            </div>
            <span class="badge-update">MANEB Structure</span>
        </div>
        <div class="p-4">
            <div class="row g-3">
                <?php foreach ($paper_performance as $paper => $data): 
                    $grade_class = $data['avg'] >= 75 ? 'grade-a' : ($data['avg'] >= 65 ? 'grade-b' : ($data['avg'] >= 50 ? 'grade-c' : ($data['avg'] >= 40 ? 'grade-d' : 'grade-f')));
                ?>
                <div class="col-md-4">
                    <div class="exam-paper-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <span class="paper-badge">
                                <i class="fa-regular fa-file-alt me-1"></i> 
                                Paper <?= $paper !== 'General' ? $paper : 'All Papers' ?>
                            </span>
                            <span class="maneb-grade <?= $grade_class ?>"><?= $data['avg'] ?>%</span>
                        </div>
                        <?php if ($data['title']): ?>
                        <div style="font-size: 12px; color: #6B7280; margin-bottom: 12px;"><?= htmlspecialchars($data['title']) ?></div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-2" style="font-size: 12px;">
                            <span>Average Score</span>
                            <span class="fw-bold"><?= $data['avg'] ?>%</span>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width: <?= $data['avg'] ?>%; background: <?= $data['avg'] >= 70 ? '#10b981' : ($data['avg'] >= 50 ? '#f59e0b' : '#ef4444') ?>;"></div>
                        </div>
                        <div style="font-size: 11px; color: #9CA3AF; margin-top: 10px;">
                            Based on <?= $data['count'] ?> assessment(s)
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Detailed Grades Table -->
    <div class="dashboard-card fade-up">
        <div class="card-header-custom">
            <div class="header-title">
                <i class="fa-regular fa-table"></i>
                <h3>Complete Grade Record</h3>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-light text-dark rounded-pill px-3 py-1 small">
                    <i class="fa-regular fa-file-lines me-1"></i> <?= $total_grades ?> records
                </span>
                <button onclick="window.print()" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fa-regular fa-print"></i> Print
                </button>
            </div>
        </div>

        <?php if ($grades): ?>
        <div class="grade-table-container">
            <table class="grade-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Paper</th>
                        <th>CA1</th>
                        <th>CA2</th>
                        <th>CA3</th>
                        <th>Exam</th>
                        <th>Total</th>
                        <th>MANEB Grade</th>
                        <th>Term/Year</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grades as $g):
                        $maneb_grade = $g['standard_grade'] ?? ($g['letter_grade'] ?? 'F');
                        $grade_class = getManebGradeClass($maneb_grade);
                        $scoreClass = $g['total_score'] >= 70 ? 'score-high' : ($g['total_score'] >= 50 ? 'score-mid' : 'score-low');
                    ?>
                    <tr>
                        <td>
                            <span class="subject-code-badge"><?= htmlspecialchars($g['subject_code'] ?? substr($g['subject_name'], 0, 3)) ?></span>
                            <?= htmlspecialchars($g['subject_name']) ?>
                        </td>
                        <td>
                            <?php if ($g['paper_number']): ?>
                            <span class="paper-badge">Paper <?= $g['paper_number'] ?></span>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $g['ca1_score'] ?? '—' ?></td>
                        <td><?= $g['ca2_score'] ?? '—' ?></td>
                        <td><?= $g['ca3_score'] ?? '—' ?></td>
                        <td><?= $g['exam_score'] ?? '—' ?></td>
                        <td class="<?= $scoreClass ?>"><strong><?= $g['total_score'] ?>%</strong></td>
                        <td><span class="maneb-grade <?= $grade_class ?>"><?= $maneb_grade ?></span></td>
                        <td class="text-muted small">Term <?= $g['term'] ?> · <?= $g['academic_year'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fa-regular fa-file-circle-xmark"></i>
            <h5 style="margin-top: 16px; color: #6B7280;">No Grade Records Found</h5>
            <p class="text-muted">Your academic performance will appear here as assessments are completed.</p>
            <a href="index.php" class="btn btn-primary rounded-pill px-4 mt-2">
                <i class="fa-regular fa-home"></i> Return to Dashboard
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- MANEB Grading Guide -->
    <div class="dashboard-card fade-up" style="background: #F8FAFE;">
        <div class="card-header-custom">
            <div class="header-title">
                <i class="fa-regular fa-graduation-cap"></i>
                <h3>MANEB Official Grading Guide</h3>
            </div>
        </div>
        <div class="p-4 pt-0">
            <div class="row text-center g-3">
                <div class="col-md-2 col-6">
                    <span class="maneb-grade grade-a d-inline-block mb-2" style="min-width: 60px;">A</span>
                    <div style="font-size: 13px; font-weight: 500;">75-100%</div>
                    <div style="font-size: 11px; color: #6B7280;">Distinction</div>
                </div>
                <div class="col-md-2 col-6">
                    <span class="maneb-grade grade-b d-inline-block mb-2" style="min-width: 60px;">B</span>
                    <div style="font-size: 13px; font-weight: 500;">65-74%</div>
                    <div style="font-size: 11px; color: #6B7280;">Credit</div>
                </div>
                <div class="col-md-2 col-6">
                    <span class="maneb-grade grade-c d-inline-block mb-2" style="min-width: 60px;">C</span>
                    <div style="font-size: 13px; font-weight: 500;">50-64%</div>
                    <div style="font-size: 11px; color: #6B7280;">Pass</div>
                </div>
                <div class="col-md-2 col-6">
                    <span class="maneb-grade grade-d d-inline-block mb-2" style="min-width: 60px;">D</span>
                    <div style="font-size: 13px; font-weight: 500;">40-49%</div>
                    <div style="font-size: 11px; color: #6B7280;">Marginal Pass</div>
                </div>
                <div class="col-md-2 col-6">
                    <span class="maneb-grade grade-f d-inline-block mb-2" style="min-width: 60px;">F</span>
                    <div style="font-size: 13px; font-weight: 500;">0-39%</div>
                    <div style="font-size: 11px; color: #6B7280;">Fail</div>
                </div>
                <div class="col-md-2 col-6">
                    <i class="fa-regular fa-calculator" style="font-size: 32px; color: var(--maneb-blue);"></i>
                    <div style="font-size: 11px; margin-top: 8px;">Scientific Calculator<br>Allowed for Maths & Sciences</div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Helper function to update query parameters
function updateQueryParam(param, value) {
    const url = new URL(window.location.href);
    if (value === 'all') {
        url.searchParams.delete(param);
    } else {
        url.searchParams.set(param, value);
    }
    return url.toString();
}

// Animate trend bars on load
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars
    document.querySelectorAll('.progress-fill').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => { bar.style.width = width; }, 100);
    });
    
    // Animate trend bars
    document.querySelectorAll('.trend-bar .bar').forEach(bar => {
        const height = bar.style.height;
        bar.style.height = '0px';
        setTimeout(() => { bar.style.height = height; }, 150);
    });
    
    // Fade up animation observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.05 });
    
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
    
    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});

// Print styles for better printing
const printStyles = document.createElement('style');
printStyles.textContent = `
    @media print {
        .sidebar, .sidebar-toggle, .filter-bar, .btn, .prediction-card, 
        button, .maneb-badge, .badge-update, .dashboard-card:last-child {
            display: none !important;
        }
        .maneb-header, .dashboard-card, .stat-card, .subject-card, .exam-paper-card {
            break-inside: avoid;
            box-shadow: none;
            border: 1px solid #ddd;
        }
        body, .academic-container {
            padding: 0 !important;
            margin: 0 !important;
            background: white;
        }
        .grade-table th, .grade-table td {
            border: 1px solid #ddd;
        }
    }
`;
document.head.appendChild(printStyles);
</script>


</body>
</html>