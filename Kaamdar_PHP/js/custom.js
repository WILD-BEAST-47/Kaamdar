// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
    const serviceOpenButton = document.getElementById('service-open-button');
    const serviceCloseButton = document.getElementById('service-close-button');
    const navService = document.querySelector('.nav-service');

    if (serviceOpenButton && serviceCloseButton && navService) {
        serviceOpenButton.addEventListener('click', function() {
            document.body.classList.add('show-mobile-service');
        });

        serviceCloseButton.addEventListener('click', function() {
            document.body.classList.remove('show-mobile-service');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!navService.contains(event.target) && !serviceOpenButton.contains(event.target)) {
                document.body.classList.remove('show-mobile-service');
            }
        });
    }
});

// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Only Number for input fields
function isInputNumber(evt) {
    var ch = String.fromCharCode(evt.which);
    if (!(/[0-9]/.test(ch))) {
        evt.preventDefault();
    }
}

// Set today's date as default for date inputs
document.addEventListener('DOMContentLoaded', function() {
    var dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function(input) {
        if (!input.value) {
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var yyyy = today.getFullYear();
            input.value = yyyy + '-' + mm + '-' + dd;
        }
    });
}); 