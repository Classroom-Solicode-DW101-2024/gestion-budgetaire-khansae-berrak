<?php
session_start();
include 'config.php'; // الاتصال بـ PDO مثلاً $pdo

// دالة تسجيل الدخول
function loginUser($email, $password, $pdo) {
    $email = htmlspecialchars($email);

    $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }

    return false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $user = loginUser($email, $password, $pdo);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nom'];
            $_SESSION['user_email'] = $user['email'];

            header("Location: indix.php");
            exit();
        } else {
            $error = "Email or password is incorrect.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>


<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول - FARHA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url(bgpin.jpg);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(5px);
            color: #000000cc;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ff69b4;
            text-align: center;
            width: 400px;
        }
        h2 {
            margin-bottom: 20px;
            color: #000000cc;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        label {
            color: white;
            display: block;
            text-align: left;
            margin-top: 10px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #e7a8c8;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
        }
        button:hover {
            background-color: #ac5d84;
        }
    </style>
    </style>
</head>
<body>
    <div class="login-container">
        <h2>login</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="" method="POST">
            <label for="email">email:</label>
            <input type="email" name="email" required  placeholder="Email">

            <label for="password">password</label>
            <input type="password" name="password" required placeholder="Password">

            <button type="submit">submit</button>
        </form>
    </div>
</body>
</html>
