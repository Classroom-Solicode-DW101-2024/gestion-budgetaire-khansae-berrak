<?php

require 'config.php';
include 'user.php';

$errors = [];


if(isset($_POST['regBtn'])){

    $_fullName = $_POST['fullName'];
    $_email = $_POST['email'];
    $_password = $_POST['password'];
    $_confirmPassword = $_POST['conPassword'];
 

    if(empty($_fullName)){

        $errors['fullName'] = 'Full name is required.';

    }

    if(empty($_email)){

        $errors['email'] = 'Email address is required.';

    }else{
        $isEmailAvaillable = checkUser($_email,$pdo);
        if($isEmailAvaillable){
            $errors['email'] = 'This email address is already in use. Please choose a different one.';
        }
    }

    if(empty($_password)){

        $errors['password'] = 'Password is required.';

    }

    if(empty($_confirmPassword)){

        $errors['ConPassword'] = 'Password confirmation is required.';

    }

    if (!empty($_password) && !empty($_confirmPassword) && $_password !== $_confirmPassword) {
        $errors['passwordMatch'] = 'Password and confirmation do not match.';
    }





    if(empty($errors)){

        $user = [
            'nom' =>htmlspecialchars($_fullName) ,
            'email' =>htmlspecialchars($_email) ,
            'password' => password_hash($_password,PASSWORD_DEFAULT),
        ];

        addUser($user,$pdo);


    }

}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/register.css">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url(bgpin.jpg) !important;
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
            box-shadow: 0 0 10px rgb(173, 36, 36);
            text-align: center;
            width: 400px;
            box-shadow: 0 0 10px #ff69b4;
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
            margin-left:-6PX;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color:#e7a8c8;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
        }
        button:hover {
            background-color:#ac5d84;
        }
    </style>
</head>
<body>

    <div class="formContainer">
        <h2>Register Now</h2>
        <form method="post">

            <input type="text" placeholder="Full Name" name="fullName" id="registerFullName" value="<?php echo isset($_fullName) ? htmlspecialchars($_fullName) : ''; ?>">
            <?php if (isset($errors['fullName'])): ?>
                <p><?php echo $errors['fullName']; ?></p>
            <?php endif; ?>
            <input type="email" placeholder="Email" name="email" id="registerEmail" value="<?php echo isset($_email) ? htmlspecialchars($_email) : ''; ?>">
            <?php if (isset($errors['email'])): ?>
                <p><?php echo $errors['email']; ?></p>
            <?php endif; ?>
            <input type="password" name="password" id="registerPassword" placeholder="Password" >
            <?php if (isset($errors['password'])): ?>
                <p><?php echo $errors['password']; ?></p>
            <?php endif; ?>
            <input type="password" name="conPassword" id="registerConfPassword" placeholder="Confirm Password">
            <?php if (isset($errors['ConPassword'])): ?>
                <p><?php echo $errors['ConPassword']; ?></p>
            <?php endif; ?>
            <?php if (isset($errors['passwordMatch'])): ?>
                <p><?php echo $errors['passwordMatch']; ?></p>
            <?php endif; ?>
            <button name="regBtn">Register</button>
        </form>
        <a href="login.php">Have an Acoount ? Login</a>
    </div>
    
</body>
</html>