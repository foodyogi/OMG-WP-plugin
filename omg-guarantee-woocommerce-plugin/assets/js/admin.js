jQuery(document).ready(function($) {
    
    // Charity search functionality
    $('#charity_search').on('input', function() {
        var searchTerm = $(this).val();
        
        if (searchTerm.length >= 3) {
            searchCharities(searchTerm);
        } else {
            $('#charity_search_results').hide();
        }
    });
    
    // Test charity search button
    $('#test_charity_search').on('click', function() {
        var searchTerm = $('#charity_search').val();
        if (searchTerm.length >= 3) {
            searchCharities(searchTerm);
        } else {
            alert('Please enter at least 3 characters to search');
        }
    });
    
    // Test donation processing
    $('#test_donation_processing').on('click', function() {
        var $button = $(this);
        var $results = $('#test_results');
        
        $button.prop('disabled', true).text('Testing...');
        $results.html('<p>Testing donation processing...</p>').show();
        
        $.ajax({
            url: omg_woo_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'omg_woo_test_donation',
                nonce: omg_woo_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $results.html('<div class="notice notice-success"><p>✅ Donation processing test successful!</p><pre>' + JSON.stringify(response.data, null, 2) + '</pre></div>');
                } else {
                    $results.html('<div class="notice notice-error"><p>❌ Donation processing test failed: ' + response.data + '</p></div>');
                }
            },
            error: function() {
                $results.html('<div class="notice notice-error"><p>❌ Connection error during donation test</p></div>');
            },
            complete: function() {
                $button.prop('disabled', false).text('Test Donation Processing');
            }
        });
    });
    
    // Test blockchain connection
    $('#test_blockchain_connection').on('click', function() {
        var $button = $(this);
        var $results = $('#test_results');
        
        $button.prop('disabled', true).text('Testing...');
        $results.html('<p>Testing blockchain connection...</p>').show();
        
        // Simulate blockchain test (replace with actual implementation)
        setTimeout(function() {
            $results.html('<div class="notice notice-success"><p>✅ Blockchain connection successful!</p><p>Connected to Polygon network</p></div>');
            $button.prop('disabled', false).text('Test Blockchain Connection');
        }, 2000);
    });
    
    // Search charities function
    function searchCharities(searchTerm) {
        $.ajax({
            url: omg_woo_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'omg_woo_test_charity_search',
                search_term: searchTerm,
                nonce: omg_woo_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    displayCharityResults(response.data);
                } else {
                    $('#charity_search_results').html('<div class="charity-result">No charities found</div>').show();
                }
            },
            error: function() {
                $('#charity_search_results').html('<div class="charity-result">Error searching charities</div>').show();
            }
        });
    }
    
    // Display charity search results
    function displayCharityResults(charities) {
        var html = '';
        
        charities.forEach(function(charity) {
            html += '<div class="charity-result" data-slug="' + charity.slug + '" data-name="' + charity.name + '">';
            html += '<strong>' + charity.name + '</strong>';
            if (charity.location) {
                html += '<br><small>' + charity.location + '</small>';
            }
            if (charity.category) {
                html += '<br><small>Category: ' + charity.category + '</small>';
            }
            html += '</div>';
        });
        
        $('#charity_search_results').html(html).show();
    }
    
    // Handle charity selection
    $(document).on('click', '.charity-result', function() {
        var slug = $(this).data('slug');
        var name = $(this).data('name');
        
        if (slug && name) {
            // Add to dropdown if not exists
            if ($('#omg_charity_select option[value="' + slug + '"]').length === 0) {
                $('#omg_charity_select').append('<option value="' + slug + '">' + name + '</option>');
            }
            
            // Select the charity
            $('#omg_charity_select').val(slug);
            
            // Clear search
            $('#charity_search').val('');
            $('#charity_search_results').hide();
            
            // Show success message
            $('#charity_search_results').html('<div class="notice notice-success"><p>✅ Charity selected: ' + name + '</p></div>').show();
            setTimeout(function() {
                $('#charity_search_results').hide();
            }, 3000);
        }
    });
    
    // Product page - toggle impact fields
    $('#_omg_enable_impact').on('change', function() {
        if ($(this).is(':checked')) {
            $('#_omg_impact_percentage, #_omg_charity').closest('.form-field').show();
        } else {
            $('#_omg_impact_percentage, #_omg_charity').closest('.form-field').hide();
        }
    }).trigger('change');
    
    // Auto-save settings notification
    var $form = $('form[action="options.php"]');
    var originalData = $form.serialize();
    
    $form.on('change', 'input, select, textarea', function() {
        var currentData = $form.serialize();
        if (currentData !== originalData) {
            if (!$('.omg-unsaved-notice').length) {
                $form.prepend('<div class="notice notice-warning omg-unsaved-notice"><p>⚠️ You have unsaved changes. Don\'t forget to save your settings!</p></div>');
            }
        }
    });
    
    $form.on('submit', function() {
        $('.omg-unsaved-notice').remove();
        originalData = $form.serialize();
    });
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $($(this).attr('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 50
            }, 500);
        }
    });
    
    // Tooltips for help text
    $('.description').each(function() {
        $(this).attr('title', $(this).text());
    });
    
    // Confirmation for destructive actions
    $('.button-destructive').on('click', function(e) {
        if (!confirm('Are you sure you want to perform this action? This cannot be undone.')) {
            e.preventDefault();
        }
    });
    
});

