/**
 * OM Guarantee Dashboard - Grid Layout JavaScript
 * Handles animations and interactions for the grid-based dashboard
 */

(function() {
    'use strict';

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initOMGGridDashboard();
    });

    function initOMGGridDashboard() {
        // Initialize progress bar animations
        initProgressBars();
        
        // Initialize counter animations
        initCounterAnimations();
        
        // Initialize action button interactions
        initActionButtons();
        
        // Initialize responsive grid adjustments
        initResponsiveGrid();
    }

    /**
     * Initialize progress bar animations
     */
    function initProgressBars() {
        const progressBars = document.querySelectorAll('.omg-progress-fill');
        
        // Use Intersection Observer for performance
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        animateProgressBar(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.3,
                rootMargin: '0px 0px -50px 0px'
            });

            progressBars.forEach(function(bar) {
                observer.observe(bar);
            });
        } else {
            // Fallback for older browsers
            progressBars.forEach(function(bar) {
                animateProgressBar(bar);
            });
        }
    }

    /**
     * Animate individual progress bar
     */
    function animateProgressBar(progressBar) {
        const targetWidth = progressBar.getAttribute('data-width');
        if (!targetWidth) return;

        // Reset width
        progressBar.style.width = '0%';
        
        // Animate to target width
        setTimeout(function() {
            progressBar.style.width = targetWidth + '%';
        }, 200);
    }

    /**
     * Initialize counter animations
     */
    function initCounterAnimations() {
        const counters = document.querySelectorAll('.omg-metric__value');
        
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
     * Animate counter with counting effect
     */
    function animateCounter(element) {
        const text = element.textContent.trim();
        const hasPercent = text.includes('%');
        const hasDollar = text.includes('$');
        const hasComma = text.includes(',');
        
        // Extract number from text
        const numberMatch = text.match(/[\d,]+/);
        if (!numberMatch) return;
        
        const numberStr = numberMatch[0].replace(/,/g, '');
        const targetNumber = parseInt(numberStr, 10);
        
        if (isNaN(targetNumber)) return;

        const duration = 1500; // 1.5 seconds
        const steps = 50;
        const increment = targetNumber / steps;
        let current = 0;
        let step = 0;

        const timer = setInterval(function() {
            current += increment;
            step++;
            
            if (step >= steps) {
                current = targetNumber;
                clearInterval(timer);
            }
            
            let displayValue = Math.floor(current);
            
            // Format the number
            let displayText = displayValue.toString();
            if (hasComma && displayValue >= 1000) {
                displayText = displayValue.toLocaleString();
            }
            
            // Add prefixes/suffixes
            if (hasDollar) displayText = '$' + displayText;
            if (hasPercent) displayText = displayText + '%';
            
            element.textContent = displayText;
        }, duration / steps);
    }

    /**
     * Initialize action button interactions
     */
    function initActionButtons() {
        const actionButtons = document.querySelectorAll('.omg-action-btn');
        
        actionButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                handleActionButtonClick(e, button);
            });

            // Add keyboard support
            button.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    handleActionButtonClick(e, button);
                }
            });
        });
    }

    /**
     * Handle action button click
     */
    function handleActionButtonClick(e, button) {
        e.preventDefault();
        
        // Add click animation
        button.style.transform = 'scale(0.95)';
        setTimeout(function() {
            button.style.transform = '';
        }, 150);
        
        // Handle blockchain verification
        if (button.textContent.includes('BLOCKCHAIN')) {
            handleBlockchainVerification(button);
        }
    }

    /**
     * Handle blockchain verification
     */
    function handleBlockchainVerification(button) {
        const originalText = button.innerHTML;
        
        // Show loading state
        button.innerHTML = '<span class="omg-action-btn__text">VERIFYING...</span>';
        button.disabled = true;
        button.style.opacity = '0.7';
        
        // Simulate verification process
        setTimeout(function() {
            button.innerHTML = '<span class="omg-action-btn__text">✓ VERIFIED</span>';
            button.style.background = '#28a745';
            button.style.color = 'white';
            
            // Reset after delay
            setTimeout(function() {
                button.innerHTML = originalText;
                button.disabled = false;
                button.style.opacity = '';
                button.style.background = '';
                button.style.color = '';
            }, 2000);
        }, 1500);
    }

    /**
     * Initialize responsive grid adjustments
     */
    function initResponsiveGrid() {
        let resizeTimer;
        
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                adjustGridForScreenSize();
            }, 250);
        });
        
        // Initial adjustment
        adjustGridForScreenSize();
    }

    /**
     * Adjust grid layout based on screen size
     */
    function adjustGridForScreenSize() {
        const dashboards = document.querySelectorAll('.omg-dashboard');
        const width = window.innerWidth;
        
        dashboards.forEach(function(dashboard) {
            // Add responsive classes
            dashboard.classList.remove('omg-dashboard--mobile', 'omg-dashboard--tablet');
            
            if (width <= 480) {
                dashboard.classList.add('omg-dashboard--mobile');
            } else if (width <= 768) {
                dashboard.classList.add('omg-dashboard--tablet');
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
     * Update dashboard data dynamically
     */
    function updateDashboardData(data) {
        // Update metric values
        if (data.totalImpact !== undefined) {
            updateMetricValue('Total Impact', data.totalImpact + '%');
        }
        
        if (data.totalDonated !== undefined) {
            updateMetricValue('Total Donated', '$' + formatNumber(data.totalDonated));
        }
        
        if (data.blockchainTransactions !== undefined) {
            updateMetricValue('Blockchain Transactions', formatNumber(data.blockchainTransactions));
        }
        
        // Update progress bars
        if (data.productImpacts && Array.isArray(data.productImpacts)) {
            updateProgressBars(data.productImpacts);
        }
        
        // Update charity list
        if (data.topCharities && Array.isArray(data.topCharities)) {
            updateCharityList(data.topCharities);
        }
        
        // Re-animate counters
        initCounterAnimations();
    }

    /**
     * Update metric value by label
     */
    function updateMetricValue(label, value) {
        const metrics = document.querySelectorAll('.omg-metric');
        metrics.forEach(function(metric) {
            const labelElement = metric.querySelector('.omg-metric__label');
            if (labelElement && labelElement.textContent.includes(label)) {
                const valueElement = metric.querySelector('.omg-metric__value');
                if (valueElement) {
                    valueElement.textContent = value;
                }
            }
        });
    }

    /**
     * Update progress bars with new data
     */
    function updateProgressBars(productImpacts) {
        const progressItems = document.querySelectorAll('.omg-progress-item');
        
        productImpacts.forEach(function(product, index) {
            if (progressItems[index]) {
                const item = progressItems[index];
                const label = item.querySelector('.omg-progress-label');
                const value = item.querySelector('.omg-progress-value');
                const fill = item.querySelector('.omg-progress-fill');
                
                if (label) label.textContent = product.name;
                if (value) value.textContent = product.percentage + '%';
                if (fill) {
                    fill.setAttribute('data-width', product.percentage);
                    animateProgressBar(fill);
                }
            }
        });
    }

    /**
     * Update charity list with new data
     */
    function updateCharityList(charities) {
        const charityList = document.querySelector('.omg-charity-list');
        if (!charityList) return;
        
        charityList.innerHTML = '';
        
        charities.forEach(function(charity) {
            const item = document.createElement('div');
            item.className = 'omg-charity-item';
            item.innerHTML = `
                <span class="omg-charity-name">${escapeHtml(charity.name)}</span>
                <span class="omg-charity-amount">$${formatNumber(charity.amount)}</span>
            `;
            charityList.appendChild(item);
        });
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Add loading state to dashboard
     */
    function addLoadingState() {
        const dashboards = document.querySelectorAll('.omg-dashboard');
        dashboards.forEach(function(dashboard) {
            dashboard.classList.add('omg-dashboard--loading');
        });
    }

    /**
     * Remove loading state from dashboard
     */
    function removeLoadingState() {
        const dashboards = document.querySelectorAll('.omg-dashboard');
        dashboards.forEach(function(dashboard) {
            dashboard.classList.remove('omg-dashboard--loading');
        });
    }

    // Expose public API
    window.OMGGridDashboard = {
        updateData: updateDashboardData,
        addLoadingState: addLoadingState,
        removeLoadingState: removeLoadingState,
        formatNumber: formatNumber,
        animateProgressBar: animateProgressBar,
        animateCounter: animateCounter
    };

    // Auto-initialize progress bars on page load
    setTimeout(function() {
        const progressBars = document.querySelectorAll('.omg-progress-fill[data-width]');
        progressBars.forEach(function(bar) {
            animateProgressBar(bar);
        });
    }, 500);

})();

