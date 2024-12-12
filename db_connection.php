<?php
$servername = "localhost";  // 資料庫伺服器
$username = "root";         // 資料庫使用者名稱
$password = "31415926";     // 資料庫密碼
$dbname = "buffet";     // 資料庫名稱

// 建立資料庫連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線是否成功
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}
?>
