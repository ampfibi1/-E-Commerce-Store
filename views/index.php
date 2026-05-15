<?php
session_start();
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: ./admin/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>LOGIN</h1>
        <span id="errormsg" style="color: red;">
            <?php
            echo isset($_SESSION['msg']) ? $_SESSION['msg'] : '';
            ?>
        </span>
        <form action="../controller/loginController.php" method="post" id="loginForm">
            <label for="email">Email:</label><br>
            <input type="email" name="email" id="email" placeholder="Email" value="admin@example.com">
            <br>
            <label for="password">Password:</label><br>
            <input type="password" name="password" id="password" placeholder="Password" value="admin123">
            <br><br>
            <input type="submit" value="Login">
        </form>
        <p>Don't have an account? <a href="../controller/registerController.php">Register here</a></p>
    </div>
    <script>
        //js validation
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (email === '' || password === '') {
                document.getElementById('errormsg').textContent = 'Please fill in all fields.';
                //alert('Please fill in all fields.');
                event.preventDefault();
            }
        });
    </script>
</body>
</html>