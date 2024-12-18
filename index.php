<?php
session_start();
require 'db_connection.php'; // 連接資料庫

// 處理登入請求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $isGuest = isset($_POST['guest']); // 判斷是否是以訪客身份登入

    if ($isGuest) {
        $_SESSION['user_id'] = 'guest';
        $_SESSION['username'] = '訪客';
        header("Location: order.php?table_number=" . $_GET['table_number']);
        exit();
    } else {
        // 從資料庫驗證用戶
        $stmt = $conn->prepare("SELECT id, password, is_admin FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $username;

                if ($user['is_admin'] == 1) {
                    header('Location: admin.php'); // 管理員跳轉
                } else {
                    header("Location: order.php?table_number=" . $_GET['table_number']);
                }
                exit();
            } else {
                $error = "密碼錯誤";
            }
        } else {
            $error = "用戶名不存在";
        }
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登入頁面</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            width: 48%;
            padding: 10px;
            margin: 10px 5px;
            border-radius: 5px;
            border: none;
            background-color: #5cb85c;
            color: white;
            cursor: pointer;
        }
        button[type="submit"]:hover {
            background-color: #4cae4c;
        }
        .guest-btn {
            background-color: #f0ad4e;
        }
        .guest-btn:hover {
            background-color: #ec971f;
        }
        .register-link {
            margin-top: 15px;
            font-size: 14px;
        }
        .register-link a {
            color: #007bff;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h1>南華吃到飽 - 登入</h1>
        
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">用戶名：</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">密碼：</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">登入</button>
        </form>

        <form method="POST">
            <button type="submit" name="guest" value="1" class="guest-btn">以訪客身份進入</button>
        </form>

        <div class="register-link">
            <p>還沒有帳號嗎？<a href="register.php">註冊新帳號</a> 會員可享有九折優惠!</p>
        </div>
    </div>

</body>
</html>
