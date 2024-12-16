<?php
session_start();
require 'db_connection.php'; // 確保包含資料庫連線檔案

//檢查是否已登入
//if (isset($_SESSION['user_id'])&&$user['is_admin'] == 0) {
//    header('Location: order.php');
//    exit();
//}

// 處理登入請求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $isGuest = isset($_POST['guest']);
    if ($isGuest) {
        $_SESSION['user_id'] = 'guest';
        $_SESSION['username'] = '訪客';
        header('Location: order.php');
        exit();
    } else {
        // 從資料庫驗證用戶
        $stmt = $conn->prepare("SELECT id, password,is_admin FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $username;
                
             
				if($user['is_admin'] == 1){
					header('Location: admin.php'); // 管理員跳轉
				}else {
					header('Location: order.php'); // 一般用戶跳轉
				}
				exit();
			}else {
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
</head>
<body>
    <h1>吃到飽點餐系統 - 登入</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label for="username">用戶名：</label>
        <input type="text" id="username" name="username" required><br>
        <label for="password">密碼：</label>
        <input type="password" id="password" name="password" required><br>
        <button type="submit">登入</button>
    </form>
    <form method="POST">
        <button type="submit" name="guest" value="1">以訪客身份進入</button>
    </form>
	    <p>還沒有帳號嗎？<a href="register.php">註冊新帳號</a> 會員可享有九折優惠!</p>
</body>
</html>
