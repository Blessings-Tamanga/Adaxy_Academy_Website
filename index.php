<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
  
include "config/db_connect.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Adaxy Academy </title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Font Awesome 6 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ═══════════════════════  TOP BAR  ═══════════════════════ -->
<div class="topbar d-none d-md-block">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div class="d-flex align-items-center flex-wrap gap-1">
        <i class="fa fa-map-marker-alt text-gold me-1" style="color:#C9963B"></i>
        <span>P.O. Box xxx, Lilongwe, Malawi</span>
        <span class="sep">|</span>
        <i class="fa fa-phone me-1"></i>
        <a href="tel:+2650123456">+265 (0)1 xxx xxx</a>
        <span class="sep">|</span>
        <i class="fa fa-envelope me-1"></i>
        <a href="mailto:info@Adaxyacademy.mw">info@AdaxyAcademy.mw</a>
      </div>
      <div class="d-flex align-items-center gap-3">
        <span><i class="fa fa-clock me-1"></i>Mon–Fri: 07:30–17:00</span>
        <a href="#portals" class="d-flex align-items-center gap-1"><i class="fa fa-user-circle"></i> Portal Login</a>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════  NAVBAR  ═══════════════════════ -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <!-- Brand -->
    <a class="navbar-brand" href="#">
      <div class="brand-emblem">
        <img src="DAXY LOGO.png" alt="">
      </div>
      <div>
        <div class="brand-text-top">Adaxy Academy</div>
        <div class="brand-text-sub">Est. 2026 · Lilongwe</div>
      </div>
    </a>

    <button class="navbar-toggler border-0 shadow-none" type="button"
            data-bs-toggle="collapse" data-bs-target="#navMain">
      <i class="fa fa-bars" style="font-size:20px;color:var(--navy)"></i>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-0">

        <!-- Home -->
        <li class="nav-item">
          <a class="nav-link active" href="#">Home</a>
        </li>

        <!-- About -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#about" data-bs-toggle="dropdown">About</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#mission"><i class="fa fa-bullseye"></i> Mission &amp; Vision</a></li>
            <li><a class="dropdown-item" href="#team"><i class="fa fa-users"></i> Our Team</a></li>
          </ul>
        </li>

        <!-- News -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#news" data-bs-toggle="dropdown">News</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#news" data-tab="latest"><i class="fa fa-newspaper"></i> Latest News</a></li>
            <li><a class="dropdown-item" href="#news" data-tab="students"><i class="fa fa-user-graduate"></i> Student News</a></li>
            <li><a class="dropdown-item" href="#news" data-tab="parents"><i class="fa fa-house-user"></i> Parent News</a></li>
          </ul>
        </li>

        <!-- Enroll -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#enroll" data-bs-toggle="dropdown">Enroll</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#enroll"><i class="fa fa-pencil-alt"></i> JCE Students (Form 1–4)</a></li>
            <li><a class="dropdown-item" href="#enroll"><i class="fa fa-pencil-ruler"></i> MSCE Students (Form 5–6)</a></li>
            <li><hr class="dropdown-divider" /></li>
            <li><a class="dropdown-item" href="#enroll"><i class="fa fa-file-alt"></i> Download Forms</a></li>
          </ul>
        </li>

        <!-- Portals -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#portals" data-bs-toggle="dropdown">Portals</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="Auth/login.php?role=student"><i class="fa fa-user-graduate"></i> Student Portal</a></li>
            <li><a class="dropdown-item" href="Auth/login.php?role=teacher"><i class="fa fa-chalkboard-teacher"></i> Teacher Portal</a></li>
          </ul>
        </li>

        <!-- Links -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#links" data-bs-toggle="dropdown">Important Links</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#links"><i class="fa fa-link"></i> Resource Links</a></li>
            <li><a class="dropdown-item" href="#enroll"><i class="fa fa-pen-to-square"></i> Admissions</a></li>
            <li><a class="dropdown-item" href="#portals"><i class="fa fa-user-circle"></i> Portal Login</a></li>
          </ul>
        </li>

      </ul>

      <a href="#enroll" class="btn-enroll ms-lg-4 mt-3 mt-lg-0">
        Apply Now <i class="fa fa-arrow-right ms-1"></i>
      </a>
    </div>
  </div>
</nav>

<!-- ═══════════════════════  HERO  ═══════════════════════ -->
<section id="home" class="hero">
  <div class="hero-media" aria-hidden="true">
    <video class="hero-video" autoplay muted loop playsinline poster="assets/hero.jpg">
      <source src="assets/hero.mp4" type="video/mp4" />
    </video>
  </div>
  <div class="hero-pattern"></div>
  <div class="hero-grid"></div>
  <div class="container py-5">
    <div class="row align-items-center gy-5">
      <div class="col-lg-6 hero-content">
        <h1>Shaping Minds,<br><span>Building Futures</span></h1>
        <p>Adaxy Academy delivers outstanding secondary education grounded in academic excellence, character development, and a passion for lifelong learning.</p>
        <div class="hero-cta">
          <a href="#enroll" class="btn-hero-primary">
            <i class="fa fa-pen-to-square"></i> Apply for Enrolment
          </a>
          <a href="#about" class="btn-hero-secondary">
            <i class="fa fa-play-circle"></i> Discover More
          </a>
        </div>
       
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════  QUICK ACCESS BAND  ═══════════════════════ -->
<div class="quick-band">
  <div class="container">
    <div class="row gy-1">
      <div class="col-6 col-md-3">
        <a href="Auth/login.php?role=student" class="quick-link-item">
          <i class="fa fa-user-circle"></i> Student Portal
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="Auth/login.php?role=teacher" class="quick-link-item">
          <i class="fa fa-chalkboard-teacher"></i> Teacher Portal
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="#enroll" class="quick-link-item">
          <i class="fa fa-file-signature"></i> Apply Online
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="#" class="quick-link-item">
          <i class="fa fa-calendar-days"></i> Academic Calendar
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════  ABOUT  ═══════════════════════ -->
<section id="about">
  <div class="container">
    <!-- Mission / Vision / Values -->
    <div id="mission" class="section-hdr fade-up">
      <h2 class="section-title">Who We Are</h2>
      <div class="divider-gold"></div>
      <p class="section-desc">Founded in 1985, Adaxy Academy has grown into one of Malawi's most respected secondary schools, nurturing students through the JCE and MSCE programmes.</p>
    </div>

    <div class="row gy-4 mb-5">
      <div class="col-md-4 fade-up">
        <div class="about-card">
          <div class="about-icon"><i class="fa fa-bullseye"></i></div>
          <h4>Our Mission</h4>
          <p>To provide a stimulating, inclusive, and values-driven learning environment that empowers every student to achieve their highest academic and personal potential.</p>
        </div>
      </div>
      <div class="col-md-4 fade-up" style="transition-delay:.1s">
        <div class="about-card">
          <div class="about-icon"><i class="fa fa-eye"></i></div>
          <h4>Our Vision</h4>
          <p>To be the leading centre of academic excellence in Malawi, producing graduates who are confident, compassionate, and globally competitive.</p>
        </div>
      </div>
      <div class="col-md-4 fade-up" style="transition-delay:.2s">
        <div class="about-card">
          <div class="about-icon"><i class="fa fa-heart"></i></div>
          <h4>Core Values</h4>
          <p>We are guided by integrity, excellence, discipline, respect, and community service — values that define every aspect of school life at Adaxy Academy.</p>
        </div>
      </div>
    </div>

    <!-- History -->
    <div id="history" class="row gy-5 align-items-start mt-3">
      <div class="col-lg-5 fade-up">
        <div class="section-tag">Our Journey</div>
        <h2 class="section-title">A History of Excellence</h2>
        <div class="divider-gold"></div>
        <p class="mb-4" style="color:var(--muted);font-size:14.5px">From humble beginnings with 120 students, Adaxy Academy has expanded over four decades into a full-service secondary school with state-of-the-art facilities and a legacy of outstanding results.</p>
        <div class="timeline">
          <div class="tl-item">
            <div class="tl-dot"></div>
            <div class="tl-year">1985</div>
            <p class="tl-desc">Adaxy Academy was founded with an inaugural class of 120 students and 12 teachers.</p>
          </div>
          <div class="tl-item">
            <div class="tl-dot"></div>
            <div class="tl-year">1992</div>
            <p class="tl-desc">Construction of the main library block and first science laboratory complex completed.</p>
          </div>
          <div class="tl-item">
            <div class="tl-dot"></div>
            <div class="tl-year">2001</div>
            <p class="tl-desc">Received MANEB recognition for highest MSCE pass rate in Central Region three consecutive years.</p>
          </div>
          <div class="tl-item">
            <div class="tl-dot"></div>
            <div class="tl-year">2015</div>
            <p class="tl-desc">ICT Centre opened; introduced Computer Science as a core MSCE subject.</p>
          </div>
          <div class="tl-item">
            <div class="tl-dot"></div>
            <div class="tl-year">2024</div>
            <p class="tl-desc">Awarded Best Secondary School in Malawi at the National Education Excellence Awards.</p>
          </div>
        </div>
      </div>
      <div class="col-lg-6 offset-lg-1 fade-up" style="transition-delay:.15s">
        <div id="team">
          <div class="section-tag">Leadership</div>
          <h2 class="section-title">Meet the Team</h2>
          <div class="divider-gold"></div>
        </div>
        <div class="row gy-4">
          <div class="col-sm-6">
            <div class="team-card">
              <img class="team-photo" src="assets/team-1.jpg" alt="Mr. Bernard Mwale" />
              <div class="team-overlay">
                <div class="team-name">Mr. Bernard Mwale</div>
                <div class="team-role">Headmaster</div>
                <div class="team-contact">
                  <a href="mailto:bernard.mwale@Adaxyacademy.mw"><i class="fa fa-envelope"></i> bernard.mwale@Adaxyacademy.mw</a>
                  <a href="tel:+265991234001"><i class="fa fa-phone"></i> +265 991 234 001</a>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="team-card">
              <img class="team-photo" src="assets/team-2.jpg" alt="Mrs. Grace Nkhata" />
              <div class="team-overlay">
                <div class="team-name">Mrs. Grace Nkhata</div>
                <div class="team-role">Deputy Head – Academics</div>
                <div class="team-contact">
                  <a href="mailto:grace.nkhata@Adaxyacademy.mw"><i class="fa fa-envelope"></i> grace.nkhata@Adaxyacademy.mw</a>
                  <a href="tel:+265991234002"><i class="fa fa-phone"></i> +265 991 234 002</a>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="team-card">
              <img class="team-photo" src="assets/team-3.jpg" alt="Mr. Charles Tembo" />
              <div class="team-overlay">
                <div class="team-name">Mr. Charles Tembo</div>
                <div class="team-role">Dean of Students</div>
                <div class="team-contact">
                  <a href="mailto:charles.tembo@Adaxyacademy.mw"><i class="fa fa-envelope"></i> charles.tembo@Adaxyacademy.mw</a>
                  <a href="tel:+265991234003"><i class="fa fa-phone"></i> +265 991 234 003</a>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="team-card">
              <img class="team-photo" src="assets/team-4.jpg" alt="Mrs. Alile Soko" />
              <div class="team-overlay">
                <div class="team-name">Mrs. Alile Soko</div>
                <div class="team-role">Head of Administration</div>
                <div class="team-contact">
                  <a href="mailto:alile.soko@Adaxyacademy.mw"><i class="fa fa-envelope"></i> alile.soko@Adaxyacademy.mw</a>
                  <a href="tel:+265991234004"><i class="fa fa-phone"></i> +265 991 234 004</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════  NEWS  ═══════════════════════ -->
<section id="news">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-5 gap-4">
      <div class="fade-up">
        <div class="section-tag">Stay Informed</div>
        <h2 class="section-title mb-0">News &amp; Announcements</h2>
        <div class="divider-gold mt-3 mb-0"></div>
      </div>
      <ul class="nav news-tabs gap-2 fade-up" id="newsTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-latest" type="button">
            <i class="fa fa-bolt me-1"></i> Latest
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-students" type="button">
            <i class="fa fa-user-graduate me-1"></i> Students
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-parents" type="button">
            <i class="fa fa-house-user me-1"></i> Parents
          </button>
        </li>
      </ul>
    </div>

    <div class="tab-content" id="newsTabsContent">
      <!-- Latest -->
      <div class="tab-pane fade show active" id="tab-latest">
        <div class="row gy-4">
          <div class="col-md-4 fade-up">
            <div class="news-card">
              <div class="news-img">
                <i class="fa fa-trophy"></i>
                <span class="news-cat">Achievement</span>
              </div>
              <div class="news-body">
                <div class="news-date"><i class="fa fa-calendar"></i> 5 March 2025</div>
                <h5>Adaxy wins National Education Excellence Award 2024</h5>
                <p>For the second consecutive year, Adaxy Academy has been recognised nationally for outstanding MSCE results and holistic student development.</p>
                <a href="#" class="news-link">Read more <i class="fa fa-arrow-right"></i></a>
              </div>
            </div>
          </div>
          <div class="col-md-4 fade-up" style="transition-delay:.1s">
            <div class="news-card">
              <div class="news-img">
                <i class="fa fa-flask"></i>
                <span class="news-cat">Events</span>
              </div>
              <div class="news-body">
                <div class="news-date"><i class="fa fa-calendar"></i> 20 February 2025</div>
                <h5>Annual Science &amp; Technology Fair — May 2025 Edition</h5>
                <p>Students from all forms are invited to register their projects for the upcoming Science Fair. Entries close 10 May 2025.</p>
                <a href="#" class="news-link">Read more <i class="fa fa-arrow-right"></i></a>
              </div>
            </div>
          </div>
          <div class="col-md-4 fade-up" style="transition-delay:.2s">
            <div class="news-card">
              <div class="news-img">
                <i class="fa fa-book-open"></i>
                <span class="news-cat">Academic</span>
              </div>
              <div class="news-body">
                <div class="news-date"><i class="fa fa-calendar"></i> 10 January 2025</div>
                <h5>New Computer Science syllabus adopted for MSCE 2025</h5>
                <p>In partnership with MANEB, the academy has adopted an updated ICT and Computer Science curriculum aligned with modern industry standards.</p>
                <a href="#" class="news-link">Read more <i class="fa fa-arrow-right"></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Students -->
      <div class="tab-pane fade" id="tab-students">
        <div class="row gy-4">
          <div class="col-md-4 fade-up">
            <div class="news-card">
              <div class="news-img" style="background: linear-gradient(135deg,#1a3a6b,#0b1f3a)">
                <i class="fa fa-medal"></i>
                <span class="news-cat">Students</span>
              </div>
              <div class="news-body">
                <div class="news-date"><i class="fa fa-calendar"></i> 1 March 2025</div>
                <h5>Form 4 student tops Central Region Mathematics Olympiad</h5>
                <p>Chisomo Banda (Form 4B) achieved a perfect score at the MANEB Mathematics Olympiad, earning a scholarship to the Regional Finals.</p>
                <a href="#" class="news-link">Read more <i class="fa fa-arrow-right"></i></a>
              </div>
            </div>
          </div>
          <div class="col-md-4 fade-up" style="transition-delay:.1s">
            <div class="news-card">
              <div class="news-img" style="background: linear-gradient(135deg,#1a3a6b,#0b1f3a)">
                <i class="fa fa-music"></i>
                <span class="news-cat">Students</span>
              </div>
              <div class="news-body">
                <div class="news-date"><i class="fa fa-calendar"></i> 14 February 2025</div>
                <h5>Adaxy Choir qualifies for National Schools Music Festival</h5>
                <p>After winning the regional heats, the school choir will represent Lilongwe at the National Schools Music Festival in Blantyre this April.</p>
                <a href="#" class="news-link">Read more <i class="fa fa-arrow-right"></i></a>
              </div>
            </div>
          </div>
          <div class="col-md-4 fade-up" style="transition-delay:.2s">
            <div class="news-card">
              <div class="news-img" style="background: linear-gradient(135deg,#1a3a6b,#0b1f3a)">
                <i class="fa fa-futbol"></i>
                <span class="news-cat">Students</span>
              </div>
              <div class="news-body">
                <div class="news-date"><i class="fa fa-calendar"></i> 5 February 2025</div>
                <h5>Football team advances to Schools Cup quarter-finals</h5>
                <p>The Adaxy Academy under-20 football team defeated Lilongwe Secondary 3-1 to advance to the national quarter-finals.</p>
                <a href="#" class="news-link">Read more <i class="fa fa-arrow-right"></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Parents -->
      <div class="tab-pane fade" id="tab-parents">
        <div class="row gy-4">
          <div class="col-md-4 fade-up">
            <div class="news-card">
              <div class="news-img" style="background: linear-gradient(135deg,#2d3b1a,#1a3a6b)">
                <i class="fa fa-people-group"></i>
                <span class="news-cat">Parents</span>
              </div>
              <div class="news-body">
                <div class="news-date"><i class="fa fa-calendar"></i> 25 February 2025</div>
                <h5>Parent–Teacher Conference: 28 March 2025</h5>
                <p>All parents are invited to the Term 1 Parent–Teacher Conference on 28 March at 14:00 in the Main Hall. Booking via the Parent Portal.</p>
                <a href="#" class="news-link">Read more <i class="fa fa-arrow-right"></i></a>
              </div>
            </div>
          </div>
          <div class="col-md-4 fade-up" style="transition-delay:.1s">
            <div class="news-card">
              <div class="news-img" style="background: linear-gradient(135deg,#2d3b1a,#1a3a6b)">
                <i class="fa fa-file-invoice"></i>
                <span class="news-cat">Parents</span>
              </div>
              <div class="news-body">
                <div class="news-date"><i class="fa fa-calendar"></i> 3 January 2025</div>
                <h5>2025 Fee Structure &amp; Payment Deadlines</h5>
                <p>The school fees structure for 2025 has been published. Parents may pay via bank transfer, mobile money, or at the school bursar's office.</p>
                <a href="#" class="news-link">Read more <i class="fa fa-arrow-right"></i></a>
              </div>
            </div>
          </div>
          <div class="col-md-4 fade-up" style="transition-delay:.2s">
            <div class="news-card">
              <div class="news-img" style="background: linear-gradient(135deg,#2d3b1a,#1a3a6b)">
                <i class="fa fa-shield-halved"></i>
                <span class="news-cat">Parents</span>
              </div>
              <div class="news-body">
                <div class="news-date"><i class="fa fa-calendar"></i> 15 January 2025</div>
                <h5>Updated School Safety Policy for 2025</h5>
                <p>Our revised child safeguarding and school safety policy is now available for download. Parents are asked to review and sign the acknowledgement form.</p>
                <a href="#" class="news-link">Read more <i class="fa fa-arrow-right"></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════  ENROL  ═══════════════════════ -->
<section id="enroll">
  <div class="container position-relative" style="z-index:1">
    <div class="section-hdr text-center fade-up">
      <div class="section-tag" style="color:var(--gold-light)">Admissions</div>
      <h2 class="section-title" style="color:var(--white)">Enrolment 2025</h2>
      <div class="divider-gold mx-auto"></div>
      <p class="section-desc mx-auto" style="color:rgba(255,255,255,.6)">Applications for the 2025 academic year are open. Choose your programme below and begin your journey with Adaxy Academy.</p>
    </div>

    <div class="row gy-4 justify-content-center">
      <!-- JCE -->
      <div class="col-lg-5 fade-up">
        <div class="enroll-card">
          <div class="enroll-icon"><i class="fa fa-book"></i></div>
          <h3>JCE Programme</h3>
          <p class="pill-tag mb-3" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.15);color:rgba(255,255,255,.6)">Form 1 – Form 4 · Ages 13–16</p>
          <p>Our Junior Certificate of Education programme provides a strong academic foundation across core and elective subjects, preparing students for MSCE and beyond.</p>
          <ul class="enroll-req">
            <li><i class="fa fa-check-circle"></i> PSLCE Certificate (minimum Grade B)</li>
            <li><i class="fa fa-check-circle"></i> Birth Certificate copy</li>
            <li><i class="fa fa-check-circle"></i> 2 recent passport photographs</li>
            <li><i class="fa fa-check-circle"></i> Previous school report (last 2 years)</li>
            <li><i class="fa fa-check-circle"></i> Non-refundable application fee: MWK 5,000</li>
          </ul>
          <a href="#" class="btn-enroll-card">
            <i class="fa fa-pen-to-square"></i> Apply for JCE
          </a>
        </div>
      </div>

      <!-- MSCE -->
      <div class="col-lg-5 fade-up" style="transition-delay:.15s">
        <div class="enroll-card">
          <div class="enroll-icon"><i class="fa fa-graduation-cap"></i></div>
          <h3>MSCE Programme</h3>
          <p class="pill-tag mb-3" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.15);color:rgba(255,255,255,.6)">Form 5 – Form 6 · Ages 17–19</p>
          <p>Our Malawi School Certificate of Education programme offers a broad range of advanced subjects, with pathways into university, technical colleges, and professional training.</p>
          <ul class="enroll-req">
            <li><i class="fa fa-check-circle"></i> JCE Certificate (minimum Grade C)</li>
            <li><i class="fa fa-check-circle"></i> National ID or Birth Certificate</li>
            <li><i class="fa fa-check-circle"></i> 2 recent passport photographs</li>
            <li><i class="fa fa-check-circle"></i> Recommendation letter from previous school</li>
            <li><i class="fa fa-check-circle"></i> Non-refundable application fee: MWK 7,500</li>
          </ul>
          <a href="#" class="btn-enroll-card">
            <i class="fa fa-pen-to-square"></i> Apply for MSCE
          </a>
        </div>
      </div>
    </div>

    <!-- Info strip -->
    <div class="row mt-5 gy-3">
      <div class="col-md-4 fade-up">
        <div class="d-flex align-items-center gap-3" style="color:rgba(255,255,255,.65);font-size:14px">
          <i class="fa fa-calendar-check" style="color:var(--gold);font-size:22px;flex-shrink:0"></i>
          <div><strong style="color:var(--white)">Application Deadline</strong><br>31 March 2025 (JCE) · 15 March 2025 (MSCE)</div>
        </div>
      </div>
      <div class="col-md-4 fade-up" style="transition-delay:.1s">
        <div class="d-flex align-items-center gap-3" style="color:rgba(255,255,255,.65);font-size:14px">
          <i class="fa fa-download" style="color:var(--gold);font-size:22px;flex-shrink:0"></i>
          <div><strong style="color:var(--white)">Download Forms</strong><br><a href="#" style="color:var(--gold-light)">Application Form PDF</a> available on our website</div>
        </div>
      </div>
      <div class="col-md-4 fade-up" style="transition-delay:.2s">
        <div class="d-flex align-items-center gap-3" style="color:rgba(255,255,255,.65);font-size:14px">
          <i class="fa fa-phone" style="color:var(--gold);font-size:22px;flex-shrink:0"></i>
          <div><strong style="color:var(--white)">Contact Admissions</strong><br>+265 (0)1 234 567 · admissions@Adaxyacademy.mw</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════  PORTALS  ═══════════════════════ -->
<section id="portals">
  <div class="container">
    <div class="section-hdr text-center fade-up">
      <div class="section-tag">Digital Access</div>
      <h2 class="section-title">Online Portals</h2>
      <div class="divider-gold mx-auto"></div>
      <p class="section-desc mx-auto">Access results, timetables, notices, and school resources through our dedicated digital portals.</p>
    </div>

    <div class="row gy-4">
      <div class="col-lg-6 fade-up">
        <div class="portal-card portal-student">
          <div class="portal-card-icon"><i class="fa fa-user-graduate"></i></div>
          <h3>Student Portal</h3>
          <p>Your personalised gateway to academic progress, timetables, assignments, and school announcements.</p>
          <ul class="portal-features">
            <li><i class="fa fa-check-circle"></i> View exam results &amp; report cards</li>
            <li><i class="fa fa-check-circle"></i> Download class timetables</li>
            <li><i class="fa fa-check-circle"></i> Submit assignments online</li>
            <li><i class="fa fa-check-circle"></i> Access library resources</li>
            <li><i class="fa fa-check-circle"></i> Check fee balances</li>
          </ul>
          <a href="Auth/login.php?role=student" class="btn-portal btn-portal-student">
            <i class="fa fa-arrow-right-to-bracket"></i> Student Login
          </a>
        </div>
      </div>
      <div class="col-lg-6 fade-up" style="transition-delay:.15s">
        <div class="portal-card portal-teacher">
          <div class="portal-card-icon"><i class="fa fa-chalkboard-teacher"></i></div>
          <h3>Teacher Portal</h3>
          <p>A comprehensive staff platform for managing classes, marks, lesson plans, and school communication.</p>
          <ul class="portal-features">
            <li><i class="fa fa-check-circle"></i> Enter &amp; manage student grades</li>
            <li><i class="fa fa-check-circle"></i> Post assignments &amp; notices</li>
            <li><i class="fa fa-check-circle"></i> Track attendance records</li>
            <li><i class="fa fa-check-circle"></i> Access staff resource library</li>
            <li><i class="fa fa-check-circle"></i> Communicate with parents</li>
          </ul>
          <a href="Auth/login.php?role=teacher" class="btn-portal btn-portal-teacher">
            <i class="fa fa-arrow-right-to-bracket"></i> Staff Login
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════  IMPORTANT LINKS  ═══════════════════════ -->
<section id="links">
  <div class="container">
    <div class="section-hdr fade-up">
      <div class="section-tag">Resources</div>
      <h2 class="section-title">Important Links</h2>
      <div class="divider-gold"></div>
    </div>

    <div class="row gy-4 mb-5">
      <div class="col-md-4 fade-up">
        <a href="#faqs" class="link-card">
          <div class="link-card-icon"><i class="fa fa-question-circle"></i></div>
          <div>
            <h5>FAQs</h5>
            <p>Answers to the most commonly asked questions from students, parents, and prospective families.</p>
            <span class="link-arrow">Browse FAQs <i class="fa fa-arrow-right"></i></span>
          </div>
        </a>
      </div>
      <div class="col-md-4 fade-up" style="transition-delay:.1s">
        <a href="#jobs" class="link-card">
          <div class="link-card-icon"><i class="fa fa-briefcase"></i></div>
          <div>
            <h5>Job Vacancies</h5>
            <p>Join the Adaxy team. View current vacancies for teaching and non-teaching positions.</p>
            <span class="link-arrow">See Openings <i class="fa fa-arrow-right"></i></span>
          </div>
        </a>
      </div>
      <div class="col-md-4 fade-up" style="transition-delay:.2s">
        <a href="#" class="link-card">
          <div class="link-card-icon"><i class="fa fa-calendar-alt"></i></div>
          <div>
            <h5>Academic Calendar</h5>
            <p>Download the full 2025 academic calendar including term dates, exams, and public holidays.</p>
            <span class="link-arrow">Download PDF <i class="fa fa-arrow-right"></i></span>
          </div>
        </a>
      </div>
      <div class="col-md-4 fade-up" style="transition-delay:.05s">
        <a href="#" class="link-card">
          <div class="link-card-icon"><i class="fa fa-book-open-reader"></i></div>
          <div>
            <h5>MANEB Resources</h5>
            <p>Past examination papers, MSCE syllabi, and official MANEB circulars for students and teachers.</p>
            <span class="link-arrow">Access Resources <i class="fa fa-arrow-right"></i></span>
          </div>
        </a>
      </div>
      <div class="col-md-4 fade-up" style="transition-delay:.15s">
        <a href="#" class="link-card">
          <div class="link-card-icon"><i class="fa fa-download"></i></div>
          <div>
            <h5>Downloadable Forms</h5>
            <p>Application forms, consent forms, fee schedules, and official school documents all in one place.</p>
            <span class="link-arrow">Download Forms <i class="fa fa-arrow-right"></i></span>
          </div>
        </a>
      </div>
      <div class="col-md-4 fade-up" style="transition-delay:.25s">
        <a href="#" class="link-card">
          <div class="link-card-icon"><i class="fa fa-phone-volume"></i></div>
          <div>
            <h5>Contact Directory</h5>
            <p>Departmental phone numbers and email addresses for all key school offices and staff.</p>
            <span class="link-arrow">View Directory <i class="fa fa-arrow-right"></i></span>
          </div>
        </a>
      </div>
    </div>

    <!-- FAQs -->
    <div id="faqs" class="row gy-5">
      <div class="col-lg-6 fade-up">
        <div class="section-tag">Help Centre</div>
        <h3 class="section-title" style="font-size:clamp(22px,3vw,32px)">Frequently Asked Questions</h3>
        <div class="divider-gold"></div>
        <div class="accordion faq-accordion" id="faqAccordion">
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#faq1">
                What are the admission requirements for Form 1 (JCE)?
              </button>
            </h2>
            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                Applicants must present a PSLCE Certificate with a minimum Grade B, birth certificate, 2 passport photographs, and previous school reports. An application fee of MWK 5,000 applies.
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq2">
                What is the fee structure for 2025?
              </button>
            </h2>
            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                Annual tuition fees for 2025 range from MWK 450,000 (JCE) to MWK 600,000 (MSCE). Day scholars and boarders have separate fee schedules available from the bursar's office. Instalment plans are available.
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq3">
                Does the school offer bursaries or scholarships?
              </button>
            </h2>
            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                Yes. Adaxy Academy offers merit-based scholarships for students with exceptional PSLCE or JCE results, and need-based bursaries for financially disadvantaged families. Contact the admissions office for details.
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq4">
                How do I access the Student Portal?
              </button>
            </h2>
            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                Each enrolled student receives a unique login credential during registration. Access the portal via the "Student Portal" link in the navigation. For password resets, contact the ICT office at ict@Adaxyacademy.mw.
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq5">
                What co-curricular activities are available?
              </button>
            </h2>
            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                We offer football, netball, chess, debate club, science club, choir, drama, community service, and environmental club. All students are encouraged to participate in at least one activity.
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Job Vacancies -->
      <div id="jobs" class="col-lg-5 offset-lg-1 fade-up" style="transition-delay:.15s">
        <div class="section-tag">Careers</div>
        <h3 class="section-title" style="font-size:clamp(22px,3vw,32px)">Job Vacancies</h3>
        <div class="divider-gold"></div>
        <p class="mb-4" style="color:var(--muted);font-size:14px">We are currently looking for talented, passionate individuals to join the Adaxy Academy family.</p>

        <div class="job-item">
          <div class="job-icon"><i class="fa fa-atom"></i></div>
          <div>
            <div class="job-title">Physics Teacher (MSCE)</div>
            <div class="job-dept">Science Department · Full-time</div>
          </div>
          <span class="job-badge open">Open</span>
        </div>
        <div class="job-item">
          <div class="job-icon"><i class="fa fa-calculator"></i></div>
          <div>
            <div class="job-title">Mathematics Teacher</div>
            <div class="job-dept">Mathematics Department · Full-time</div>
          </div>
          <span class="job-badge open">Open</span>
        </div>
        <div class="job-item">
          <div class="job-icon"><i class="fa fa-laptop-code"></i></div>
          <div>
            <div class="job-title">ICT Technician</div>
            <div class="job-dept">ICT Department · Full-time</div>
          </div>
          <span class="job-badge open">Open</span>
        </div>
        <div class="job-item">
          <div class="job-icon"><i class="fa fa-language"></i></div>
          <div>
            <div class="job-title">English Language Teacher</div>
            <div class="job-dept">Languages · Contract</div>
          </div>
          <span class="job-badge open">Open</span>
        </div>
        <div class="job-item">
          <div class="job-icon"><i class="fa fa-user-nurse"></i></div>
          <div>
            <div class="job-title">School Nurse</div>
            <div class="job-dept">Health Unit · Part-time</div>
          </div>
          <span class="job-badge" style="background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.2);color:#dc2626">Closed</span>
        </div>

        <div class="mt-4">
          <a href="mailto:hr@Adaxyacademy.mw" class="btn-enroll d-inline-flex align-items-center gap-2">
            <i class="fa fa-paper-plane"></i> Send Application
          </a>
          <p class="mt-3" style="font-size:12.5px;color:var(--muted)">Send CV &amp; cover letter to <strong>hr@Adaxyacademy.mw</strong></p>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ═══════════════════════  FOOTER  ═══════════════════════ -->
<footer>
  <div class="container">
    <div class="row gy-5">
      <!-- Brand -->
      <div class="col-lg-4">
        <div class="footer-logo">
          <div class="brand-emblem">
            <i class="fa fa-graduation-cap"></i>
          </div>
          <div>
            <div class="footer-brand">Adaxy Academy</div>
            <div style="font-size:11px;color:rgba(255,255,255,.35);letter-spacing:.08em">EST. 1985 · LILONGWE, MALAWI</div>
          </div>
        </div>
        <p class="footer-tagline">Empowering the next generation through academic excellence, integrity, and community values. Nationally recognised, proudly Malawian.</p>
        <div class="footer-social">
          <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-btn"><i class="fab fa-x-twitter"></i></a>
          <a href="#" class="social-btn"><i class="fab fa-instagram"></i></a>
          <a href="#" class="social-btn"><i class="fab fa-youtube"></i></a>
          <a href="#" class="social-btn"><i class="fab fa-whatsapp"></i></a>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-6 col-md-3 col-lg-2">
        <div class="footer-heading">Quick Links</div>
        <ul class="footer-links">
          <li><a href="#"><i class="fa fa-chevron-right"></i> Home</a></li>
          <li><a href="#about"><i class="fa fa-chevron-right"></i> About</a></li>
          <li><a href="#news"><i class="fa fa-chevron-right"></i> News</a></li>
          <li><a href="#enroll"><i class="fa fa-chevron-right"></i> Enrol</a></li>
          <li><a href="#portals"><i class="fa fa-chevron-right"></i> Portals</a></li>
          <li><a href="#links"><i class="fa fa-chevron-right"></i> Resources</a></li>
        </ul>
      </div>

      <!-- Academic -->
      <div class="col-6 col-md-3 col-lg-2">
        <div class="footer-heading">Academic</div>
        <ul class="footer-links">
          <li><a href="#"><i class="fa fa-chevron-right"></i> JCE Programme</a></li>
          <li><a href="#"><i class="fa fa-chevron-right"></i> MSCE Programme</a></li>
          <li><a href="#"><i class="fa fa-chevron-right"></i> Past Papers</a></li>
          <li><a href="#"><i class="fa fa-chevron-right"></i> Academic Calendar</a></li>
          <li><a href="#"><i class="fa fa-chevron-right"></i> MANEB Portal</a></li>
          <li><a href="#"><i class="fa fa-chevron-right"></i> School Rules</a></li>
        </ul>
      </div>

      <!-- Contact -->
      <div class="col-md-6 col-lg-4">
        <div class="footer-heading">Contact Us</div>
        <div class="footer-contact">
          <p><i class="fa fa-map-marker-alt"></i> P.O. Box 1204, Area 47, Lilongwe, Malawi</p>
          <p><i class="fa fa-phone"></i> +265 (0)1 234 567 / +265 (0)99 123 4567</p>
          <p><i class="fa fa-envelope"></i> info@Adaxyacademy.mw</p>
          <p><i class="fa fa-clock"></i> Mon – Fri: 07:30 – 17:00<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sat: 08:00 – 12:00</p>
        </div>
        <div class="mt-3">
          <a href="#enroll" class="btn-enroll d-inline-flex align-items-center gap-2 mt-1">
            <i class="fa fa-pen-to-square"></i> Apply Now
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="container">
      <span>© 2025 Adaxy Academy, Lilongwe, Malawi. All rights reserved.</span>
      &nbsp;&nbsp;·&nbsp;&nbsp;
      <a href="#">Privacy Policy</a>
      &nbsp;&nbsp;·&nbsp;&nbsp;
      <a href="#">Terms of Use</a>
      &nbsp;&nbsp;·&nbsp;&nbsp;
      <a href="#">Sitemap</a>
    </div>
  </div>
</footer>

<!-- ═══════════════════════  SCRIPTS  ═══════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // ── Smooth scroll for dropdown links ──
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const id = a.getAttribute('href');
      if (id && id !== '#') {
        const el = document.querySelector(id);
        if (el) { e.preventDefault(); el.scrollIntoView({ behavior: 'smooth' }); }
      }
    });
  });

  // ── Intersection Observer for fade-up ──
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(en => { if (en.isIntersecting) { en.target.classList.add('visible'); obs.unobserve(en.target); } });
  }, { threshold: 0.12 });
  document.querySelectorAll('.fade-up').forEach(el => obs.observe(el));

  // ── Active navbar link on scroll ──
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.navbar .nav-link');
  window.addEventListener('scroll', () => {
    let cur = '';
    sections.forEach(s => { if (window.scrollY >= s.offsetTop - 100) cur = s.id; });
    navLinks.forEach(l => {
      l.classList.remove('active');
      if (l.getAttribute('href') === '#' + cur || (cur === 'home' && l.getAttribute('href') === '#')) l.classList.add('active');
    });
  });
</script>
</body>
</html>