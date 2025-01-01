<?php
session_start();
require 'db_connection.php'; // 連接資料庫

// 獲取當前時間
date_default_timezone_set('Asia/Taipei');
$now = new DateTime();
$currentTimestamp = $now->format('Y-m-d H:i:s');

// 更新所有已超過 10 分鐘的 reserved 桌位為 vacant
$stmtUpdateExpired = $conn->prepare("
    UPDATE tables 
    SET status = 'vacant', reservation_time = NULL, 
		check_in_time = NULL, 
		diners_count = 0, 
		total_amount = 0, 
		Last_name = '', 
		phone_number = '0900-000000' 
    WHERE status = 'reserved' 
    AND TIMESTAMPDIFF(MINUTE, reservation_time, ?) >= 10
");
$stmtUpdateExpired->bind_param('s', $currentTimestamp);
$stmtUpdateExpired->execute();

// 更新所有已經超過 1 小時且 30 分鐘的桌位狀態為 'vacant'
$stmtUpdate = $conn->prepare("
    UPDATE tables 
    SET total_amount = 0,
	status = 'vacant', 
	diners_count = 0, 
	reservation_time = NULL,
	check_in_time = NULL, 
	Last_name = '', 
	phone_number = '0900-000000' 
    WHERE status = 'occupied' 
    AND (
        TIMESTAMPDIFF(HOUR, check_in_time, ?) > 1 OR 
        (TIMESTAMPDIFF(HOUR, check_in_time, ?) = 1 AND TIMESTAMPDIFF(MINUTE, check_in_time, ?) >= 30)
    )
");
$stmtUpdate->bind_param('sss', $currentTimestamp, $currentTimestamp, $currentTimestamp);
$stmtUpdate->execute();

// 查詢所有桌位狀態
$stmt = $conn->prepare("
    SELECT table_number, status, reservation_time, check_in_time 
    FROM tables 
    ORDER BY table_number
");
$stmt->execute();
$result = $stmt->get_result();
$tables = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="30"> <!-- 每30秒刷新一次頁面 -->
    <title>服務生介面</title>
    <style>
		
		/* 按鈕樣式 */
        .view-reserved-button {
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
        .view-reserved-button:hover {
            background-color: #a1a049;
        }
		
		.login-container {
			background-color: rgba(0, 255, 255, 0.5); /* 半透明青色 清一色 */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 100;
            text-align: center;
        }
		
        /* 標題樣式 */
        h1 {
            text-align: center;
            margin-top: 20px;
        }
        /* 桌位容器 */
        .tables-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin: 20px;
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
            background-color: #a1a049;
        }
    </style>
</head>
<body>
	<div class="login-container">
		<h1>服務生介面</h1>
	
		<div class="tables-container">
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
	</div>
    <!-- 返回按鈕 -->
    <button class="back-button" onclick="window.location.href='index.php'">返回首頁</button>
		<!-- 查看 reserved 桌位按鈕 -->
    <button class="view-reserved-button" onclick="window.location.href='reserved.php'">查看已預訂桌位</button>

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
