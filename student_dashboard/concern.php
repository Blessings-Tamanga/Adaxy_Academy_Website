<?php
// ============================================================
//  Adaxy Academy · Raise a Concern
//  Simple, professional communication form with teacher selection
// ============================================================

session_start();
include('../config/db_connect.php');

if (empty($_SESSION['slogin'])) {
    header('Location: ../Auth/login.php?role=student');
    exit;
}

$username = $_SESSION['slogin'];

$stmt = $conn->prepare("
    SELECT s.*, c.class_name, c.form_level, c.programme, c.class_id
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
$class_id   = $student['class_id'] ?? 0;
$roll_number = $student['roll_number'];
$initials   = strtoupper(substr($student['first_name'],0,1) . substr($student['last_name'],0,1));

// Fetch teachers for this class
$teachers = [];
if ($class_id > 0) {
    $stmt = $conn->prepare("
        SELECT DISTINCT 
            t.teacher_id,
            t.first_name,
            t.last_name,
            t.email,
            sub.subject_name
        FROM timetable tt
        JOIN teachers t ON t.teacher_id = tt.teacher_id
        JOIN subjects sub ON sub.subject_id = tt.subject_id
        WHERE tt.class_id = ?
        ORDER BY t.first_name
    ");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $teachers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$success = '';
$error   = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_type = trim($_POST['recipient_type'] ?? '');
    $recipient_name = trim($_POST['recipient_name'] ?? '');
    $subject   = trim($_POST['subject'] ?? '');
    $message   = trim($_POST['message'] ?? '');

    if (!$recipient_type || !$subject || !$message) {
        $error = 'Please fill in all fields.';
    } elseif ($recipient_type === 'class_teacher' && !$recipient_name) {
        $error = 'Please select a teacher.';
    } else {
        // Set recipient display name
        if ($recipient_type === 'headmaster') $recipient = 'Headmaster Mr. Bernard Mwale';
        elseif ($recipient_type === 'deputy') $recipient = 'Deputy Head Mrs. Grace Nkhata';
        elseif ($recipient_type === 'registrar') $recipient = 'Registrar Mr. Charles Tembo';
        elseif ($recipient_type === 'bursar') $recipient = 'Bursar Mrs. Alile Soko';
        elseif ($recipient_type === 'counselor') $recipient = 'Guidance Counselor';
        elseif ($recipient_type === 'class_teacher') $recipient = $recipient_name;
        else $recipient = $recipient_type;
        
        $title = "[CONCERN] $subject — $full_name";
        $content = "Recipient: $recipient\n\nMessage:\n$message\n\n---\nStudent: $full_name ($roll_number)\nClass: $class_name";
        
        $stmt = $conn->prepare("
            INSERT INTO notices (title, content, audience, posted_by, posted_role, is_published)
            VALUES (?, ?, 'teachers', ?, 'admin', 0)
        ");
        $stmt->bind_param("sss", $title, $content, $full_name);

        if ($stmt->execute()) {
            $success = 'Your message has been sent successfully to ' . $recipient . '.';
            $_POST = [];
        } else {
            $error = 'Something went wrong. Please try again.';
        }
        $stmt->close();
    }
}

$page_title = 'Raise a Concern';
$conn->close();

include 'includes/header.php';
?>

<style>
    .concern-container {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .recipient-card {
        background: #F8FAFE;
        border-radius: 16px;
        padding: 14px 16px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
    }
    
    .recipient-card:hover {
        background: #EFF6FF;
        transform: translateX(4px);
    }
    
    .recipient-card.selected {
        border-color: #2563EB;
        background: #EFF6FF;
    }
    
    .recipient-name {
        font-weight: 600;
        font-size: 15px;
        color: #0F172A;
    }
    
    .recipient-title {
        font-size: 11px;
        color: #6B7280;
    }
    
    .teacher-dropdown {
        background: #F8FAFE;
        border-radius: 12px;
        padding: 16px;
        margin-top: 12px;
        border-left: 3px solid #2563EB;
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .teacher-option {
        padding: 10px 12px;
        margin: 4px 0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid #E5E7EB;
        background: white;
    }
    
    .teacher-option:hover {
        background: #EFF6FF;
        border-color: #2563EB;
    }
    
    .teacher-option.selected {
        background: #2563EB;
        color: white;
        border-color: #2563EB;
    }
    
    .teacher-subject {
        font-size: 11px;
        color: #6B7280;
        margin-top: 2px;
    }
    
    .teacher-option.selected .teacher-subject {
        color: rgba(255,255,255,0.8);
    }
    
    .alert-success {
        background: #DCFCE7;
        border: 1px solid #86EFAC;
        color: #15803D;
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 20px;
    }
    
    .alert-error {
        background: #FEE2E2;
        border: 1px solid #FECACA;
        color: #B91C1C;
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 20px;
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
    
    .selected-badge {
        display: inline-block;
        background: #2563EB;
        color: white;
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 20px;
        margin-left: 8px;
    }
</style>

<div class="concern-container" style="padding: 0 20px 40px;">

    <!-- Header -->
    <div class="text-center mb-4 fade-up">
        <div class="section-tag">Communication</div>
        <h2 style="font-size: 28px; margin: 8px 0 4px;">Raise a Concern</h2>
        <p style="color: var(--muted);">Send a message to school administration or your teachers</p>
    </div>

    <!-- Form Card -->
    <div class="card-box fade-up">
        <div class="card-box-header">
            <h4><i class="fa fa-envelope me-2" style="color: var(--gold);"></i>New Message</h4>
        </div>

        <?php if ($success): ?>
            <div class="alert-success">
                <i class="fa fa-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert-error">
                <i class="fa fa-circle-xmark me-2"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Student Info (read-only) -->
        <div style="background: #F8FAFE; border-radius: 12px; padding: 14px; margin-bottom: 24px;">
            <div class="row">
                <div class="col-sm-4">
                    <div style="font-size: 11px; color: #6B7280;">From</div>
                    <div style="font-weight: 500;"><?= $full_name ?></div>
                </div>
                <div class="col-sm-4">
                    <div style="font-size: 11px; color: #6B7280;">Roll Number</div>
                    <div style="font-weight: 500;"><?= $roll_number ?></div>
                </div>
                <div class="col-sm-4">
                    <div style="font-size: 11px; color: #6B7280;">Class</div>
                    <div style="font-weight: 500;"><?= $class_name ?></div>
                </div>
            </div>
        </div>

        <form method="POST">
            <!-- Recipient Selection -->
            <div class="mb-4">
                <label class="form-label">Send to <span class="text-danger">*</span></label>
                <input type="hidden" name="recipient_type" id="selectedRecipientType" required>
                <input type="hidden" name="recipient_name" id="selectedRecipientName">
                
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="recipient-card" data-type="headmaster" data-name="Headmaster Mr. Bernard Mwale">
                            <div class="recipient-name">Mr. Bernard Mwale</div>
                            <div class="recipient-title">Headmaster</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="recipient-card" data-type="deputy" data-name="Deputy Head Mrs. Grace Nkhata">
                            <div class="recipient-name">Mrs. Grace Nkhata</div>
                            <div class="recipient-title">Deputy Head - Academics</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="recipient-card" data-type="registrar" data-name="Registrar Mr. Charles Tembo">
                            <div class="recipient-name">Mr. Charles Tembo</div>
                            <div class="recipient-title">Registrar</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="recipient-card" data-type="bursar" data-name="Bursar Mrs. Alile Soko">
                            <div class="recipient-name">Mrs. Alile Soko</div>
                            <div class="recipient-title">Bursar</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="recipient-card" data-type="counselor" data-name="Guidance Counselor">
                            <div class="recipient-name">Guidance Counselor</div>
                            <div class="recipient-title">Student Support</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="recipient-card" data-type="class_teacher" data-name="">
                            <div class="recipient-name">Class Teacher</div>
                            <div class="recipient-title">Your Subject Teachers</div>
                        </div>
                    </div>
                </div>
                
                <!-- Teacher Selection Dropdown (initially hidden) -->
                <div id="teacherSelection" style="display: none;">
                    <?php if (!empty($teachers)): ?>
                    <div class="teacher-dropdown">
                        <div style="font-size: 13px; font-weight: 600; margin-bottom: 12px; color: #0F172A;">
                            <i class="fa fa-chalkboard-user me-1"></i> Select a teacher:
                        </div>
                        <?php foreach ($teachers as $teacher): ?>
                        <div class="teacher-option" 
                             data-teacher-name="<?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?>"
                             data-teacher-id="<?= $teacher['teacher_id'] ?>">
                            <div style="font-weight: 500;">
                                <?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?>
                            </div>
                            <div class="teacher-subject">
                                <i class="fa fa-book-open me-1"></i> <?= htmlspecialchars($teacher['subject_name'] ?? 'Various Subjects') ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="teacher-dropdown" style="background: #FEF3C7;">
                        <div style="font-size: 13px; color: #B45309;">
                            <i class="fa fa-exclamation-triangle me-1"></i> 
                            No teachers assigned to your class yet. Please contact the academic office.
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Subject -->
            <div class="mb-3">
                <label class="form-label">Subject <span class="text-danger">*</span></label>
                <input type="text" name="subject" class="form-control" 
                       placeholder="Brief title of your concern" required maxlength="100"
                       value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
            </div>

            <!-- Message -->
            <div class="mb-4">
                <label class="form-label">Message <span class="text-danger">*</span></label>
                <textarea name="message" class="form-control" rows="5" 
                          placeholder="Describe your concern in detail..." required
                          style="resize: vertical;"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-enroll" style="background: #2563EB; width: 100%;">
                <i class="fa fa-paper-plane"></i> Send Message
            </button>
        </form>
    </div>

    <!-- Help Card -->
    <div class="card-box mt-4 fade-up" style="transition-delay: 0.1s;">
        <div class="card-box-header">
            <h4><i class="fa fa-circle-info me-2" style="color: var(--gold);"></i>Need Immediate Help?</h4>
        </div>
        <div style="display: flex; gap: 20px; flex-wrap: wrap; justify-content: center;">
            <div style="text-align: center;">
                <i class="fa fa-phone" style="font-size: 24px; color: #2563EB;"></i>
                <div style="font-size: 12px; margin-top: 4px;">+265 991 000 004</div>
            </div>
            <div style="text-align: center;">
                <i class="fa fa-envelope" style="font-size: 24px; color: #2563EB;"></i>
                <div style="font-size: 12px; margin-top: 4px;">studentaffairs@adaxy.mw</div>
            </div>
            <div style="text-align: center;">
                <i class="fa fa-building" style="font-size: 24px; color: #2563EB;"></i>
                <div style="font-size: 12px; margin-top: 4px;">Student Affairs Office</div>
            </div>
        </div>
    </div>

</div>

<script>
// Recipient selection
let selectedTeacherName = '';

document.querySelectorAll('.recipient-card').forEach(card => {
    card.addEventListener('click', function() {
        // Remove selected class from all
        document.querySelectorAll('.recipient-card').forEach(c => c.classList.remove('selected'));
        // Add selected class to clicked card
        this.classList.add('selected');
        
        const type = this.getAttribute('data-type');
        const name = this.getAttribute('data-name');
        
        // Set hidden inputs
        document.getElementById('selectedRecipientType').value = type;
        
        // Handle teacher selection dropdown
        const teacherDiv = document.getElementById('teacherSelection');
        
        if (type === 'class_teacher') {
            // Show teacher selection
            teacherDiv.style.display = 'block';
            // Clear previously selected teacher if any
            selectedTeacherName = '';
            document.getElementById('selectedRecipientName').value = '';
            // Remove selected class from teacher options
            document.querySelectorAll('.teacher-option').forEach(opt => {
                opt.classList.remove('selected');
            });
        } else {
            // Hide teacher selection
            teacherDiv.style.display = 'none';
            // Set recipient name directly
            document.getElementById('selectedRecipientName').value = name;
        }
    });
});

// Teacher selection
document.querySelectorAll('.teacher-option').forEach(teacher => {
    teacher.addEventListener('click', function() {
        // Remove selected class from all teacher options
        document.querySelectorAll('.teacher-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        // Add selected class to clicked teacher
        this.classList.add('selected');
        
        // Store selected teacher name
        selectedTeacherName = this.getAttribute('data-teacher-name');
        document.getElementById('selectedRecipientName').value = selectedTeacherName;
        
        // Update the class teacher card to show selected teacher
        const classTeacherCard = document.querySelector('.recipient-card[data-type="class_teacher"]');
        if (classTeacherCard) {
            const existingBadge = classTeacherCard.querySelector('.selected-badge');
            if (existingBadge) existingBadge.remove();
            
            const badge = document.createElement('span');
            badge.className = 'selected-badge';
            badge.innerHTML = selectedTeacherName;
            classTeacherCard.querySelector('.recipient-name').appendChild(badge);
        }
    });
});

// Auto-select if there was a previous selection
<?php if (isset($_POST['recipient_type']) && $_POST['recipient_type']): ?>
document.querySelectorAll('.recipient-card').forEach(card => {
    if (card.getAttribute('data-type') === '<?= $_POST['recipient_type'] ?>') {
        card.click();
        <?php if (isset($_POST['recipient_name']) && $_POST['recipient_name'] && $_POST['recipient_type'] === 'class_teacher'): ?>
        // Auto-select the teacher
        setTimeout(() => {
            document.querySelectorAll('.teacher-option').forEach(teacher => {
                if (teacher.getAttribute('data-teacher-name') === '<?= addslashes($_POST['recipient_name']) ?>') {
                    teacher.click();
                }
            });
        }, 100);
        <?php endif; ?>
    }
});
<?php endif; ?>

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

// Auto-hide alerts after 4 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert-success, .alert-error');
    alerts.forEach(alert => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 4000);
</script>

