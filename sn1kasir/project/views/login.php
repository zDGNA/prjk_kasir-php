
<?php
require_once '../controllers/AuthController.php';

$auth = new AuthController();

// Redirect if already logged in
if($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error_message = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if(!empty($username) && !empty($password)) {
        $result = $auth->login($username, $password);
        if($result['success']) {
            header("Location: " . $result['redirect']);
            exit();
        } else {
            $error_message = $result['message'];
        }
    } else {
        $error_message = 'Please fill in all fields';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Kasir</title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #1e1e2f;
        color: #dcdcdc;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-container {
        background: #2c2c3c;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        width: 100%;
        max-width: 400px;
    }

    .login-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .login-header h1 {
        color: #ffffff;
        font-size: 28px;
        margin-bottom: 10px;
    }

    .login-header p {
        color: #aaa;
        font-size: 14px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #cccccc;
        font-weight: 500;
    }

    .form-group input {
        width: 100%;
        padding: 12px;
        background: #1e1e2f;
        border: 2px solid #444;
        border-radius: 5px;
        font-size: 16px;
        color: #f1f1f1;
        transition: border-color 0.3s;
    }

    .form-group input:focus {
        outline: none;
        border-color: #667eea;
    }

    .btn-login {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .btn-login:hover {
        transform: translateY(-2px);
    }

    .error-message {
        background: #5a1e22;
        color: #f5aeb0;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 20px;
        border: 1px solid #cc4c5a;
    }

    .demo-info {
        margin-top: 20px;
        padding: 15px;
        background: #34344f;
        border-radius: 5px;
        font-size: 14px;
    }

    .demo-info h4 {
        color: #ffffff;
        margin-bottom: 10px;
    }

    .demo-info p {
        color: #c0c0d0;
        margin-bottom: 5px;
    }
</style>

</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Sistem Kasir</h1>
            <p>Silakan login untuk melanjutkan</p>
        </div>

        <?php if($error_message): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="demo-info">
            <h4>Demo Account:</h4>
            <p><strong>Username:</strong> admin</p>
            <p><strong>Password:</strong> password</p>
        </div>
    </div>
</body>
</html>