<?php
// 包含資料庫連線
require 'db_connection.php';

if (isset($_POST['register'])) {
    // 收集表單資料
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // 密碼驗證
    if ($password !== $confirmPassword) {
        echo "密碼和確認密碼不相符！";
        exit();
    }


    // 檢查用戶名是否已存在
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
		echo "該用戶名已經存在！";
		echo '<script>
				setTimeout(function() {
					window.location.href = "register.php";
				}, 1500); // 延遲 1 秒後跳轉
			</script>';
		exit(); // 停止執行 PHP
	}

    // 插入新用戶資料
    $sql = "INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')";
    if (mysqli_query($conn, $sql)) {
        echo "註冊成功，請前往登入！";
        echo '<script>
				setTimeout(function() {
					window.location.href = "index.php";
				}, 1500); // 延遲 1 秒後跳轉
			</script>';
		exit();  // 註冊成功後跳轉到登入頁面
    } else {
        echo "註冊失敗，請稍後再試！";
    }
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>註冊頁面</title>
</head>
<body>
    <h1>註冊帳號</h1>
    <form action="register.php" method="POST">
        <label for="username">用戶名：</label>
        <input type="text" name="username" id="username" required><br>

        <label for="password">密碼：</label>
        <input type="password" name="password" id="password" required><br>

        <label for="confirm_password">確認密碼：</label>
        <input type="password" name="confirm_password" id="confirm_password" required><br>

        <label for="email">電子郵件：</label>
        <input type="email" name="email" id="email" required><br>
		<button type="button" onclick="window.location.href='index.php';">返回</button>
        <button type="submit" name="register">註冊</button>
		
    </form>
</body>
</html>

