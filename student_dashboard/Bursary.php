<?php
// ============================================================
//  Adaxy Academy · Scholarship Opportunities
//  Simple announcements page with external links
// ============================================================

session_start();
include('../config/db_connect.php');

// ── Authentication Guard ─────────────────────────────────────
if (empty($_SESSION['slogin'])) {
    header('Location: ../Auth/login.php?role=student');
    exit;
}

$username = $_SESSION['slogin'];

// ── Fetch Student Information ────────────────────────────────
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

$full_name  = $student['first_name'] . ' ' . $student['last_name'];
$first_name = $student['first_name'];
$class_name = $student['class_name'] ?? 'N/A';
$programme  = $student['programme'];
$form_level = (int)$student['form_level'];
$initials   = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));

// ── Fetch Scholarship Announcements ──────────────────────────
// You can either use a scholarships table or just hardcode for now
// Option 1: If you have a scholarships table
$scholarships = [];

// Check if scholarships table exists
$table_check = $conn->query("SHOW TABLES LIKE 'scholarships'");
if ($table_check && $table_check->num_rows > 0) {
    $result = $conn->query("
        SELECT * FROM scholarships 
        WHERE is_active = 1 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    if ($result) {
        $scholarships = $result->fetch_all(MYSQLI_ASSOC);
    }
}

// Option 2: Hardcoded scholarships (fallback if table doesn't exist)
if (empty($scholarships)) {
    $scholarships = [
        [
            'scholarship_name' => 'Malawi Government Merit Scholarship',
            'provider' => 'Ministry of Education',
            'description' => 'Full tuition scholarship for top-performing MSCE students pursuing university education in Malawi.',
            'requirements' => 'Minimum 12 points at MSCE, admitted to a public university in Malawi',
            'deadline' => '2025-12-31',
            'website_url' => 'https://www.education.gov.mw/scholarships',
            'amount' => 'Full Tuition'
        ],
        [
            'scholarship_name' => 'Mastercard Foundation Scholars Program',
            'provider' => 'Mastercard Foundation',
            'description' => 'Comprehensive scholarship for academically talented but economically disadvantaged students from Africa.',
            'requirements' => 'Excellent academic record, leadership potential, community service involvement',
            'deadline' => '2025-10-15',
            'website_url' => 'https://mastercardfdn.org/scholars',
            'amount' => 'Full Scholarship'
        ],
        [
            'scholarship_name' => 'DAAD Scholarship for Sub-Saharan Africa',
            'provider' => 'German Academic Exchange Service',
            'description' => 'For Malawian students wishing to pursue postgraduate studies in Germany.',
            'requirements' => 'Bachelor\'s degree with above-average results, 2+ years work experience',
            'deadline' => '2025-11-30',
            'website_url' => 'https://www.daad.org.za',
            'amount' => 'Up to €10,000/year'
        ],
        [
            'scholarship_name' => 'African Leadership Academy Scholarship',
            'provider' => 'ALA',
            'description' => 'Two-year pre-university program for young African leaders aged 16-19.',
            'requirements' => 'Strong academic record, leadership potential, commitment to Africa\'s development',
            'deadline' => '2025-09-30',
            'website_url' => 'https://www.africanleadershipacademy.org',
            'amount' => 'Full Scholarship'
        ],
        [
            'scholarship_name' => 'Commonwealth Shared Scholarships',
            'provider' => 'UK Government',
            'description' => 'For students from developing Commonwealth countries to study Master\'s degrees in the UK.',
            'requirements' => 'Bachelor\'s degree, demonstrated financial need',
            'deadline' => '2025-12-15',
            'website_url' => 'https://cscuk.fcdo.gov.uk',
            'amount' => 'Full Tuition + Living Stipend'
        ],
        [
            'scholarship_name' => 'Equity Leaders Program',
            'provider' => 'Equity Group Foundation',
            'description' => 'Leadership development and scholarship program for high-achieving students.',
            'requirements' => 'Excellent MSCE results, leadership potential',
            'deadline' => '2025-10-31',
            'website_url' => 'https://equitygroupfoundation.com/elp',
            'amount' => 'Varies'
        ]
    ];
}

$conn->close();
$page_title = 'Scholarship Opportunities';
include 'includes/header.php';
?>

<style>
    .scholarship-hero {
        background: linear-gradient(135deg, #0F2B3D 0%, #1A4A6F 100%);
        border-radius: 24px;
        padding: 32px 36px;
        margin-bottom: 32px;
        color: white;
    }
    
    .scholarship-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 24px;
        transition: all 0.3s ease;
        border: 1px solid #E5E7EB;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    
    .scholarship-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1);
        border-color: #2563EB;
    }
    
    .scholarship-title {
        font-size: 20px;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 8px;
    }
    
    .scholarship-provider {
        font-size: 13px;
        color: #2563EB;
        font-weight: 600;
        margin-bottom: 12px;
        display: inline-block;
        background: #EFF6FF;
        padding: 4px 12px;
        border-radius: 30px;
    }
    
    .scholarship-amount {
        font-size: 18px;
        font-weight: 700;
        color: #10B981;
        margin-bottom: 12px;
    }
    
    .scholarship-description {
        color: #4B5563;
        line-height: 1.6;
        margin-bottom: 16px;
    }
    
    .scholarship-requirements {
        background: #F9FAFB;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 13px;
        color: #6B7280;
        margin-bottom: 20px;
        border-left: 3px solid #2563EB;
    }
    
    .deadline-badge {
        background: #FEF3C7;
        color: #B45309;
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
        margin-right: 12px;
    }
    
    .deadline-urgent {
        background: #FEE2E2;
        color: #B91C1C;
    }
    
    .btn-apply {
        background: #2563EB;
        color: white;
        padding: 10px 24px;
        border-radius: 40px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }
    
    .btn-apply:hover {
        background: #1D4ED8;
        color: white;
        transform: translateX(4px);
    }
    
    .btn-external {
        background: #F3F4F6;
        color: #1F2937;
    }
    
    .btn-external:hover {
        background: #E5E7EB;
        color: #0F172A;
    }
    
    .info-box {
        background: #F0F9FF;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 24px;
        border-left: 4px solid #2563EB;
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
        transform: translateY(24px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }
    
    .fade-up.visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    @media (max-width: 768px) {
        .scholarship-hero { padding: 24px; }
        .scholarship-card { padding: 20px; }
        .scholarship-title { font-size: 18px; }
    }
</style>

<div class="scholarship-container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px 40px;">

    <!-- Hero Section -->
    <div class="scholarship-hero fade-up">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div style="background: rgba(255,255,255,0.2); display: inline-block; padding: 6px 14px; border-radius: 30px; font-size: 12px; margin-bottom: 16px;">
                    <i class="fa-regular fa-graduation-cap"></i> Funding Opportunities
                </div>
                <h1 style="color: white; margin-bottom: 12px; font-size: 28px;">Scholarship & Bursary Announcements</h1>
                <p style="color: #B0C4DE; margin-bottom: 0;">
                    Discover funding opportunities for your education. Click on any opportunity to visit the official website.
                </p>
            </div>
            <div class="text-center">
                <div style="font-size: 36px; font-weight: 700; color: #FFD966;"><?= count($scholarships) ?></div>
                <div style="font-size: 12px; color: #B0C4DE;">Active Opportunities</div>
            </div>
        </div>
    </div>

    <!-- Information Box -->
    <div class="info-box fade-up">
        <div class="d-flex gap-3 align-items-start">
            <i class="fa-regular fa-circle-info" style="font-size: 24px; color: #2563EB;"></i>
            <div>
                <h4 style="margin: 0 0 8px; font-size: 16px; font-weight: 600;">How to Apply</h4>
                <p style="margin: 0; font-size: 14px; color: #4B5563;">
                    Each scholarship has its own application process. Click the "Visit Website" button to learn more and apply directly through the official scholarship provider's website.
                </p>
            </div>
        </div>
    </div>

    <!-- Scholarship Cards -->
    <?php if (!empty($scholarships)): ?>
        <?php foreach ($scholarships as $scholarship): 
            $days_left = isset($scholarship['deadline']) && $scholarship['deadline'] ? ceil((strtotime($scholarship['deadline']) - time()) / 86400) : null;
            $is_urgent = $days_left !== null && $days_left <= 14 && $days_left > 0;
            $website_url = $scholarship['website_url'] ?? '#';
        ?>
        <div class="scholarship-card fade-up">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                <div class="flex-grow-1">
                    <h3 class="scholarship-title"><?= htmlspecialchars($scholarship['scholarship_name']) ?></h3>
                    <div class="scholarship-provider">
                        <i class="fa-regular fa-building"></i> <?= htmlspecialchars($scholarship['provider']) ?>
                    </div>
                </div>
                <?php if (isset($scholarship['amount']) && $scholarship['amount']): ?>
                <div class="scholarship-amount">
                    <i class="fa-regular fa-coins"></i> <?= htmlspecialchars($scholarship['amount']) ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="scholarship-description">
                <?= nl2br(htmlspecialchars($scholarship['description'])) ?>
            </div>
            
            <?php if (isset($scholarship['requirements']) && $scholarship['requirements']): ?>
            <div class="scholarship-requirements">
                <i class="fa-regular fa-list-check me-2"></i> 
                <strong>Requirements:</strong> <?= htmlspecialchars($scholarship['requirements']) ?>
            </div>
            <?php endif; ?>
            
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <?php if (isset($scholarship['deadline']) && $scholarship['deadline']): ?>
                    <span class="deadline-badge <?= $is_urgent ? 'deadline-urgent' : '' ?>">
                        <i class="fa-regular fa-calendar"></i> Deadline: <?= date('F j, Y', strtotime($scholarship['deadline'])) ?>
                        <?php if ($days_left !== null && $days_left > 0 && $days_left <= 30): ?>
                            (<?= $days_left ?> days left)
                        <?php elseif ($days_left !== null && $days_left <= 0): ?>
                            (Expired)
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>
                </div>
                <a href="<?= $website_url ?>" target="_blank" rel="noopener noreferrer" class="btn-apply">
                    <i class="fa-regular fa-arrow-up-right-from-square"></i> Visit Website
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state fade-up">
            <i class="fa-regular fa-inbox" style="font-size: 56px; color: #CBD5E1; margin-bottom: 20px;"></i>
            <h3 style="color: #4B5563; margin-bottom: 12px;">No Scholarships Available</h3>
            <p style="color: #9CA3AF; max-width: 400px; margin: 0 auto;">
                There are no scholarship announcements at this time. Check back later for new opportunities.
            </p>
        </div>
    <?php endif; ?>

    <!-- Tips Section -->
    <div class="row g-4 mt-4">
        <div class="col-md-6">
            <div class="scholarship-card" style="background: #F8FAFE;">
                <div class="d-flex gap-3">
                    <i class="fa-regular fa-lightbulb" style="font-size: 28px; color: #F59E0B;"></i>
                    <div>
                        <h4 style="margin: 0 0 8px; font-size: 16px; font-weight: 600;">Tips for Scholarship Applications</h4>
                        <ul style="margin: 0; padding-left: 20px; color: #6B7280; font-size: 13px;">
                            <li>Start your application early</li>
                            <li>Read all requirements carefully</li>
                            <li>Prepare your academic transcripts</li>
                            <li>Get recommendation letters from teachers</li>
                            <li>Write a compelling personal statement</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="scholarship-card" style="background: #F8FAFE;">
                <div class="d-flex gap-3">
                    <i class="fa-regular fa-circle-question" style="font-size: 28px; color: #2563EB;"></i>
                    <div>
                        <h4 style="margin: 0 0 8px; font-size: 16px; font-weight: 600;">Need Help?</h4>
                        <p style="margin: 0; color: #6B7280; font-size: 13px;">
                            Visit the Academic Advising Office for assistance with scholarship applications.
                        </p>
                        <p style="margin: 12px 0 0; color: #2563EB; font-size: 13px;">
                            <i class="fa-regular fa-envelope"></i> advising@adaxy.mw
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
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

