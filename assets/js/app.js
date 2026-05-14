// Expense Tracker Application JavaScript

document.addEventListener('DOMContentLoaded', function() {
    console.log('Expense Tracker App Loaded');

    // Initialize animations and interactions
    initializeAnimations();
    initializeScrollEffects();
    initializeAuthFeatures();
});

function initializeAnimations() {
    // Add fade-in animation to sections on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
            }
        });
    }, observerOptions);

    // Observe all sections
    const sections = document.querySelectorAll('section');
    sections.forEach(section => {
        observer.observe(section);
    });

    // Add hover effects to feature cards
    const featureCards = document.querySelectorAll('.feature-card');
    featureCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Smooth scroll for navigation links
    const navLinks = document.querySelectorAll('.nav-links a');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            if (targetSection) {
                targetSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Animate stats counters
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => {
        const target = parseInt(stat.textContent.replace(/[^\d]/g, ''));
        if (!isNaN(target)) {
            animateCounter(stat, 0, target, 2000);
        }
    });
}

function initializeScrollEffects() {
    // Navbar background change on scroll
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = 'none';
            }
        });
    }
}

function initializeAuthFeatures() {
    // Password visibility toggle
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        const wrapper = input.closest('.input-wrapper');
        if (wrapper) {
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'password-toggle';
            toggleBtn.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                </svg>
            `;
            toggleBtn.setAttribute('aria-label', 'Toggle password visibility');

            toggleBtn.addEventListener('click', function() {
                const isVisible = input.type === 'text';
                input.type = isVisible ? 'password' : 'text';
                this.innerHTML = isVisible ? `
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                    </svg>
                ` : `
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.99902 3L20.999 21M9.8433 9.91364C9.32066 10.4536 8.99902 11.1892 8.99902 12C8.99902 13.6569 10.3422 15 12 15C12.8215 15 13.5667 14.669 14.1086 14.133M6.49902 6.64715C4.59972 7.90034 3.15305 9.78394 2.45703 12C3.73128 16.0571 7.52159 19 12 19C13.9881 19 15.8414 18.4194 17.3988 17.4184M10.999 5.04939C11.328 5.01673 11.6617 5 11.999 5C16.4784 5 20.2687 7.94291 21.5429 12C21.2607 12.894 20.8577 13.7338 20.3522 14.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                `;
            });

            wrapper.appendChild(toggleBtn);
        }
    });

    // Form validation feedback
    const formInputs = document.querySelectorAll('.auth-form input');
    formInputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateInput(this);
        });

        input.addEventListener('input', function() {
            if (this.classList.contains('invalid')) {
                validateInput(this);
            }
        });
    });

    // Password confirmation matching
    const confirmPassword = document.getElementById('confirm_password');
    const password = document.getElementById('password');

    if (confirmPassword && password) {
        confirmPassword.addEventListener('input', function() {
            if (this.value !== password.value) {
                this.setCustomValidity('Passwords do not match');
                this.classList.add('invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('invalid');
            }
        });
    }

    // Form submission animation
    const authForm = document.querySelector('.auth-form');
    if (authForm) {
        authForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.auth-btn');
            if (submitBtn) {
                submitBtn.innerHTML = `
                    <div class="loading-spinner"></div>
                    <span>Processing...</span>
                `;
                submitBtn.disabled = true;
            }
        });
    }
}

function validateInput(input) {
    const wrapper = input.closest('.input-wrapper');
    const value = input.value.trim();

    // Remove existing validation classes
    wrapper.classList.remove('valid', 'invalid');

    // Basic validation
    if (input.hasAttribute('required') && !value) {
        wrapper.classList.add('invalid');
        return;
    }

    if (input.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            wrapper.classList.add('invalid');
            return;
        }
    }

    if (input.type === 'password' && value && value.length < 6) {
        wrapper.classList.add('invalid');
        return;
    }

    // If we get here, input is valid
    if (value) {
        wrapper.classList.add('valid');
    }
}

function animateCounter(element, start, end, duration) {
    const startTime = performance.now();

    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        const current = Math.floor(start + (end - start) * progress);
        element.textContent = current.toLocaleString() + (element.textContent.includes('%') ? '%' : '+');

        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        }
    }

    requestAnimationFrame(updateCounter);
}

// Utility function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Utility function to format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Fetch transactions (example API call)
async function fetchTransactions() {
    try {
        const response = await fetch('api/transactions.php');
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching transactions:', error);
    }
}

// Add a new transaction (example API call)
async function addTransaction(transaction) {
    try {
        const response = await fetch('api/transactions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(transaction)
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error adding transaction:', error);
    }
}
