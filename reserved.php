<?php
session_start();
require 'db_connection.php'; // 連接資料庫

// 查詢所有狀態為 'reserved' 的桌位
$stmt = $conn->prepare("
    SELECT table_number, status, reservation_time, last_name, phone_number, diners_count 
    FROM tables 
    WHERE status = 'reserved' 
    ORDER BY table_number
");
$stmt->execute();
$result = $stmt->get_result();
$reservedTables = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - 查看已預訂桌位</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
        }
        /* 桌位顯示樣式 */
        .reserved-table {
            position: relative;
            padding: 15px;
            margin: 10px auto;
            background-color: #f0f0f0;
            border-radius: 5px;
            font-size: 16px;
            width: 300px;
            border: 1px solid #ddd;
            box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.1);
        }
        .dot {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 15px;
            height: 15px;
            background-color: red;
            border-radius: 50%;
        }
        .back-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .back-button:hover {
            background-color: #a1a049;
        }
    </style>
</head>
<body>

<h1>已預訂桌位</h1>

<?php
date_default_timezone_set('Asia/Taipei'); // 設定時區

if (count($reservedTables) > 0) {
    // 顯示所有 reserved 桌位
    foreach ($reservedTables as $table) {
        $isOverdue = strtotime($table['reservation_time']) < time(); // 檢查是否超過預定時間

        echo "<div class='reserved-table'>";
        if ($isOverdue) {
            echo "<div class='dot'></div>"; // 如果超過預約時間，顯示紅點
        }
        echo "桌號: {$table['table_number']}<br>";
        echo "貴姓: {$table['last_name']}<br>";
        echo "電話號碼: {$table['phone_number']}<br>";
        echo "用餐人數: {$table['diners_count']}<br>";
        echo "預約時間: {$table['reservation_time']}<br>";
        echo "</div>";
    }
} else {
    echo "<p>目前沒有已預訂的桌位。</p>";
}
?>

<button class="back-button" onclick="window.location.href='admin.php';">返回服務生介面</button>

</body>
</html>
