<?php
include("check_login.php");
?>
<html>

<head>
    <title>Đăng nhập</title>

    <style>
        body {
            font-family: Arial;
            background: linear-gradient(
                270deg,
                red,
                orange,
                yellow,
                green,
                cyan,
                blue,
                violet
            );
            background-size: 1400% 1400%;
            animation: rainbow 0.1s ease infinite;
        }

        @keyframes rainbow {
            0% {background-position:0% 50%;}
            50% {background-position:100% 50%;}
            100% {background-position:0% 50%;}
        }

        .box {
            width: 300px;
            margin: 120px auto;
            padding: 20px;
            background: white;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 0 10px gray;
        }

        input {
            width: 90%;
            padding: 8px;
            margin: 8px 0;
        }

        button {
            padding: 8px 20px;
        }

        .message {
            margin-top: 15px;
            font-weight: bold;
        }
    </style>

</head>

<body>
    <?php
        $message = "";
        $style = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") 
        {
            $username = $_POST["username"];
            $password = $_POST["password"];

            if ($username == "admin" && $password == "123456") 
            {
                $message = "Welcome to admin";
                $style = "font-family:Tahoma; color:red;";
            } 
            else 
            {
                $message = "Username hoặc password không chính xác. Vui lòng đăng nhập lại";
            }
        }
    ?>
    <div class="box">
        <h2>Đăng nhập</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Đăng nhập</button>
        </form>

        <div class="message" style="<?php echo $style ?>">
            <?php echo $message ?>
        </div>
    </div>
</body>

</html>