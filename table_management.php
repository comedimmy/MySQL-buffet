<?php
session_start();
require 'db_connection.php'; 

// 確保有桌號參數
if (!isset($_GET['table_number']) || !in_array($_GET['table_number'], [1,2,3,4,5,6,7,8,9,10])) {
    echo "無效的桌號";
    exit();
}

$tableNumber = $_GET['table_number']; // 獲取桌號

// 查詢該桌的狀態及預定時間
$stmt = $conn->prepare("SELECT status, reservation_time FROM tables WHERE table_number = ?");
$stmt->bind_param('i', $tableNumber);
$stmt->execute();
$result = $stmt->get_result();
$table = $result->fetch_assoc();

if (!$table) {
    echo "無效的桌號，請稍後重試。";
    exit();
}

$tableStatus = $table['status'];
$reservationTime = $table['reservation_time']; // 預定時間

// 檢查是否超過預定時間10分鐘
$currentTime = new DateTime();
$reservationTime = new DateTime($reservationTime);
$timeDiff = $currentTime->diff($reservationTime);
$timeDiffMinutes = $timeDiff->i; // 分鐘數

//如果超過10分鐘，將桌位狀態更新為 'vacant'
	//if ($timeDiffMinutes > 10 && $tableStatus === 'reserved') {
	//	$updateStmt = $conn->prepare("UPDATE tables SET status = 'vacant' WHERE table_number = ?")	
	//	$updateStmt->bind_param('i', $tableNumber)	
	//	$updateStmt->execute()	
	//	$tableStatus = 'vacant'; // 更新狀態到 'vacant'
	//}

//處理用餐人數設定
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_diners'])) {
    $diners = $_POST['diners']; // 用餐人數
    $totalAmount = $diners * 399; // 計算金額，假設每人399元

    // 更新該桌的用餐狀況和人數
    $stmt = $conn->prepare("UPDATE tables SET status = 'occupied', diners_count = ?, total_amount = ?, check_in_time = now() WHERE table_number = ?");
    $stmt->bind_param('iii', $diners, $totalAmount, $tableNumber);
    if ($stmt->execute()) {
        header("Location: table_management.php?table_number={$tableNumber}");
        exit();
    } else {
        echo "設置失敗，請稍後重試。";
    }
}

// 查詢該桌未送達的餐點
$stmt = $conn->prepare("SELECT id, food_name, quantity FROM served_orders WHERE table_number = ? AND is_delivered = 0");
$stmt->bind_param('i', $tableNumber);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="30"> <!-- 每60秒刷新一次頁面 -->
    <title>桌號 <?= htmlspecialchars($tableNumber) ?> 管理頁面</title>
	<style>
        /* 用來控制頁面中央顯示 */
        body {
            display: flex;
            justify-content: center;
            align-items: flex-start; /* 頁面內容從頂部開始 */
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        /* 外層容器 */
        .container {
			width: 80%;
			max-width: 1200px;
			padding: 20px;
			background-color: #f4f4f9;
			border-radius: 8px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
			margin-bottom: 20px;
		
			/* 使用 Flexbox 來將內容置中 */
			display: flex;
			flex-direction: column;
			align-items: center; /* 垂直居中 */
			justify-content: flex-start; /* 上方對齊 */
		}

        /* 顯示訊息區域 */
        .message {
            color: #f44336;
            text-align: center;
        }

        /* 表單內按鈕區域 */
        .form-container {
            display: flex;
            justify-content: flex-start;
            gap: 10px;
            margin-bottom: 20px;
        }

        .form-container label, .form-container input, .form-container button {
            font-size: 16px;
        }

        /* 未送達餐點表格 */
        .orders-container {
            float: right;
            width: 48%; /* 控制餐點表格寬度 */
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        /* 返回主介面按鈕 */
        .back-button-container {
            text-align: center; /* 確保返回按鈕在容器中居中 */
            margin-top: 30px;
        }

        .back-button {
            padding: 12px 25px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .back-button:hover {
            background-color: #e53935;
        }
    </style>

</head>
<body>
	<div class="container">
		<h1>桌號 <?= htmlspecialchars($tableNumber) ?> 管理頁面</h1>
	
		<!-- 顯示訊息 -->
		<?php
		if (isset($_SESSION['message'])) {
			echo "<p>{$_SESSION['message']}</p>";
			unset($_SESSION['message']);
		}
		?>
	
		<!-- 顯示不同按鈕根據桌位的狀態 -->
		<form method="POST">
		<?php if ($tableStatus === 'reserved'): ?>
				<!-- 客人已到達按鈕 -->
				<button type="submit" name="guest_arrived">客人已到達</button>
			<?php elseif ($tableStatus === 'vacant'): ?>
				<!-- 設定用餐人數按鈕 -->
				<label for="diners">用餐人數：</label>
				<input type="number" id="diners" name="diners" min="1" max="12" required>
				<button type="submit" name="set_diners">設定用餐人數</button>
			<?php endif; ?>
			<?php if ($tableStatus === 'vacant'): ?>
				<button type="button" onclick="window.location.href='book_a_table.php?table_number=<?= $tableNumber ?>';">訂位</button>
			<?php endif; ?>
		</form>
	
		<h2>未送達餐點</h2>
		<table border="1">
			<tr>
				<th>餐點名稱</th>
				<th>數量</th>
				<th>操作</th>
			</tr>
	
			<?php while ($row = $result->fetch_assoc()): ?>
				<tr>
					<td><?= htmlspecialchars($row['food_name']) ?></td>
					<td><?= htmlspecialchars($row['quantity']) ?></td>
					<td>
						<form method="POST">
							<input type="hidden" name="order_id" value="<?= $row['id'] ?>">
							<button type="submit" name="mark_delivered">標記為送達</button>
						</form>
					</td>
				</tr>
			<?php endwhile; ?>
		</table>
	
		<?php
		// 標記餐點為送達
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_delivered'])) {
			$orderId = $_POST['order_id'];
	
			$stmt = $conn->prepare("UPDATE served_orders SET is_delivered = 1 WHERE id = ?");
			$stmt->bind_param('i', $orderId);
	
			if ($stmt->execute()) {
				header("Location: table_management.php?table_number={$tableNumber}");
				exit();
			} else {
				echo "標記送達失敗，請稍後再試。";
			}
		}
	
		// 當按下"客人已到達"按鈕時，將桌位狀態更新為"occupied"
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guest_arrived'])) {
			$updateStmt = $conn->prepare("UPDATE tables SET status = 'occupied',check_in_time=now() WHERE table_number = ?");
			$updateStmt->bind_param('i', $tableNumber);
			$updateStmt->execute();
	
			header("Location: table_management.php?table_number={$tableNumber}");
			exit();
		}
		?>
	
		<button type="button" onclick="window.location.href='admin.php';">返回主介面</button>
	</div>
</body>
</html>
