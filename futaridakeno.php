<?php
session_start();
$conn = new mysqli("localhost", "root", "", "asfourmy_asfour");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        $message = "❌ Username tidak ditemukan!";
    } elseif (!password_verify($password, $user['password'])) {
        $message = "❌ Password salah!";
    } else {
        $_SESSION["admin"] = $user["username"];
        header("Location: dorimutaimu.php");
        exit;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #2980b9, #8e44ad);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            animation: backgroundAnimation 5s infinite alternate;
        }
        
        @keyframes backgroundAnimation {
            0% {
                background: linear-gradient(135deg, #2980b9, #8e44ad);
            }
            100% {
                background: linear-gradient(135deg, #16a085, #f39c12);
            }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 380px;
            width: 100%;
            animation: scaleUp 0.5s ease-in-out;
        }

        @keyframes scaleUp {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
            margin-bottom: 15px;
            transition: border 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #2980b9;
        }

        button {
            width: 100%;
            padding: 14px;
            background-color: #2980b9;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #3498db;
        }

        .error {
            color: red;
            margin-top: 10px;
            text-align: center;
            font-size: 14px;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #aaa;
            margin-top: 25px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login Admin</h2>
    <?php if ($message): ?>
        <div class="error"><?= $message ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Masuk</button>
    </form>
    <div class="footer">© 2025 ASFOUR. All Rights Reserved.</div>
</div>

</body>
</html>
