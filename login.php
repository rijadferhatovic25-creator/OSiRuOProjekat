<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "All fields are required!";
    } else {
        $stmt = $conn->prepare("SELECT id, password, role, status FROM korisnici WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $hashed_password, $role, $status);
            $stmt->fetch();

            if ($status === 'banned') {
                $error = "Your account has been suspended!";
            } elseif (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                header("Location: index.php");
                exit();
            } else {
                $error = "Incorrect password!";
            }
        } else {
            $error = "Username does not exist!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Filmoteka</title>
    <link rel="stylesheet" href="stil.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #0b0b0b;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        header {
            position: absolute;
        }
        .auth-wrapper {
            width: 100%;
            max-width: 420px;
        }
        .auth-card {
            background: #111111;
            border: 1px solid #222222;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.8);
        }
        .auth-brand {
            font-size: 32px;
            font-weight: 800;
            color: #e50914;
            text-align: center;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        .auth-subtitle {
            color: #888;
            font-size: 14px;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 500;
        }
        .form-group {
            margin-bottom: 22px;
        }
        .form-group label {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #aaa;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-control {
            width: 100%;
            padding: 14px 16px;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 6px;
            color: #fff;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #e50914;
            background: #222;
            outline: none;
            box-shadow: 0 0 10px rgba(229, 9, 20, 0.2);
        }
        .auth-btn {
            width: 100%;
            padding: 14px;
            background: #e50914;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .auth-btn:hover {
            background: #b81d24;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(229, 9, 20, 0.4);
        }
        .auth-btn:active {
            transform: translateY(0);
        }
        .msg {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 25px;
            font-size: 14px;
            text-align: center;
            font-weight: 600;
            background: rgba(229, 9, 20, 0.1);
            color: #e50914;
            border: 1px solid rgba(229, 9, 20, 0.3);
        }
        .auth-switch {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }
        .auth-switch a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
            margin-left: 5px;
        }
        .auth-switch a:hover {
            color: #e50914;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">FILMOTEKA</div>
</header>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">FILMOTEKA</div>
        <div class="auth-subtitle">Sign in to your account</div>
        
        <?php if(!empty($error)): ?>
            <div class="msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="auth-btn">Sign In</button>
        </form>

        <div class="auth-switch">
            Don't have an account? <a href="registracija.php">Register now</a>
        </div>
    </div>
</div>

</body>
</html>
