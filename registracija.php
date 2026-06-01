<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ime = trim($_POST['ime']);
    $prezime = trim($_POST['prezime']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($ime) || empty($prezime) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM korisnici WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username or email already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt_insert = $conn->prepare("INSERT INTO korisnici (ime, prezime, email, username, password, role) VALUES (?, ?, ?, ?, ?, 'guest')");
            $stmt_insert->bind_param("sssss", $ime, $prezime, $email, $username, $hashed_password);

            if ($stmt_insert->execute()) {
                $success = "Registration successful! You can now log in.";
            } else {
                $error = "An error occurred during registration!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - Filmoteka</title>
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
            padding: 40px 20px;
        }
        header {
            position: absolute;
        }
        .auth-wrapper {
            width: 100%;
            max-width: 500px;
            margin-top: 60px;
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
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .full-width {
            grid-column: span 2;
        }
        .form-group {
            margin-bottom: 20px;
            position: relative;
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
            padding: 12px 16px;
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
        }
        .msg-error {
            background: rgba(229, 9, 20, 0.1);
            color: #e50914;
            border: 1px solid rgba(229, 9, 20, 0.3);
        }
        .msg-success {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.3);
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
        @media (max-width: 576px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .full-width {
                grid-column: span 1;
            }
            .auth-card {
                padding: 25px 20px;
            }
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
        <div class="auth-subtitle">Create your new account</div>
        
        <?php if(!empty($error)): ?>
            <div class="msg msg-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div class="msg msg-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="registracija.php" method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="ime" class="form-control" placeholder="John" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="prezime" class="form-control" placeholder="Doe" required>
                </div>
                <div class="form-group full-width">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                </div>
                <div class="form-group full-width">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" placeholder="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="auth-btn">Register</button>
        </form>

        <div class="auth-switch">
            Already have an account? <a href="login.php">Log in</a>
        </div>
    </div>
</div>

</body>
</html>
