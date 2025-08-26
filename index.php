<?php
  ob_start();
  require_once('includes/load.php');
  
  if($session->isUserLoggedIn(true)) {
    // Get the current user's data
    $user = current_user();
    
    // Redirect based on user level
    if($user['user_level'] === '1') {
      redirect('admin.php', false); // Admin dashboard
    } elseif($user['user_level'] === '2') {
      redirect('special_dashboard.php', false); // Special user dashboard
    } else {
      redirect('home.php', false); // Regular user dashboard
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php if (!empty($page_title))
            echo remove_junk($page_title);
        elseif (!empty($user))
            echo ucfirst($user['name']);
        else echo "Inventory Management System"; ?>
    </title>
    <script src="https://kit.fontawesome.com/YOUR-FONT-AWESOME-KEY.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="libs/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Ubuntu&display=swap" rel="stylesheet">
</head>
<body>
    <header class="mkd-logo">
        <img class="img-1" src="libs/images/code-logo.png">
        <img class="img-2" src="libs/images/mkd-logo.png">
    </header>

    <section class="front">
        <div class="content">
        <h1>
            <span class="highlight">M</span>KD<br>
            <span class="highlight">I</span>nventory<br>
            <span class="highlight">S</span>ystem
        </h1>

            <button class="btnLogin-popup">Login</button>
        </div>
        <div class="content-img">
            <div class="shape">
                <!-- <img src="libs/images/mkd-logo.png"> -->
            </div>
        </div>

        <div class="shape2"></div>
    </section>

    <div class="wrapper">
        <span class="icon-close">
            <ion-icon name="close"></ion-icon>
        </span>

        <div class="form-box login">
            <h2>Login</h2>
            <?php echo display_msg($msg); ?>
            <form method="post" action="auth.php" class="clearfix">
                <div class="input-box">
                    <span class="icon"><ion-icon name="person"></ion-icon></span>
                    <input type="name" name="username" required>
                    <label for="username">Username</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                    <input type="password" id="password" name="password" required>
                    <label for="Password">Password</label>
                </div>
                <button type="submit" class="btn">Login</button>
                <div class="forgot-password">
                    <a href="#" class="forgot-link">Forgot Password?</a>
                </div>
            </form>
        </div>

        <div class="form-box forgot-password">
            <h2>Forgot Password</h2>
            <p class="forgot-instruction">Enter your username or email address and we'll send you a reset link.</p>
            <form action="forgot_password.php" method="post" class="clearfix">
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail"></ion-icon></span>
                    <input type="text" name="username_email" required>
                    <label>Username or Email</label>
                </div>
                <button type="submit" class="btn">Send Reset Link</button>
                <div class="back-to-login">
                    <a href="#" class="login-link">Back to Login</a>
                </div>
            </form>
        </div>

    </div>

    <!-- Overlay for click outside to close -->
    <div class="overlay"></div>

    <script src="libs/js/functions.js"></script>
    <script src="libs/js/scriptz.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>