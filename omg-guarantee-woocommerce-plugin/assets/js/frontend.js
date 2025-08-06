jQuery(document).ready(function($) {
    
    // Initialize OM Guarantee frontend functionality
    initOMGuarantee();
    
    function initOMGuarantee() {
        // Add smooth animations to badges
        animateBadges();
        
        // Handle impact calculations in cart
        handleCartImpact();
        
        // Add click tracking for blockchain links
        trackBlockchainClicks();
        
        // Initialize impact counters with animation
        animateCounters();
        
        // Handle responsive behavior
        handleResponsive();
    }
    
    function animateBadges() {
        // Fade in badges when they come into view
        if (typeof IntersectionObserver !== 'undefined') {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        $(entry.target).addClass('omg-visible');
                    }
                });
            }, {
                threshold: 0.1
            });
            
            $('.omg-certification-badge, .omg-impact-counter, .omg-blockchain-verification').each(function() {
                observer.observe(this);
            });
        }
        
        // Add CSS for animation
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .omg-certification-badge,
                .omg-impact-counter,
                .omg-blockchain-verification {
                    opacity: 0;
                    transform: translateY(20px);
                    transition: opacity 0.6s ease, transform 0.6s ease;
                }
                .omg-certification-badge.omg-visible,
                .omg-impact-counter.omg-visible,
                .omg-blockchain-verification.omg-visible {
                    opacity: 1;
                    transform: translateY(0);
                }
            `)
            .appendTo('head');
    }
    
    function handleCartImpact() {
        // Update impact display when cart changes
        $(document.body).on('updated_cart_totals', function() {
            updateImpactDisplay();
        });
        
        $(document.body).on('updated_checkout', function() {
            updateImpactDisplay();
        });
        
        // Handle quantity changes
        $(document).on('change', '.qty', function() {
            setTimeout(updateImpactDisplay, 500);
        });
    }
    
    function updateImpactDisplay() {
        // This would typically make an AJAX call to recalculate impact
        // For now, we'll just add a visual indicator that impact is being calculated
        $('.omg-cart-impact-details, .omg-checkout-impact-details').each(function() {
            var $this = $(this);
            $this.addClass('omg-updating');
            
            setTimeout(function() {
                $this.removeClass('omg-updating');
            }, 1000);
        });
        
        // Add CSS for updating state
        if (!$('#omg-updating-styles').length) {
            $('<style id="omg-updating-styles">')
                .prop('type', 'text/css')
                .html(`
                    .omg-updating {
                        opacity: 0.6;
                        transition: opacity 0.3s ease;
                    }
                `)
                .appendTo('head');
        }
    }
    
    function trackBlockchainClicks() {
        $(document).on('click', '.omg-blockchain-link', function(e) {
            var href = $(this).attr('href');
            
            // Track the click (you could send this to analytics)
            if (typeof gtag !== 'undefined') {
                gtag('event', 'blockchain_verification_click', {
                    'event_category': 'OM Guarantee',
                    'event_label': href
                });
            }
            
            // Add visual feedback
            $(this).addClass('omg-clicked');
            setTimeout(() => {
                $(this).removeClass('omg-clicked');
            }, 200);
        });
        
        // Add CSS for click feedback
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .omg-blockchain-link.omg-clicked {
                    transform: scale(0.95);
                    transition: transform 0.1s ease;
                }
            `)
            .appendTo('head');
    }
    
    function animateCounters() {
        $('.omg-stat-value').each(function() {
            var $this = $(this);
            var finalValue = parseFloat($this.text().replace(/[$,]/g, ''));
            
            if (!isNaN(finalValue) && finalValue > 0) {
                $this.prop('Counter', 0).animate({
                    Counter: finalValue
                }, {
                    duration: 2000,
                    easing: 'swing',
                    step: function(now) {
                        if ($this.text().includes('$')) {
                            $this.text('$' + Math.ceil(now).toLocaleString());
                        } else {
                            $this.text(Math.ceil(now).toLocaleString());
                        }
                    },
                    complete: function() {
                        if ($this.text().includes('$')) {
                            $this.text('$' + finalValue.toLocaleString());
                        } else {
                            $this.text(finalValue.toLocaleString());
                        }
                    }
                });
            }
        });
    }
    
    function handleResponsive() {
        // Adjust badge layout on window resize
        $(window).on('resize', function() {
            adjustBadgeLayout();
        });
        
        adjustBadgeLayout();
    }
    
    function adjustBadgeLayout() {
        var windowWidth = $(window).width();
        
        $('.omg-badge-container').each(function() {
            var $container = $(this);
            
            if (windowWidth < 768) {
                $container.addClass('omg-mobile-layout');
            } else {
                $container.removeClass('omg-mobile-layout');
            }
        });
        
        // Add mobile-specific styles
        if (!$('#omg-mobile-styles').length) {
            $('<style id="omg-mobile-styles">')
                .prop('type', 'text/css')
                .html(`
                    .omg-mobile-layout {
                        flex-direction: column !important;
                        text-align: center !important;
                    }
                    .omg-mobile-layout .omg-badge-content {
                        margin-top: 15px;
                    }
                `)
                .appendTo('head');
        }
    }
    
    // Product page impact calculator
    if ($('.single-product').length) {
        initProductImpactCalculator();
    }
    
    function initProductImpactCalculator() {
        var $quantityInput = $('.qty');
        var $priceElement = $('.price');
        
        if ($quantityInput.length && $priceElement.length) {
            $quantityInput.on('change input', function() {
                updateProductImpact();
            });
        }
    }
    
    function updateProductImpact() {
        // This would calculate and update the impact based on quantity
        // Implementation would depend on how the impact is displayed on product pages
        var quantity = parseInt($('.qty').val()) || 1;
        
        $('.omg-impact-text').each(function() {
            var $this = $(this);
            var originalText = $this.data('original-text') || $this.text();
            $this.data('original-text', originalText);
            
            // Extract the impact amount and multiply by quantity
            var impactMatch = originalText.match(/\$([0-9.]+)/);
            if (impactMatch) {
                var baseImpact = parseFloat(impactMatch[1]);
                var newImpact = (baseImpact * quantity).toFixed(2);
                var newText = originalText.replace(/\$[0-9.]+/, '$' + newImpact);
                $this.text(newText);
            }
        });
    }
    
    // Thank you page animations
    if ($('.omg-thankyou-impact').length) {
        setTimeout(function() {
            $('.omg-thankyou-impact').addClass('omg-animate-in');
        }, 500);
        
        // Add animation styles
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .omg-thankyou-impact {
                    transform: scale(0.9);
                    opacity: 0;
                    transition: transform 0.6s ease, opacity 0.6s ease;
                }
                .omg-thankyou-impact.omg-animate-in {
                    transform: scale(1);
                    opacity: 1;
                }
            `)
            .appendTo('head');
    }
    
    // Accessibility improvements
    function improveAccessibility() {
        // Add ARIA labels to important elements
        $('.omg-blockchain-link').attr('aria-label', 'View transaction on blockchain explorer (opens in new tab)');
        $('.omg-certification-badge').attr('role', 'region').attr('aria-label', 'OM Guarantee certification information');
        $('.omg-impact-counter').attr('role', 'region').attr('aria-label', 'Social impact statistics');
        
        // Add keyboard navigation for interactive elements
        $('.omg-blockchain-link').on('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    }
    
    improveAccessibility();
    
    // Error handling for missing elements
    function handleMissingElements() {
        // Check if required elements are present and show fallback if needed
        if ($('.omg-certification-badge').length === 0 && $('.omg-badge-placeholder').length > 0) {
            $('.omg-badge-placeholder').html('<p>OM Guarantee certification loading...</p>');
        }
    }
    
    handleMissingElements();
    
    // Performance optimization - lazy load heavy elements
    function lazyLoadElements() {
        $('.omg-blockchain-verification').each(function() {
            var $this = $(this);
            
            // Only load blockchain data when element is visible
            if (typeof IntersectionObserver !== 'undefined') {
                var observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            loadBlockchainData($this);
                            observer.unobserve(entry.target);
                        }
                    });
                });
                
                observer.observe(this);
            }
        });
    }
    
    function loadBlockchainData($element) {
        // This would make an AJAX call to load blockchain verification data
        // For now, just add a loading indicator
        $element.find('.omg-transactions').prepend('<div class="omg-loading">Loading blockchain data...</div>');
        
        setTimeout(function() {
            $element.find('.omg-loading').remove();
        }, 1000);
    }
    
    lazyLoadElements();
    
});

