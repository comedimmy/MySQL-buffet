<?php
session_start();


if (isset($_GET['table_number'])) {
    $tableNumber = intval($_GET['table_number']); // 確保桌號是整數
    $_SESSION['table_number'] = $tableNumber; // 將桌號存入 session
} else if (isset($_SESSION['table_number'])) {
    $tableNumber = $_SESSION['table_number']; // 從 session 中獲取桌號
} else {
    echo "無法辨識桌號，請掃描正確的 QR Code！";
    exit();
}
$tableNumber = $_SESSION['table_number'];
// 連接資料庫
require 'db_connection.php';

// 處理提交訂單
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_food'])) {
    $foodName = $_POST['food_name'];
    $quantity = $_POST['quantity'];

    $stmt = $conn->prepare("INSERT INTO unserved_orders (food_name, quantity, table_number) VALUES (?, ?, ?)");
    $stmt->bind_param('sii', $foodName, $quantity, $tableNumber);

    if ($stmt->execute()) {
        echo "訂單已提交！";
    } else {
        echo "訂單提交失敗，請稍後重試。" . $stmt->error;;
    }
}
// 處理確認出餐
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serve_all'])) {
    // 查詢所有該桌的未出餐訂單
    $stmt = $conn->prepare("SELECT id, food_name, quantity, table_number FROM unserved_orders WHERE table_number = ?");
    $stmt->bind_param('i', $tableNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    // 將所有未出餐訂單插入到已出餐訂單
    $insertStmt = $conn->prepare("INSERT INTO served_orders (food_name, quantity, table_number, order_at) VALUES (?, ?, ?, NOW())");

    // 使用一個變數來追蹤是否有插入錯誤
    $hasError = false;

    while ($row = $result->fetch_assoc()) {
        $insertStmt->bind_param('sii', $row['food_name'], $row['quantity'], $row['table_number']);
        if (!$insertStmt->execute()) {
            $hasError = true;
            break;
        }
    }

    // 如果插入成功，刪除未出餐訂單中的紀錄
    if (!$hasError) {
        $deleteStmt = $conn->prepare("DELETE FROM unserved_orders WHERE table_number = ?");
        $deleteStmt->bind_param('i', $tableNumber);
        if ($deleteStmt->execute()) {
            echo "全部出餐成功！";
        } else {
            echo "刪除未出餐訂單時發生錯誤，請稍後再試。";
        }
    } else {
        echo "移動未出餐訂單時發生錯誤，請稍後再試。";
    }
}

$stmt = $conn->prepare("SELECT id, food_name, quantity FROM unserved_orders WHERE table_number = ?");
$stmt->bind_param('i', $tableNumber);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

	// 處理移除訂單的請求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_order'])) {
	$orderId = $_POST['order_id'];

	// 刪除未出餐訂單
	$deleteStmt = $conn->prepare("DELETE FROM unserved_orders WHERE id = ?");
	$deleteStmt->bind_param('i', $orderId);
	if ($deleteStmt->execute()) {
		echo "訂單已移除！";
		// 刪除後重新載入頁面
	} else {
		echo "刪除訂單失敗：" . $deleteStmt->error;
	}
}


$stmt = $conn->prepare("SELECT check_in_time FROM tables WHERE table_number = ?");
$stmt->bind_param('i', $tableNumber);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
	$checkInTime = $row['check_in_time'];
} else {
	echo "無法找到該桌的資料";
	exit();
}

// 讀取菜單資料
$menuResult = $conn->query("SELECT * FROM menu");

// 讀取已出餐訂單
$servedResult = $conn->query("SELECT * FROM served_orders where table_number={$tableNumber}");

// 讀取未出餐訂單
$unservedResult = $conn->query("SELECT * FROM unserved_orders where table_number={$tableNumber}");

// 更新倒計時
if (isset($_SESSION['remaining_time']) && $_SESSION['remaining_time'] > 0) {
    $_SESSION['remaining_time']--;
    if ($_SESSION['remaining_time'] === 0) {
        $canOrder = false; // 禁止點餐
    }
} else {
    $canOrder = false; // 禁止點餐
}


// 確認剩餘時間
if (isset($_SESSION['remaining_time']) && $_SESSION['remaining_time'] <= 0) {
    echo "時間已到，無法提交訂單！";
    exit();
}





?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>吃到飽點餐系統</title>
    <style>
        /* 基本樣式 */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
        }

        .timer {
            font-size: 1.5em;
            color: red;
            margin-bottom: 20px;
        }

        /* 容器樣式 */
        .container {
            display: flex;
            justify-content: space-between;
            width: 90%;
            gap: 20px;
        }

        /* 區塊樣式 */
        .section {
            flex: 1;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            background-color: #f9f9f9;
        }

        .section h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        .section table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .section table th,
        .section table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        .section table th {
            background-color: #f2f2f2;
        }

        /* 按鈕樣式 */
        button {
            padding: 10px 20px;
            margin-top: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* 彈跳視窗的樣式 */
        .modal {
            display: none;  /* 初始狀態隱藏 */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        /* 彈跳視窗內容 */
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            border-radius: 5px;
        }

        /* 關閉按鈕 */
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* 彈跳視窗內的表單 */
        .modal-form label {
            display: block;
            margin-bottom: 10px;
        }

        .modal-form select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 10px;
            border-radius: 5px;
        }

        .modal-form button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.2em;
        }

        .modal-form button:hover {
            background-color: #0056b3;
        }
    </style>

    <script>
        // 彈跳視窗顯示與隱藏的函數
        function showModal(foodName) {
            document.getElementById('modal').style.display = 'block';
            document.getElementById('food_name').value = foodName;
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('modal')) {
                closeModal();
            }
        }

       
				// 倒數計時的邏輯
       document.addEventListener('DOMContentLoaded', function () {
            const checkInTime = new Date("<?= $checkInTime ?>").getTime(); // 從 PHP 獲取 check_in_time
            const countDownDuration = 90* 60 * 1000; // 90 分鐘 (毫秒)

            function updateCountdown() {
                const now = new Date().getTime();
                const elapsed = now - checkInTime;
                const remaining = countDownDuration - elapsed;

                if (remaining > 0) {
					const hours = Math.floor((remaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)); // 計算小時
                    const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60)); //分鐘
                    const seconds = Math.floor((remaining % (1000 * 60)) / 1000); //秒

                    document.getElementById('countdown').textContent = `${hours} 小時 ${minutes} 分 ${seconds} 秒`;
                } else{
                    document.getElementById('countdown').textContent = "已超過 90 分鐘！";

					
					// 隱藏按鈕
						const selectButtons = document.querySelectorAll('button[onclick^="showModal"]'); // 選擇按鈕
						const serveAllButton = document.querySelector('button[name="serve_all"]'); // 全部出餐按鈕
				
						selectButtons.forEach(button => {
							button.style.display = 'none'; // 隱藏選擇按鈕
						});
				
						if (serveAllButton) {
							serveAllButton.style.display = 'none'; // 隱藏全部出餐按鈕
						}
					
							window.location.href = "Check_out.php";
                }
				
				if (remaining <= 30 * 60 * 1000) { // 剩餘時間 <= 30 分鐘
					if (typeof canOrder === 'undefined' || canOrder) { // 若尚未執行過
						canOrder = false;  // 禁止點餐
				
						// 隱藏按鈕
						const selectButtons = document.querySelectorAll('button[onclick^="showModal"]'); // 選擇按鈕
						const serveAllButton = document.querySelector('button[name="serve_all"]'); // 全部出餐按鈕
				
						selectButtons.forEach(button => {
							button.style.display = 'none'; // 隱藏選擇按鈕
						});
				
						if (serveAllButton) {
							serveAllButton.style.display = 'none'; // 隱藏全部出餐按鈕
						}
				
						document.getElementById('countdown').textContent = "時間已超過，無法點餐";
						alert("時間已超過60分，無法進行點餐！");
					}
				}
				
            }
			

            updateCountdown(); // 初次調用
            const timer = setInterval(updateCountdown, 1000); // 每秒更新一次
        });


    </script>
	
</head>

<body>
    <h1>吃到飽點餐系統</h1>
    <p class="timer">用餐剩餘時間：<span id="countdown"></span></p>

    <div class="container">
        <!-- 左側: 菜單 -->
        <div class="section">
            <h2>菜單</h2>
            <table>
                <tr>
                    <th>餐點名稱</th>
                    <th>單價 (NT$)</th>
                    <th>操作</th>
                </tr>
                <?php while ($row = $menuResult->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['food_name']) ?></td>
                    <td><?= htmlspecialchars($row['price']) ?></td>
                    <td>
                        <button onclick="showModal('<?= htmlspecialchars($row['food_name']) ?>')">選擇</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- 中間: 未出餐訂單 -->
        <div class="section">
            <h2>未出餐訂單</h2>
            <table>
                <tr>
                    <th>餐點名稱</th>
                    <th>數量</th>
					<th>操作</th>
                </tr>
                <?php while ($row = $unservedResult->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['food_name']) ?></td>
                    <td><?= htmlspecialchars($row['quantity']) ?></td>
					<td>
						<form method="POST">
							<input type="hidden" name="order_id" value="<?= $row['id'] ?>">
							<button type="submit" name="remove_order">移除</button>
						</form>
					</td>
                </tr>
                <?php endwhile; ?>
            </table>
            <form method="POST">
                <button type="submit" name="serve_all" <?php if ($unservedResult->num_rows == 0) echo 'style="display:none;"'; ?>>全部出餐</button>
            </form>
        </div>

        <!-- 右側: 已出餐訂單 -->
        <div class="section">
            <h2>已出餐訂單</h2>
            <table>
                <tr>
                    <th>餐點名稱</th>
                    <th>數量</th>
                    <th>出餐時間</th>
                </tr>
                <?php while ($row = $servedResult->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['food_name']) ?></td>
                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                    <td><?= htmlspecialchars($row['order_at']) ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <!-- 彈跳視窗 -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>選擇數量</h3>
            <form method="POST" class="modal-form">
                <input type="hidden" id="food_name" name="food_name">
                <label for="quantity">數量：</label>
                <select id="quantity" name="quantity" required>
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" name="order_food">確認訂單</button>
            </form>
        </div>
    </div>

    <h1>歡迎 <?= htmlspecialchars($_SESSION['username']) ?>！</h1>
    <a href="logout.php">
    <button class="logout-btn">登出</button>
    </a>
</body>
</html>

