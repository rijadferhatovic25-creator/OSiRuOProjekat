<?php
session_start();
$target = isset($_SESSION['user_id']) ? "index.php" : "landing.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link rel="stylesheet" href="stil.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        .error-wrapper {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background: #0b0b0b;
            padding: 20px;
            box-sizing: border-box;
        }
        .error-code {
            font-size: 150px;
            font-weight: 900;
            color: #e50914;
            line-height: 1;
            margin-bottom: 10px;
            text-shadow: 0 0 30px rgba(229, 9, 20, 0.3);
        }
        .error-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #fff;
        }
        .error-desc {
            font-size: 16px;
            color: #aaa;
            max-width: 500px;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        .error-btn {
            background: #e50914;
            color: #fff;
            padding: 14px 35px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            text-transform: uppercase;
            border-radius: 4px;
            letter-spacing: 0.5px;
            transition: background 0.2s;
        }
        .error-btn:hover {
            background: #c40812;
        }
    </style>
</head>
<body>

<div class="error-wrapper">
    <div class="error-code">404</div>
    <div class="error-title">Lost in the theater?</div>
    <div class="error-desc">It looks like this movie has been completely cut from the final screening. The scene or route you are looking for does not exist.</div>
    <a href="<?php echo $target; ?>" class="error-btn">Return To Safety</a>
</div>

</body>
</html>