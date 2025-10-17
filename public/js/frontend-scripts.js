/**
 * Skate Spots Frontend Scripts
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Form validation helper
        function validateEmail(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        // Smooth scroll for anchor links
        $('a[href^="#"]').on('click', function(e) {
            var target = $(this.hash);
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        });
        
        // Auto-hide success messages after 5 seconds
        setTimeout(function() {
            $('.success-message').fadeOut('slow');
        }, 5000);
        
        // Form field character counter (if needed)
        $('textarea[maxlength]').each(function() {
            var $textarea = $(this);
            var maxLength = $textarea.attr('maxlength');
            var $counter = $('<div class="char-counter"></div>');
            
            $textarea.after($counter);
            
            function updateCounter() {
                var remaining = maxLength - $textarea.val().length;
                $counter.text(remaining + ' characters remaining');
            }
            
            $textarea.on('input', updateCounter);
            updateCounter();
        });
        
        // Confirm before leaving page with unsaved form data
        var formModified = false;
        
        $('.skate-form input, .skate-form textarea, .skate-form select').on('change', function() {
            formModified = true;
        });
        
        $('.skate-form').on('submit', function() {
            formModified = false;
        });
        
        $(window).on('beforeunload', function() {
            if (formModified) {
                return 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
        
    });
    
})(jQuery);