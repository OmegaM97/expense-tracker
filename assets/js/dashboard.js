document.addEventListener('DOMContentLoaded', function() {
    initializeDashboardCharts();
    initializeDashboardModal();
    initializeSidebarToggle();
});

function initializeSidebarToggle() {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.app-sidebar');
    const mainContent = document.querySelector('.main-content');

    if (!toggle || !sidebar || !mainContent) {
        return;
    }

    toggle.addEventListener('click', function() {
        sidebar.classList.toggle('sidebar-collapsed');
        mainContent.classList.toggle('sidebar-collapsed');
    });
}

function initializeDashboardCharts() {
    const categoryData = window.expenseCategoryData || [];
    const monthlyData = window.monthlySpendingData || [];

    const categoryChartCanvas = document.getElementById('categoryChart');
    const monthlyChartCanvas = document.getElementById('monthlyChart');

    if (categoryChartCanvas && categoryData.length) {
        new Chart(categoryChartCanvas, {
            type: 'doughnut',
            data: {
                labels: categoryData.map(item => item.name),
                datasets: [{
                    data: categoryData.map(item => item.total),
                    backgroundColor: ['#fb923c', '#f97316', '#fdba74', '#fb8500', '#ffedd5'],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#334155',
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                maintainAspectRatio: false,
                cutout: '60%'
            }
        });
    }

    if (monthlyChartCanvas && monthlyData.length) {
        new Chart(monthlyChartCanvas, {
            type: 'bar',
            data: {
                labels: monthlyData.map(item => item.month),
                datasets: [{
                    label: 'Spending',
                    data: monthlyData.map(item => item.expense),
                    backgroundColor: monthlyData.map(item => item.expense > 0 ? '#f97316' : '#fbbf24'),
                    borderRadius: 12,
                    maxBarThickness: 40
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#475569'
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        ticks: {
                            color: '#475569',
                            callback: function(value) {
                                return '$' + value;
                            }
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.15)'
                        }
                    }
                },
                maintainAspectRatio: false
            }
        });
    }
}

function initializeDashboardModal() {
    const fab = document.getElementById('dashboardFab');
    const modal = document.getElementById('quickAddModal');
    const closeButton = document.getElementById('modalCloseButton');
    const modalOverlay = document.getElementById('modalOverlay');

    if (!fab || !modal || !closeButton || !modalOverlay) {
        return;
    }

    fab.addEventListener('click', function() {
        modal.classList.add('modal-open');
        modalOverlay.classList.add('overlay-visible');
        document.body.style.overflow = 'hidden';
    });

    [closeButton, modalOverlay].forEach(element => {
        element.addEventListener('click', function() {
            modal.classList.remove('modal-open');
            modalOverlay.classList.remove('overlay-visible');
            document.body.style.overflow = '';
        });
    });

    const quickAddForm = document.getElementById('quickAddForm');
    if (quickAddForm) {
        quickAddForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const button = quickAddForm.querySelector('button[type="submit"]');
            button.textContent = 'Adding...';
            button.disabled = true;
            setTimeout(() => {
                button.textContent = 'Add Transaction';
                button.disabled = false;
                modal.classList.remove('modal-open');
                modalOverlay.classList.remove('overlay-visible');
                document.body.style.overflow = '';
            }, 800);
        });
    }
}