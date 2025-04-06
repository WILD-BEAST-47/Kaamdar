<?php
// Initialize variables
$msg = '';
$success = false;

if(isset($_REQUEST['submit'])) {
    // Sanitize and validate input
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    // Validate required fields
    if(empty($name) || empty($subject) || empty($email) || empty($message)) {
        $msg = '<div class="alert alert-warning col-sm-6 ml-5 mt-2" role="alert"> All Fields are Required </div>';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = '<div class="alert alert-warning col-sm-6 ml-5 mt-2" role="alert"> Please enter a valid email address </div>';
    } else {
        $mailTo = "contact@kaamdar.com.np";
        $headers = "From: " . $email . "\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        $txt = "You have received an email from " . $name . ".\n\n" . $message;
        
        if(mail($mailTo, $subject, $txt, $headers)) {
            $msg = '<div class="alert alert-success col-sm-6 ml-5 mt-2" role="alert"> Message Sent Successfully </div>';
            $success = true;
        } else {
            $msg = '<div class="alert alert-danger col-sm-6 ml-5 mt-2" role="alert"> Failed to send message. Please try again later. </div>';
        }
    }
}
?>

<!--Start Contact Us Row-->
<div class="col-md-8">
    <!--Start Contact Us 1st Column-->
    <form action="" method="post" class="needs-validation" novalidate>
        <div class="form-group">
            <input type="text" class="form-control" name="name" placeholder="Name" required 
                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
        </div>
        <div class="form-group">
            <input type="text" class="form-control" name="subject" placeholder="Subject" required
                   value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
        </div>
        <div class="form-group">
            <input type="email" class="form-control" name="email" placeholder="E-mail" required
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        <div class="form-group">
            <textarea class="form-control" name="message" placeholder="How can we help you?" style="height:150px;" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
        </div>
        <button class="btn btn-primary" type="submit" name="submit">Send</button>
        <?php if(isset($msg)) { echo $msg; } ?>
    </form>
</div>

<script>
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