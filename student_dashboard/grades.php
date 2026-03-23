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
$student_id   = (int)$student['student_id'];
$full_name    = htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
$first_name   = htmlspecialchars($student['first_name']);
$class_name   = htmlspecialchars($student['class_name'] ?? 'Not Assigned');
$programme    = $student['programme'] ?? 'JCE';
$form_level   = (int)($student['form_level'] ?? 1);
$stream       = htmlspecialchars($student['stream'] ?? '');
$initials     = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));

// ── Filter Parameters with Sanitization ───────────────────────
$subject_filter = isset($_GET['subject']) && is_numeric($_GET['subject']) ? (int)$_GET['subject'] : 'all';
$exam_type      = isset($_GET['exam_type']) ? preg_replace('/[^a-z_]/', '', $_GET['exam_type']) : 'all';
$paper_filter   = isset($_GET['paper']) ? preg_replace('/[^a-z0-9]/', '', $_GET['paper']) : 'all';
$term_filter    = isset($_GET['term']) && is_numeric($_GET['term']) ? (int)$_GET['term'] : 'all';
$year_filter    = isset($_GET['year']) ? preg_replace('/[^0-9]/', '', $_GET['year']) : date('Y');

$allowed_exam_types = ['all', 'test', 'end_of_term', 'mock'];
$exam_type = in_array($exam_type, $allowed_exam_types) ? $exam_type : 'all';

// ── Build Dynamic SQL Query ────────────────────────────────────
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
$types  = "ssi";

if ($subject_filter !== 'all') { $sql .= " AND sub.subject_id = ?"; $params[] = $subject_filter; $types .= "i"; }
if ($exam_type !== 'all')      { $sql .= " AND g.grade_type = ?";   $params[] = $exam_type;      $types .= "s"; }
if ($paper_filter !== 'all')   { $sql .= " AND ep.paper_number = ?"; $params[] = $paper_filter;  $types .= "s"; }
if ($term_filter !== 'all')    { $sql .= " AND g.term = ?";          $params[] = $term_filter;   $types .= "i"; }
if ($year_filter !== 'all')    { $sql .= " AND g.academic_year = ?"; $params[] = $year_filter;   $types .= "i"; }

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
$subjects = $conn->query("SELECT subject_id, subject_name, subject_code FROM subjects ORDER BY subject_name")->fetch_all(MYSQLI_ASSOC) ?: [];
$papers   = $conn->query("SELECT DISTINCT paper_number, paper_title FROM exam_papers WHERE programme = '" . $conn->real_escape_string($programme) . "' ORDER BY paper_number")->fetch_all(MYSQLI_ASSOC) ?: [];

$available_years = array_unique(array_column($grades, 'academic_year'));
rsort($available_years);
$available_terms = array_unique(array_column($grades, 'term'));
sort($available_terms);

// ── Performance Calculations ──────────────────────────────────
$total_grades  = count($grades);
$total_scores  = array_column($grades, 'total_score');
$avg_score     = $total_grades > 0 ? round(array_sum($total_scores) / $total_grades, 1) : 0;

$grade_distribution  = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
$subject_performance = [];
$paper_performance   = [];

foreach ($grades as $g) {
    $letter = $g['standard_grade'] ?? ($g['letter_grade'] ?? 'F');
    if (isset($grade_distribution[$letter])) $grade_distribution[$letter]++;

    $subject = $g['subject_name'];
    if (!isset($subject_performance[$subject])) {
        $subject_performance[$subject] = ['scores' => [], 'best' => 0, 'worst' => 100, 'count' => 0, 'subject_code' => $g['subject_code']];
    }
    $score = (float)$g['total_score'];
    $subject_performance[$subject]['scores'][] = $score;
    $subject_performance[$subject]['best']     = max($subject_performance[$subject]['best'], $score);
    $subject_performance[$subject]['worst']    = min($subject_performance[$subject]['worst'], $score);
    $subject_performance[$subject]['count']++;

    $paper = $g['paper_number'] ?? 'General';
    if (!isset($paper_performance[$paper])) $paper_performance[$paper] = ['scores' => [], 'count' => 0, 'title' => $g['paper_title'] ?? ''];
    $paper_performance[$paper]['scores'][] = $score;
    $paper_performance[$paper]['count']++;
}

foreach ($subject_performance as $s => $d) $subject_performance[$s]['avg'] = round(array_sum($d['scores']) / $d['count'], 1);
foreach ($paper_performance as $p => $d)   $paper_performance[$p]['avg']   = round(array_sum($d['scores']) / $d['count'], 1);
uasort($subject_performance, fn($a, $b) => $b['avg'] <=> $a['avg']);

$trend_data     = array_slice(array_reverse($grades), 0, 10);
$trend_scores   = array_reverse(array_column($trend_data, 'total_score'));
$trend_subjects = array_reverse(array_column($trend_data, 'subject_code'));
$gpa            = $total_grades > 0 ? round(min(4.0, ($avg_score / 25)), 2) : 0;
$ab_rate        = round(($grade_distribution['A'] + $grade_distribution['B']) / max(1, $total_grades) * 100);

// ── Helper Functions ──────────────────────────────────────────
function gradeColor(string $l): string { return match($l){'A'=>'#16a34a','B'=>'#1d4ed8','C'=>'#d97706','D'=>'#ea580c',default=>'#dc2626'}; }
function gradeBg(string $l): string    { return match($l){'A'=>'#dcfce7','B'=>'#dbeafe','C'=>'#fef3c7','D'=>'#ffedd5',default=>'#fee2e2'}; }
function gradeText(string $l): string  { return match($l){'A'=>'#15803d','B'=>'#1e40af','C'=>'#92400e','D'=>'#c2410c',default=>'#b91c1c'}; }
function scoreCss(float $s): string    { return $s >= 70 ? 'hi' : ($s >= 50 ? 'mid' : 'lo'); }

function predictGrade(float $avg): array {
    if ($avg >= 75) return ['A', 'Distinction', '#16a34a'];
    if ($avg >= 65) return ['B', 'Credit', '#1d4ed8'];
    if ($avg >= 50) return ['C', 'Pass', '#d97706'];
    if ($avg >= 40) return ['D', 'Marginal Pass', '#ea580c'];
    return ['F', 'Fail', '#dc2626'];
}

function insight(float $avg, string $prog): array {
    if ($prog === 'MSCE') {
        if ($avg >= 75) return ['You\'re on track for a Distinction — outstanding academic achievement!', 'A'];
        if ($avg >= 65) return ['Credit-level performance. Focus on refining weaker areas for that push to Distinction.', 'B'];
        if ($avg >= 50) return ['Passing at a satisfactory level. More dedication to challenging subjects will help.', 'C'];
        if ($avg >= 40) return ['Marginal pass zone. Seek teacher support now before the final exams.', 'D'];
        return ['Performance needs serious improvement. Please reach out to your teachers for help.', 'F'];
    } else {
        if ($avg >= 70) return ['Excellent JCE performance — building a strong foundation for MSCE!', 'A'];
        if ($avg >= 60) return ['Very good! Showing solid understanding. Push for even higher marks.', 'B'];
        if ($avg >= 45) return ['Meeting expectations. Consistent improvement will secure your JCE.', 'C'];
        if ($avg >= 35) return ['Some areas need attention. Don\'t hesitate to ask your teachers for help.', 'D'];
        return ['Let\'s work together to improve your performance. Speak with your class teacher.', 'F'];
    }
}

[$insight_msg, $insight_grade] = insight($avg_score, $programme);
$predicted = predictGrade($avg_score);

$conn->close();
$page_title = 'Academic Performance | Adaxy Academy';
include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Academic Performance — Adaxy Academy</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ── Base (inherits from your site's global stylesheet) ───────── */
.grades-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 0 24px 24px;
}

/* ── Welcome Hero  (matches .welcome-hero in dashboard) ────────── */
.grades-hero {
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
    border-radius: 28px;
    padding: 32px 40px;
    margin-bottom: 32px;
    position: relative;
    overflow: hidden;
}
.grades-hero::before {
    content: '';
    position: absolute;
    top: -50%; right: -20%;
    width: 300px; height: 300px;
    background: rgba(37, 99, 235, 0.1);
    border-radius: 50%;
    pointer-events: none;
}
.hero-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 24px;
    position: relative;
    z-index: 1;
}
.hero-left {}
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
.hero-left h1 {
    font-size: 32px;
    font-weight: 700;
    color: white;
    margin-bottom: 12px;
}
.hero-meta {
    color: #94A3B8;
    font-size: 14px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px 20px;
    align-items: center;
}
.hero-meta span { display: flex; align-items: center; gap: 6px; }
.hero-meta i { color: #475569; font-size: 13px; }

.hero-score-box { text-align: right; }
.hero-score-num {
    font-size: 64px;
    font-weight: 700;
    color: white;
    line-height: 1;
}
.hero-score-pct { color: #60A5FA; font-size: 28px; font-weight: 400; }
.hero-score-lbl {
    color: #64748B;
    font-size: 12px;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-top: 4px;
}

/* ── MSCE Prediction banner ─────────────────────────────────────── */
.pred-banner {
    background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%);
    border-radius: 24px;
    padding: 24px 32px;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    position: relative;
    overflow: hidden;
}
.pred-banner::after {
    content: 'MSCE';
    position: absolute; right: 32px; bottom: -12px;
    font-size: 80px; font-weight: 700;
    color: rgba(255,255,255,.05);
    pointer-events: none; line-height: 1;
}
.pred-left {}
.pred-icon { font-size: 24px; color: rgba(255,255,255,.7); margin-bottom: 8px; }
.pred-title { font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 4px; }
.pred-sub { color: rgba(255,255,255,.6); font-size: 13px; }
.pred-grade { text-align: center; position: relative; z-index: 1; }
.pred-grade-letter { font-size: 72px; font-weight: 700; color: #fff; line-height: 1; }
.pred-grade-desc { color: rgba(255,255,255,.65); font-size: 13px; margin-top: 4px; }

/* ── Insight card ───────────────────────────────────────────────── */
.insight-card {
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 24px;
    padding: 20px 24px;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.insight-icon {
    width: 48px; height: 48px;
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.insight-msg { flex: 1; font-size: 14px; color: #374151; line-height: 1.55; }
.btn-support {
    display: inline-flex; align-items: center; gap: 6px;
    background: #FEF2F2; border: 1px solid #FECACA;
    border-radius: 40px; padding: 7px 16px;
    font-size: 13px; color: #DC2626;
    text-decoration: none; transition: all .15s; white-space: nowrap;
}
.btn-support:hover { background: #FEE2E2; color: #B91C1C; }

/* ── Filter bar ─────────────────────────────────────────────────── */
.filter-bar {
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 24px;
    padding: 16px 24px;
    margin-bottom: 28px;
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    align-items: center;
    justify-content: space-between;
}
.filter-group { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
.filter-label {
    font-size: 11px; font-weight: 600; text-transform: uppercase;
    letter-spacing: .6px; color: #9CA3AF;
}
.ftag {
    padding: 6px 16px; border-radius: 40px; font-size: 13px; font-weight: 500;
    color: #374151; background: #F9FAFB; border: 1px solid #E5E7EB;
    text-decoration: none; transition: all .15s; white-space: nowrap;
    display: inline-flex; align-items: center; gap: 6px;
}
.ftag:hover { background: #EFF6FF; border-color: #2563EB; color: #1D4ED8; text-decoration: none; }
.ftag.active { background: #2563EB; border-color: #2563EB; color: white; }

.fselect {
    appearance: none;
    background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 40px;
    padding: 6px 30px 6px 16px; font-size: 13px; color: #374151; cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%236B7280'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 12px center;
    transition: border-color .15s;
}
.fselect:focus { outline: none; border-color: #2563EB; }

/* ── Stats grid  (matches .stats-grid in dashboard) ─────────────── */
.stats-row {
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
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
    transition: all .2s ease;
    border: 1px solid #E5E7EB;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(0,0,0,.08); }
.stat-icon {
    width: 52px; height: 52px;
    background: #EFF6FF; border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #2563EB; flex-shrink: 0;
}
.stat-icon.green  { background: #DCFCE7; color: #16A34A; }
.stat-icon.amber  { background: #FEF3C7; color: #D97706; }
.stat-icon.purple { background: #EDE9FE; color: #7C3AED; }
.stat-info h3 {
    font-size: 28px; font-weight: 700; color: #0F172A;
    margin-bottom: 4px; line-height: 1.2;
}
.stat-info p { font-size: 13px; color: #6B7280; margin: 0; }
.stat-trend {
    margin-left: auto;
    font-size: 12px;
    background: #F1F5F9; padding: 4px 10px;
    border-radius: 40px; color: #475569; white-space: nowrap;
}
.stat-trend.green { background: #DCFCE7; color: #15803D; }

/* ── Dashboard card  (matches .dashboard-card) ─────────────────── */
.dashboard-card {
    background: white;
    border-radius: 24px;
    border: 1px solid #E5E7EB;
    overflow: hidden;
    transition: all .2s ease;
    margin-bottom: 28px;
}
.card-header {
    padding: 20px 24px;
    border-bottom: 1px solid #EFF3F8;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.header-title { display: flex; align-items: center; gap: 12px; }
.header-title i { font-size: 20px; color: #2563EB; }
.header-title h3 { font-size: 18px; font-weight: 600; color: #0F172A; margin: 0; }
.card-header p { font-size: 13px; color: #9CA3AF; margin: 0; }

.badge-update {
    font-size: 11px; background: #EFF6FF; color: #2563EB;
    padding: 4px 10px; border-radius: 40px; font-weight: 500;
}

.btn-print {
    display: inline-flex; align-items: center; gap: 6px;
    background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 40px;
    padding: 7px 16px; font-size: 13px; color: #374151; cursor: pointer;
    transition: all .15s;
}
.btn-print:hover { background: #EFF6FF; border-color: #2563EB; color: #1D4ED8; }

/* ── Trend chart ────────────────────────────────────────────────── */
.trend-wrap { padding: 24px; }
.trend-chart {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    height: 130px;
    padding-bottom: 32px;
    position: relative;
}
.trend-chart::before, .trend-chart::after {
    content: ''; position: absolute; left: 0; right: 0;
    border-top: 1px dashed #E5E7EB; pointer-events: none;
}
.trend-chart::before { bottom: 32px; }
.trend-chart::after  { bottom: calc(32px + 49px); }
.t-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 5px; }
.t-bar {
    width: 100%; max-width: 40px; border-radius: 6px 6px 0 0;
    min-height: 4px; transition: height .7s cubic-bezier(.22,.88,.36,1);
    position: relative; cursor: default;
}
.t-bar:hover::after {
    content: attr(data-score);
    position: absolute; top: -26px; left: 50%; transform: translateX(-50%);
    background: #0F172A; color: #fff; font-size: 11px;
    padding: 3px 8px; border-radius: 6px; white-space: nowrap;
}
.t-score { font-size: 11px; font-weight: 600; }
.t-sub   { font-size: 9px; color: #9CA3AF; text-transform: uppercase; letter-spacing: .5px; }

/* ── Subject grid ───────────────────────────────────────────────── */
.subj-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 14px;
    padding: 20px 24px;
}
.subj-card {
    background: #F9FAFB;
    border: 1px solid #EFF3F8;
    border-radius: 18px;
    padding: 18px 20px;
    transition: all .2s;
}
.subj-card:hover {
    background: white;
    box-shadow: 0 4px 12px rgba(0,0,0,.06);
    border-color: #E5E7EB;
}
.subj-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.subject-code {
    font-size: 11px; font-weight: 600;
    background: #F1F5F9; padding: 3px 8px;
    border-radius: 6px; color: #475569;
    display: inline-block; margin-bottom: 4px;
}
.subj-name { font-size: 14px; font-weight: 600; color: #0F172A; }
.subj-avg  { font-size: 28px; font-weight: 700; line-height: 1; }
.subj-stats { display: flex; gap: 12px; margin-bottom: 10px; font-size: 12px; color: #6B7280; }
.subj-stats span { display: flex; align-items: center; gap: 4px; }
.prog-bar  { height: 5px; background: #E5E7EB; border-radius: 4px; overflow: hidden; }
.prog-fill { height: 100%; border-radius: 4px; transition: width .6s ease; }

/* ── Paper cards ────────────────────────────────────────────────── */
.paper-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 14px; padding: 20px 24px;
}
.paper-card {
    background: white; border: 1px solid #E5E7EB;
    border-left: 4px solid #2563EB;
    border-radius: 18px; padding: 18px;
    transition: all .2s;
}
.paper-card:hover { transform: translateX(4px); box-shadow: 0 4px 16px rgba(37,99,235,.1); }
.paper-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.paper-num {
    font-size: 11px; font-weight: 600;
    color: #2563EB; background: #EFF6FF;
    padding: 3px 12px; border-radius: 40px;
}
.paper-avg-num { font-size: 26px; font-weight: 700; line-height: 1; }
.paper-title-txt { font-size: 12px; color: #6B7280; margin-bottom: 10px; }
.paper-count { font-size: 11px; color: #9CA3AF; margin-top: 8px; }

/* ── Grades table  (matches .grades-mini-table) ─────────────────── */
.grades-table-wrap { overflow-x: auto; }
.grades-table {
    width: 100%; border-collapse: collapse; font-size: 14px;
}
.grades-table thead th {
    text-align: left; padding: 14px 20px;
    font-size: 12px; font-weight: 600; color: #6B7280;
    background: #F9FAFB; border-bottom: 1px solid #EFF3F8;
    white-space: nowrap;
}
.grades-table tbody td {
    padding: 14px 20px;
    border-bottom: 1px solid #F0F2F5;
    color: #1F2937; vertical-align: middle;
}
.grades-table tbody tr:last-child td { border-bottom: none; }
.grades-table tbody tr:hover td { background: #FAFDFF; }

.subject-cell { display: flex; align-items: center; gap: 10px; }
.subject-name { color: #1F2937; }
.paper-tag {
    font-size: 11px; background: #F1F5F9; border: 1px solid #E5E7EB;
    padding: 3px 10px; border-radius: 6px; color: #475569; white-space: nowrap;
}
.grade-badge {
    display: inline-block; padding: 4px 12px;
    border-radius: 40px; font-size: 12px; font-weight: 600;
}
.grade-a { background: #DCFCE7; color: #15803D; }
.grade-b { background: #DBEAFE; color: #1E40AF; }
.grade-c { background: #FEF9C3; color: #854D0E; }
.grade-d { background: #FFEDD5; color: #C2410C; }
.grade-f { background: #FEE2E2; color: #B91C1C; }

.score-cell { font-size: 14px; }
.score-cell.hi  { color: #16A34A; font-weight: 600; }
.score-cell.mid { color: #D97706; font-weight: 600; }
.score-cell.lo  { color: #DC2626; font-weight: 600; }
.dash-val { color: #9CA3AF; }

/* ── Grade guide ────────────────────────────────────────────────── */
.grade-guide { display: flex; flex-wrap: wrap; gap: 12px; padding: 20px 24px; }
.gg-item {
    flex: 1; min-width: 110px; text-align: center;
    background: #F9FAFB; border: 1px solid #EFF3F8;
    border-radius: 18px; padding: 16px 10px;
    transition: all .15s;
}
.gg-item:hover { background: white; box-shadow: 0 4px 12px rgba(0,0,0,.06); }
.gg-letter { font-size: 32px; font-weight: 700; line-height: 1; margin-bottom: 6px; }
.gg-range  { font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 2px; }
.gg-name   { font-size: 11px; color: #6B7280; }

/* ── Empty state  (matches dashboard) ──────────────────────────── */
.empty-state { text-align: center; padding: 48px 24px; color: #9CA3AF; }
.empty-state i { font-size: 48px; margin-bottom: 16px; opacity: .5; display: block; }
.empty-state h4 { font-size: 16px; color: #6B7280; margin-bottom: 8px; }
.empty-state p  { font-size: 14px; }

/* ── Animation  (same .fade-up from dashboard) ──────────────────── */
.fade-up { opacity: 0; transform: translateY(20px); transition: opacity .5s ease, transform .5s ease; }
.fade-up.visible { opacity: 1; transform: translateY(0); }

/* ── Responsive ─────────────────────────────────────────────────── */
@media (max-width: 1200px) { .stats-row { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) {
    .grades-container { padding: 0 16px 16px; }
    .grades-hero { padding: 24px; }
    .hero-left h1 { font-size: 24px; }
    .hero-score-num { font-size: 48px; }
    .stats-row { grid-template-columns: repeat(2, 1fr); gap: 14px; }
    .card-header { padding: 16px 20px; }
    .subj-grid, .paper-grid, .trend-wrap, .grade-guide { padding: 16px 20px; }
    .pred-banner { padding: 20px 24px; }
    .grades-table thead th, .grades-table tbody td { padding: 10px 14px; font-size: 13px; }
}
@media (max-width: 480px) {
    .stats-row { grid-template-columns: 1fr 1fr; }
    .filter-bar { flex-direction: column; align-items: flex-start; }
}

/* ── Print ──────────────────────────────────────────────────────── */
@media print {
    .filter-bar, .btn-print, .btn-support, .pred-banner, .insight-card { display: none !important; }
    .grades-hero { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
    body { background: white; }
    .dashboard-card { box-shadow: none; border: 1px solid #ddd; break-inside: avoid; }
}
</style>
</head>
<body>

<div class="grades-container">

  <!-- HERO ─────────────────────────────────────────────────────── -->
  <div class="grades-hero fade-up">
    <div class="hero-inner">
      <div class="hero-left">
        <div class="greeting-badge">
          <i class="fa-regular fa-graduation-cap"></i> MANEB Standards &amp; Assessment
        </div>
        <h1>Academic Performance</h1>
        <div class="hero-meta">
          <span><i class="fa-solid fa-school"></i> <?= $class_name ?><?= $stream ? " · Stream {$stream}" : '' ?></span>
          <span><i class="fa-solid fa-list-check"></i> <?= $programme ?> Programme</span>
          <span><i class="fa-solid fa-layer-group"></i> Form <?= $form_level ?></span>
        </div>
      </div>
      <div class="hero-score-box">
        <div class="hero-score-num"><?= number_format($avg_score, 1) ?><span class="hero-score-pct">%</span></div>
        <div class="hero-score-lbl">Overall Average</div>
      </div>
    </div>
  </div>

  <!-- MSCE PREDICTION ──────────────────────────────────────────── -->
  <?php if ($programme === 'MSCE'): [$pg, $pd, $pc] = $predicted; ?>
  <div class="pred-banner fade-up" style="transition-delay:.05s">
    <div class="pred-left">
      <div class="pred-icon"><i class="fa-solid fa-chart-line"></i></div>
      <div class="pred-title">MSCE Predicted Grade</div>
      <div class="pred-sub">Based on current performance against MANEB grading standards</div>
    </div>
    <div class="pred-grade">
      <div class="pred-grade-letter" style="color:<?= $pc ?>"><?= $pg ?></div>
      <div class="pred-grade-desc"><?= $pd ?></div>
    </div>
  </div>
  <?php endif; ?>

  <!-- INSIGHT ──────────────────────────────────────────────────── -->
  <div class="insight-card fade-up" style="transition-delay:.08s">
    <?php
      $ig_bg  = ['A'=>'#DCFCE7','B'=>'#EFF6FF','C'=>'#FEF9C3','D'=>'#FFEDD5','F'=>'#FEE2E2'];
      $ig_col = ['A'=>'#16A34A','B'=>'#2563EB','C'=>'#D97706','D'=>'#EA580C','F'=>'#DC2626'];
      $ig_ico = ['A'=>'fa-star','B'=>'fa-arrow-trend-up','C'=>'fa-book-open','D'=>'fa-triangle-exclamation','F'=>'fa-circle-exclamation'];
    ?>
    <div class="insight-icon" style="background:<?= $ig_bg[$insight_grade] ?>;color:<?= $ig_col[$insight_grade] ?>">
      <i class="fa-solid <?= $ig_ico[$insight_grade] ?>"></i>
    </div>
    <div class="insight-msg"><?= htmlspecialchars($insight_msg) ?></div>
    <?php if ($avg_score < 50): ?>
    <a href="concern.php" class="btn-support"><i class="fa-solid fa-message"></i> Request Support</a>
    <?php endif; ?>
  </div>

  <!-- FILTERS ──────────────────────────────────────────────────── -->
  <div class="filter-bar fade-up" style="transition-delay:.10s">
    <div class="filter-group">
      <span class="filter-label">Subject</span>
      <a href="?subject=all&exam_type=<?= $exam_type ?>&paper=<?= $paper_filter ?>&term=<?= $term_filter ?>&year=<?= $year_filter ?>"
         class="ftag <?= $subject_filter === 'all' ? 'active' : '' ?>">All</a>
      <?php foreach ($subjects as $s): ?>
      <a href="?subject=<?= $s['subject_id'] ?>&exam_type=<?= $exam_type ?>&paper=<?= $paper_filter ?>&term=<?= $term_filter ?>&year=<?= $year_filter ?>"
         class="ftag <?= $subject_filter == $s['subject_id'] ? 'active' : '' ?>">
        <?= htmlspecialchars($s['subject_code']) ?>
      </a>
      <?php endforeach; ?>
    </div>
    <div class="filter-group">
      <select class="fselect" onchange="window.location.href=setParam('exam_type',this.value)">
        <option value="all"         <?= $exam_type==='all'?'selected':'' ?>>All Assessments</option>
        <option value="test"        <?= $exam_type==='test'?'selected':'' ?>>Tests &amp; CA</option>
        <option value="end_of_term" <?= $exam_type==='end_of_term'?'selected':'' ?>>End of Term</option>
      </select>
      <?php if (!empty($available_terms)): ?>
      <select class="fselect" onchange="window.location.href=setParam('term',this.value)">
        <option value="all" <?= $term_filter==='all'?'selected':'' ?>>All Terms</option>
        <?php foreach ($available_terms as $t): ?>
        <option value="<?= $t ?>" <?= $term_filter==$t?'selected':'' ?>>Term <?= $t ?></option>
        <?php endforeach; ?>
      </select>
      <?php endif; ?>
      <?php if (!empty($available_years)): ?>
      <select class="fselect" onchange="window.location.href=setParam('year',this.value)">
        <option value="all" <?= $year_filter==='all'?'selected':'' ?>>All Years</option>
        <?php foreach ($available_years as $y): ?>
        <option value="<?= $y ?>" <?= $year_filter==$y?'selected':'' ?>><?= $y ?></option>
        <?php endforeach; ?>
      </select>
      <?php endif; ?>
    </div>
  </div>

  <!-- STATS ────────────────────────────────────────────────────── -->
  <div class="stats-row">
    <div class="stat-card fade-up" style="transition-delay:.12s">
      <div class="stat-icon"><i class="fa-regular fa-file-lines"></i></div>
      <div class="stat-info">
        <h3><?= $total_grades ?></h3>
        <p>Total Assessments</p>
      </div>
    </div>
    <div class="stat-card fade-up" style="transition-delay:.16s">
      <div class="stat-icon green"><i class="fa-regular fa-star"></i></div>
      <div class="stat-info">
        <h3><?= $grade_distribution['A'] ?></h3>
        <p>A Grades Earned</p>
      </div>
      <?php if ($grade_distribution['A'] > 0): ?>
      <div class="stat-trend green"><i class="fa-solid fa-arrow-up"></i> Excellent</div>
      <?php endif; ?>
    </div>
    <div class="stat-card fade-up" style="transition-delay:.20s">
      <div class="stat-icon amber"><i class="fa-regular fa-chart-line"></i></div>
      <div class="stat-info">
        <h3><?= $gpa ?></h3>
        <p>GPA (4.0 Scale)</p>
      </div>
      <div class="stat-trend"><i class="fa-regular fa-chart-line"></i> Academic standing</div>
    </div>
    <div class="stat-card fade-up" style="transition-delay:.24s">
      <div class="stat-icon purple"><i class="fa-regular fa-circle-check"></i></div>
      <div class="stat-info">
        <h3><?= $ab_rate ?>%</h3>
        <p>A–B Success Rate</p>
      </div>
    </div>
  </div>

  <!-- TREND CHART ──────────────────────────────────────────────── -->
  <?php if (!empty($trend_scores)): ?>
  <div class="dashboard-card fade-up" style="transition-delay:.26s">
    <div class="card-header">
      <div class="header-title">
        <i class="fa-regular fa-chart-area"></i>
        <h3>Recent Performance Trend</h3>
      </div>
      <span class="badge-update">Last <?= count($trend_scores) ?> assessments</span>
    </div>
    <div class="trend-wrap">
      <div class="trend-chart">
        <?php foreach ($trend_scores as $i => $sc):
          $ht  = max(6, round(($sc / 100) * 98));
          $col = $sc >= 70 ? '#16A34A' : ($sc >= 50 ? '#D97706' : '#DC2626');
        ?>
        <div class="t-col">
          <div class="t-bar" data-h="<?= $ht ?>px" data-score="<?= $sc ?>%"
               style="height:0;background:<?= $col ?>;opacity:.85"></div>
          <div class="t-score" style="color:<?= $col ?>"><?= $sc ?>%</div>
          <div class="t-sub"><?= htmlspecialchars($trend_subjects[$i] ?? '') ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- SUBJECT PERFORMANCE ──────────────────────────────────────── -->
  <?php if (!empty($subject_performance)): ?>
  <div class="dashboard-card fade-up" style="transition-delay:.28s">
    <div class="card-header">
      <div class="header-title">
        <i class="fa-regular fa-chart-simple"></i>
        <h3>Subject Performance</h3>
      </div>
      <span class="badge-update">Sorted by performance</span>
    </div>
    <div class="subj-grid">
      <?php foreach ($subject_performance as $sname => $sd):
        $sc  = $sd['avg'] >= 70 ? '#16A34A' : ($sd['avg'] >= 50 ? '#D97706' : '#DC2626');
      ?>
      <div class="subj-card">
        <div class="subj-top">
          <div>
            <span class="subject-code"><?= htmlspecialchars($sd['subject_code']) ?></span>
            <div class="subj-name"><?= htmlspecialchars($sname) ?></div>
          </div>
          <div class="subj-avg" style="color:<?= $sc ?>"><?= $sd['avg'] ?>%</div>
        </div>
        <div class="subj-stats">
          <span><i class="fa-solid fa-arrow-up" style="color:#16A34A"></i> <?= $sd['best'] ?>%</span>
          <span><i class="fa-solid fa-arrow-down" style="color:#DC2626"></i> <?= $sd['worst'] ?>%</span>
          <span><i class="fa-solid fa-layer-group"></i> <?= $sd['count'] ?></span>
        </div>
        <div class="prog-bar">
          <div class="prog-fill" data-w="<?= $sd['avg'] ?>%" style="width:0;background:<?= $sc ?>"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- PAPER ANALYSIS ───────────────────────────────────────────── -->
  <?php if (!empty($paper_performance) && count($paper_performance) > 1): ?>
  <div class="dashboard-card fade-up" style="transition-delay:.30s">
    <div class="card-header">
      <div class="header-title">
        <i class="fa-regular fa-file-lines"></i>
        <h3>Exam Paper Analysis</h3>
      </div>
      <span class="badge-update">MANEB Structure</span>
    </div>
    <div class="paper-grid">
      <?php foreach ($paper_performance as $pnum => $pd):
        $pc = $pd['avg'] >= 70 ? '#16A34A' : ($pd['avg'] >= 50 ? '#D97706' : '#DC2626');
      ?>
      <div class="paper-card">
        <div class="paper-top">
          <span class="paper-num">Paper <?= $pnum !== 'General' ? $pnum : 'All' ?></span>
          <div class="paper-avg-num" style="color:<?= $pc ?>"><?= $pd['avg'] ?>%</div>
        </div>
        <?php if ($pd['title']): ?>
        <div class="paper-title-txt"><?= htmlspecialchars($pd['title']) ?></div>
        <?php endif; ?>
        <div class="prog-bar">
          <div class="prog-fill" data-w="<?= $pd['avg'] ?>%" style="width:0;background:<?= $pc ?>"></div>
        </div>
        <div class="paper-count"><?= $pd['count'] ?> assessment(s)</div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- GRADES TABLE ─────────────────────────────────────────────── -->
  <div class="dashboard-card fade-up" style="transition-delay:.32s">
    <div class="card-header">
      <div class="header-title">
        <i class="fa-regular fa-table"></i>
        <h3>Complete Grade Record</h3>
      </div>
      <div style="display:flex;gap:10px;align-items:center;">
        <span class="badge-update"><i class="fa-regular fa-file-lines"></i> <?= $total_grades ?> records</span>
        <button class="btn-print" onclick="window.print()">
          <i class="fa-solid fa-print"></i> Print Report
        </button>
      </div>
    </div>

    <?php if ($grades): ?>
    <div class="grades-table-wrap">
      <table class="grades-table">
        <thead>
          <tr>
            <th>Subject</th>
            <th>Paper</th>
            <th>CA 1</th>
            <th>CA 2</th>
            <th>CA 3</th>
            <th>Exam</th>
            <th>Total</th>
            <th>Grade</th>
            <th>Term / Year</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($grades as $g):
          $mg  = $g['standard_grade'] ?? ($g['letter_grade'] ?? 'F');
          $sc  = scoreCss((float)$g['total_score']);
          $badge = 'grade-' . strtolower($mg === 'F' ? 'f' : $mg);
          $fmt = fn($v) => ($v !== null && (float)$v > 0)
                   ? '<strong>'.htmlspecialchars($v).'</strong>'
                   : '<span class="dash-val">—</span>';
        ?>
          <tr>
            <td>
              <div class="subject-cell">
                <span class="subject-code"><?= htmlspecialchars($g['subject_code'] ?? strtoupper(substr($g['subject_name'],0,3))) ?></span>
                <span class="subject-name"><?= htmlspecialchars($g['subject_name']) ?></span>
              </div>
            </td>
            <td>
              <?= $g['paper_number']
                ? '<span class="paper-tag">Paper '.$g['paper_number'].'</span>'
                : '<span class="dash-val">—</span>' ?>
            </td>
            <td><?= $fmt($g['ca1_score'] ?? null) ?></td>
            <td><?= $fmt($g['ca2_score'] ?? null) ?></td>
            <td><?= $fmt($g['ca3_score'] ?? null) ?></td>
            <td><?= $fmt($g['exam_score'] ?? null) ?></td>
            <td class="score-cell <?= $sc ?>">
              <strong><?= number_format((float)$g['total_score'], 1) ?>%</strong>
            </td>
            <td>
              <span class="grade-badge <?= $badge ?>"><?= $mg ?></span>
            </td>
            <td style="font-size:13px;color:#6B7280;white-space:nowrap;">
              Term <?= $g['term'] ?> · <?= $g['academic_year'] ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <i class="fa-regular fa-folder-open"></i>
      <h4>No Grade Records Found</h4>
      <p>Your academic results will appear here as assessments are completed and recorded by your teachers.</p>
    </div>
    <?php endif; ?>
  </div>

  <!-- MANEB GRADE GUIDE ────────────────────────────────────────── -->
  <div class="dashboard-card fade-up" style="transition-delay:.34s">
    <div class="card-header">
      <div class="header-title">
        <i class="fa-regular fa-graduation-cap"></i>
        <h3>MANEB Grading Scale</h3>
      </div>
      <span style="font-size:13px;color:#6B7280;"><?= $programme ?> Programme</span>
    </div>
    <div class="grade-guide">
      <?php foreach ([['A','75–100%','Distinction'],['B','65–74%','Credit'],['C','50–64%','Pass'],['D','40–49%','Marginal Pass'],['F','0–39%','Fail']] as [$gl,$gr,$gd]): ?>
      <div class="gg-item">
        <div class="gg-letter" style="color:<?= gradeColor($gl) ?>"><?= $gl ?></div>
        <div class="gg-range"><?= $gr ?></div>
        <div class="gg-name"><?= $gd ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div><!-- /.grades-container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function setParam(key, val) {
    const u = new URL(window.location.href);
    val === 'all' ? u.searchParams.delete(key) : u.searchParams.set(key, val);
    return u.toString();
}

// Same observer pattern as dashboard
(function () {
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(en => {
            if (en.isIntersecting) { en.target.classList.add('visible'); obs.unobserve(en.target); }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.fade-up').forEach(el => obs.observe(el));
})();

window.addEventListener('load', () => {
    document.querySelectorAll('.t-bar[data-h]').forEach((b, i) => {
        setTimeout(() => { b.style.height = b.dataset.h; }, 200 + i * 60);
    });
    document.querySelectorAll('.prog-fill[data-w]').forEach((f, i) => {
        setTimeout(() => { f.style.width = f.dataset.w; }, 300 + i * 40);
    });
});
</script>
</body>
</html>