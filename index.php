<?php
require_once 'config/session.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['user_verified']) && $_SESSION['user_verified'] === true) {
    header('Location: dashboard/dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker - Track Your Finances Effortlessly</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">Expense Tracker</div>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#about">About</a></li>
            </ul>
            <a href="auth/login.php" class="btn btn-primary">Get Started</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1>Track Your Expenses Effortlessly</h1>
                <p>Manage your finances with our modern, secure expense tracking platform. Stay on top of your spending and achieve your financial goals.</p>
                <div class="hero-buttons">
                    <a href="#features" class="btn btn-secondary">View Features</a>
                    <a href="auth/register.php" class="btn btn-primary">Start Tracking</a>
                </div>
            </div>
            <div class="hero-illustration">
                <div class="floating-shape shape-1"></div>
                <div class="floating-shape shape-2"></div>
                <div class="floating-shape shape-3"></div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <h2>Powerful Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Smart Analytics</h3>
                    <p>Get detailed insights into your spending patterns with beautiful charts and reports.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Secure & Private</h3>
                    <p>Your financial data is encrypted and protected with bank-level security.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Cross-Platform</h3>
                    <p>Access your expense tracker from any device - desktop, tablet, or mobile.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">10K+</div>
                    <div class="stat-label">Active Users</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">50K+</div>
                    <div class="stat-label">Transactions Tracked</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">95%</div>
                    <div class="stat-label">User Satisfaction</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <h2>What Our Users Say</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p>"Expense Tracker has completely transformed how I manage my finances. The insights are invaluable."</p>
                    <div class="testimonial-author">- Sarah M.</div>
                </div>
                <div class="testimonial-card">
                    <p>"Clean interface, powerful features, and secure. Exactly what I needed for expense tracking."</p>
                    <div class="testimonial-author">- John D.</div>
                </div>
                <div class="testimonial-card">
                    <p>"The analytics help me understand my spending habits and save money. Highly recommended!"</p>
                    <div class="testimonial-author">- Emily R.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Ready to Take Control of Your Finances?</h2>
            <p>Join thousands of users who are already tracking their expenses smarter.</p>
            <a href="auth/register.php" class="btn btn-primary btn-large">Start Tracking Today</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">Expense Tracker</div>
                <p>Modern expense tracking made simple. Take control of your financial future.</p>
                <div class="footer-social">
                    <a href="#" class="social-icon">📘</a>
                    <a href="#" class="social-icon">🐦</a>
                    <a href="#" class="social-icon">📷</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Expense Tracker. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/app.js"></script>
</body>
</html>
