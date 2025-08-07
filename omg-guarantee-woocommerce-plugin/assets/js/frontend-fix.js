/**
 * OM Guarantee Frontend Fix Script
 * Ensures shortcode components have the correct class names
 * and fixes display issues that might occur with certain themes
 */
(function($) {
    'use strict';

    // Wait for document to be ready
    $(document).ready(function() {
        // Add omg-widget class to all shortcode components if they don't have it
        $('.omg-certification-badge, .omg-impact-dashboard, .omg-impact-summary, .omg-donation-counter, .omg-charity-list')
            .not('.omg-widget')
            .addClass('omg-widget');
        
        // Ensure omg-card class is applied
        $('.omg-certification-badge, .omg-impact-dashboard, .omg-charity-list').not('.omg-card').addClass('omg-card');
        
        // Wrap content in card-body if not already wrapped
        $('.omg-card').each(function() {
            if ($(this).children('.omg-card-body').length === 0) {
                $(this).wrapInner('<div class="omg-card-body"></div>');
            }
        });
        
        // Force font families on all text elements inside widgets
        $('.omg-widget').find('h1, h2, h3, h4, h5, h6, p, span, div, a').css({
            'font-family': 'Poppins, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
        });
        
        // Make sure images load correctly
        $('.omg-cert-image').each(function() {
            var $this = $(this);
            var src = $this.attr('src');
            
            // Check if image failed to load
            if ($this.height() === 0 || !src) {
                // Try to fix by reloading the image or using a default path
                var imgName = src ? src.split('/').pop() : 'OMGcertificate2022.png';
                var newSrc = window.omg_woo_ajax.plugin_url + 'assets/images/' + imgName;
                $this.attr('src', newSrc);
            }
        });
    });
    
})(jQuery);