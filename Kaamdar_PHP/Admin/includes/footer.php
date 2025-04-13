                </div>
            </main>
        </div>
    </div>

    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Initialize tooltips
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });

        // Initialize popovers
        $(function () {
            $('[data-toggle="popover"]').popover()
        });

        // Auto-hide alerts after 5 seconds
        $(document).ready(function(){
            setTimeout(function(){
                $('.alert').fadeOut('slow');
            }, 5000);
        });

        // Confirm delete actions
        $('.delete-btn').on('click', function(e){
            if(!confirm('Are you sure you want to delete this item?')){
                e.preventDefault();
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
    </script>
</body>
</html>