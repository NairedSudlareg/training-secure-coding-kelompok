<?php
require_once '../../includes/session.php';
require_once '../../config/env.php';
require_once '../../includes/auth.php';

$message = '';
$error = '';

// Handle registration BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    if($password !== $confirm_password) {
        $error = "Passwords do not match!";
        // return;
    } else {
        // Vulnerable: No input validation
        // Vulnerable: No password confirmation check
        // Vulnerable: No password complexity requirements
        // cek langsung dari Auth - aja buat validasi global
        $auth = new Auth();
        
        // Store password in plain text - major vulnerability
        $register = $auth->register($username, $email, $password, $role);
        $status = $register['status'];
        $responseMessage = $register['message'];
        if ($status == true) {
            // Send verification email after successful registration
            require_once '../../includes/email.php';
            $emailService = new EmailService();
            $verification_token = bin2hex(random_bytes(32));
    
            // Send registration email
            $emailSent = $emailService->sendRegistrationEmail($email, $username, $verification_token);
    
            if ($emailSent) {
                $message = 'Registration successful! Please check your email to verify your account.';
            } else {
                $message = 'Registration successful! However, there was an issue sending the verification email. Please contact support.';
            }
        } else {
            $error = 'Registration failed. Please try again.';
            $error = $responseMessage;
        }
    }
    
}

// Include templates AFTER registration processing
require_once '../../templates/header.php';
require_once '../../templates/nav.php';

$default_role = isset($_GET['role']) ? $_GET['role'] : 'member';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Register</h4>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="member" <?php echo $default_role === 'member' ? 'selected' : ''; ?>>Job Seeker</option>
                                <option value="company" <?php echo $default_role === 'company' ? 'selected' : ''; ?>>Company</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Vulnerable JavaScript - XSS and form validation bypass -->
<script>
    document.getElementById('username').addEventListener('input', function(e) {
        // Vulnerable: Direct innerHTML assignment
        // ERROR CODE : XSS-001 - USE textContent for more saferrrrrrrrrrrrrrrrrr because parse the input to stirng not HTML like innterHTML
        document.getElementById('username-feedback').textContent = 'Username: ' + e.target.value;
    });
    
    // Vulnerable: Client-side only validation
    // ERROR CODE : IV-004
    document.querySelector('form').addEventListener('submit', function(e) {
        var password = document.getElementById('password').value;
        var confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            alert('Passwords do not match!');
            e.preventDefault();
        }
    });
</script>

<div id="username-feedback"></div>

<?php require_once '../../templates/footer.php'; ?>