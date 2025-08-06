/**
 * OM Guarantee Dashboard JavaScript
 * Minimal JS for enhanced functionality
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        initOMGDashboard();
    });

    function initOMGDashboard() {
        // Initialize progress bar animations
        animateProgressBars();
        
        // Initialize counter animations
        animateCounters();
        
        // Initialize action button interactions
        initActionButtons();
        
        // Initialize responsive behavior
        initResponsiveBehavior();
    }

    /**
     * Animate progress bars on scroll into view
     */
    function animateProgressBars() {
        const progressBars = document.querySelectorAll('.omg-dashboard__progress-fill');
        
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const progressBar = entry.target;
                        const width = progressBar.style.width;
                        
                        // Reset and animate
                        progressBar.style.width = '0%';
                        setTimeout(function() {
                            progressBar.style.width = width;
                        }, 100);
                        
                        observer.unobserve(progressBar);
                    }
                });
            }, {
                threshold: 0.5
            });

            progressBars.forEach(function(bar) {
                observer.observe(bar);
            });
        }
    }

    /**
     * Animate counters with counting effect
     */
    function animateCounters() {
        const counters = document.querySelectorAll('.omg-dashboard__metric-value');
        
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.5
            });

            counters.forEach(function(counter) {
                observer.observe(counter);
            });
        }
    }

    /**
     * Animate individual counter
     */
    function animateCounter(element) {
        const text = element.textContent;
        const hasPercent = text.includes('%');
        const hasDollar = text.includes('$');
        const hasComma = text.includes(',');
        
        // Extract number from text
        const number = parseFloat(text.replace(/[^0-9.]/g, ''));
        
        if (isNaN(number)) return;
        
        const duration = 2000; // 2 seconds
        const steps = 60;
        const increment = number / steps;
        let current = 0;
        let step = 0;

        const timer = setInterval(function() {
            current += increment;
            step++;
            
            if (step >= steps) {
                current = number;
                clearInterval(timer);
            }
            
            let displayValue = Math.floor(current);
            
            // Format the number
            if (hasComma && displayValue >= 1000) {
                displayValue = displayValue.toLocaleString();
            }
            
            // Add prefixes/suffixes
            let displayText = displayValue.toString();
            if (hasDollar) displayText = '$' + displayText;
            if (hasPercent) displayText = displayText + '%';
            
            element.textContent = displayText;
        }, duration / steps);
    }

    /**
     * Initialize action button interactions
     */
    function initActionButtons() {
        const actionButtons = document.querySelectorAll('.omg-dashboard__action-btn');
        
        actionButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                // Add click animation
                button.style.transform = 'scale(0.95)';
                setTimeout(function() {
                    button.style.transform = '';
                }, 150);
                
                // Handle blockchain verification
                if (button.textContent.includes('BLOCKCHAIN')) {
                    handleBlockchainVerification(e);
                }
            });
        });
    }

    /**
     * Handle blockchain verification button click
     */
    function handleBlockchainVerification(e) {
        e.preventDefault();
        
        // Show loading state
        const button = e.target;
        const originalText = button.innerHTML;
        button.innerHTML = 'VERIFYING...';
        button.disabled = true;
        
        // Simulate verification process
        setTimeout(function() {
            button.innerHTML = '✓ VERIFIED';
            button.style.background = '#28a745';
            
            setTimeout(function() {
                button.innerHTML = originalText;
                button.disabled = false;
                button.style.background = '';
            }, 2000);
        }, 1500);
    }

    /**
     * Initialize responsive behavior
     */
    function initResponsiveBehavior() {
        let resizeTimer;
        
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                adjustLayoutForScreenSize();
            }, 250);
        });
        
        // Initial adjustment
        adjustLayoutForScreenSize();
    }

    /**
     * Adjust layout based on screen size
     */
    function adjustLayoutForScreenSize() {
        const dashboard = document.querySelector('.omg-dashboard');
        if (!dashboard) return;
        
        const width = window.innerWidth;
        
        // Add responsive classes
        dashboard.classList.remove('omg-dashboard--mobile', 'omg-dashboard--tablet');
        
        if (width <= 480) {
            dashboard.classList.add('omg-dashboard--mobile');
        } else if (width <= 768) {
            dashboard.classList.add('omg-dashboard--tablet');
        }
        
        // Adjust progress bar labels on mobile
        const progressItems = document.querySelectorAll('.omg-dashboard__progress-item');
        progressItems.forEach(function(item) {
            const info = item.querySelector('.omg-dashboard__progress-info');
            if (width <= 768) {
                info.style.flexDirection = 'column';
                info.style.alignItems = 'flex-start';
            } else {
                info.style.flexDirection = 'row';
                info.style.alignItems = 'center';
            }
        });
    }

    /**
     * Utility function to format numbers
     */
    function formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    }

    /**
     * Add loading state to cards
     */
    function addLoadingState(card) {
        card.classList.add('omg-dashboard__card--loading');
    }

    /**
     * Remove loading state from cards
     */
    function removeLoadingState(card) {
        card.classList.remove('omg-dashboard__card--loading');
    }

    /**
     * Update dashboard data (for dynamic content)
     */
    function updateDashboardData(data) {
        // Update metrics
        if (data.totalImpact) {
            const impactElement = document.querySelector('[data-metric="total-impact"]');
            if (impactElement) {
                impactElement.textContent = data.totalImpact + '%';
            }
        }
        
        if (data.totalDonated) {
            const donatedElement = document.querySelector('[data-metric="total-donated"]');
            if (donatedElement) {
                donatedElement.textContent = '$' + formatNumber(data.totalDonated);
            }
        }
        
        if (data.blockchainTransactions) {
            const transactionsElement = document.querySelector('[data-metric="blockchain-transactions"]');
            if (transactionsElement) {
                transactionsElement.textContent = formatNumber(data.blockchainTransactions);
            }
        }
        
        // Update progress bars
        if (data.productImpacts) {
            data.productImpacts.forEach(function(product, index) {
                const progressBar = document.querySelector(`[data-product-index="${index}"] .omg-dashboard__progress-fill`);
                const progressValue = document.querySelector(`[data-product-index="${index}"] .omg-dashboard__progress-value`);
                
                if (progressBar && progressValue) {
                    progressBar.style.width = product.percentage + '%';
                    progressValue.textContent = product.percentage + '%';
                }
            });
        }
        
        // Re-animate counters
        animateCounters();
    }

    // Expose public API
    window.OMGDashboard = {
        updateData: updateDashboardData,
        addLoadingState: addLoadingState,
        removeLoadingState: removeLoadingState,
        formatNumber: formatNumber
    };

})();

