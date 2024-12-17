<?php
session_start();
require 'db_connection.php'; // 連資料庫

// 查詢所有桌位狀態
$stmt = $conn->prepare("SELECT table_number, status, reservation_time, check_in_time FROM tables ORDER BY table_number");
$stmt->execute();
$result = $stmt->get_result();
$tables = $result->fetch_all(MYSQLI_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="refresh" content="60"> <!-- 每60秒刷新一次頁面 -->
    <title>服務生介面</title>
    <style>
        /* 標題樣式 */
        h1 {
            text-align: center;
            margin-top: 20px;
        }
        /* 桌位樣式 */
        .table {
            display: inline-block;
            width: 100px;
            height: 100px;
            margin: 10px;
            text-align: center;
            border-radius: 10px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            position: relative;
        }
        .table.green {
            background-color: green;
        }
        .table.yellow {
            background-color: yellow;
            color: black;
        }
        .table.red {
            background-color: red;
        }
        /* 提醒紅點 */
        .dot {
            width: 20px;
            height: 20px;
            background-color: blue;
            border-radius: 50%;
            position: absolute;
            top: -5px;
            right: -5px;
        }
        /* 返回按鈕樣式 */
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
        }
        .back-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>服務生介面</h1>

    <!-- 顯示每桌客人的入座時間-->
    <?php
    foreach ($tables as $table) {
        if ($table['status'] === 'occupied' && $table['check_in_time']) {
            echo "<p>桌號 {$table['table_number']} 入座時間：{$table['check_in_time']}</p>";
        }
    }
    ?>

    <div>
        <?php
        // 顯示每桌狀態
        foreach ($tables as $table) {
            $class = $table['status'] === 'vacant' ? 'green' : ($table['status'] === 'reserved' ? 'yellow' : 'red');
            $tooltip = $table['status'] === 'reserved' ? "訂位時間：{$table['reservation_time']}" : '';

            echo "<div class='table $class' title='$tooltip' onclick='redirectToTable({$table['table_number']})'>";
            echo "桌號 {$table['table_number']}";

            if ($class === 'yellow') {
                echo "<br>訂位時間：{$table['reservation_time']}";
            }
            if ($class === 'red') {
                echo "<br>入桌時間：{$table['check_in_time']}";
            }
            if ($class === 'red' && needsAttention($table['table_number'], $conn)) {
                echo "<div class='dot'></div>"; // 提醒紅點
            }
            echo "</div>";
        }
        ?>
    </div>

    <!-- 返回按鈕 -->
    <a href="index.php">
        <button class="back-button">返回首頁</button>
    </a>

    <script>
        // 點擊跳到桌位管理頁面
        function redirectToTable(tableNumber) {
            window.location.href = `table_management.php?table_number=${tableNumber}`;
        }
    </script>
</body>
</html>

<?php
// 判斷是否需要提醒的函式
function needsAttention($tableNumber, $conn) {
    // 查詢是否有新訂單
    $stmt = $conn->prepare("SELECT COUNT(*) AS order_count FROM served_orders WHERE table_number = ? and  is_delivered = 0");
    $stmt->bind_param('i', $tableNumber);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['order_count'] > 0;
}
?>
