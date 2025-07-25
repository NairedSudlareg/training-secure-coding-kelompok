<?php
require_once '../../includes/session.php';
require_once '../../config/env.php';
require_once '../../includes/auth.php';

$error = '';

// Handle login BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    // if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
    //     $error = "Invalid email format";
    // }
    $pattern = "/^[a-zA-Z0-9]+$/";
    if(!preg_match($pattern, $username)) {
        // $response['message'] = "Username only alphanumeric";
        // return $response;
        $error = 'Username only alphanumeric';
    }

    $auth = new Auth();
    $user = $auth->login($username, $password);
    

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        
        // Redirect based on role
        if ($user['role'] === 'member') {
            header('Location: ../member/dashboard.php');
        } else {
            header('Location: ../company/dashboard.php');
        }
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}

// Include templates AFTER login processing
require_once '../../templates/header.php';
require_once '../../templates/nav.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Login</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Don't have an account? <a href="register.php">Register here</a></p>
                        <p><a href="forgot-password.php">Forgot Password?</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>