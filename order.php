<?php
session_start();
// 設置剩餘時間（例如 90 分鐘 = 5400 秒）
if (!isset($_SESSION['remaining_time'])) {
    $_SESSION['remaining_time'] = 5400; // 90 分鐘
}

// 連接資料庫
require 'db_connection.php';

// 處理提交訂單
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['place_order'])) {
        // 提交訂單
        $foodName = $_POST['food_name'];
        $quantity = intval($_POST['quantity']);
        $stmt = $conn->prepare("INSERT INTO unserved_orders (food_name, quantity) VALUES (?, ?)");
        $stmt->bind_param("si", $foodName, $quantity);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['remove_order'])) {
        // 移除未出餐訂單
        $orderId = intval($_POST['order_id']);
        $stmt = $conn->prepare("DELETE FROM unserved_orders WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $stmt->close();
    }
}

// 讀取菜單資料
$menuResult = $conn->query("SELECT * FROM menu");

// 讀取未出餐訂單
$orderResult = $conn->query("SELECT * FROM unserved_orders");

// 更新倒計時
if (isset($_SESSION['remaining_time']) && $_SESSION['remaining_time'] > 0) {
    $_SESSION['remaining_time']--;
    if ($_SESSION['remaining_time'] === 0) {
        $canOrder = false; // 禁止點餐
    }
} else {
    $canOrder = false; // 禁止點餐
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>吃到飽點餐系統</title>
    <script>
        let remainingTime = <?= $_SESSION['remaining_time'] ?>;
		
		function showDropdown(foodName) {
            document.getElementById('order_form').style.display = 'block';
            document.getElementById('food_name').value = foodName;
        }
        function updateTimer() {
            const timerLabel = document.getElementById("timer");
            if (remainingTime > 0) {
                remainingTime--;
                const minutes = Math.floor(remainingTime / 60);
                const seconds = remainingTime % 60;
                timerLabel.textContent = `剩餘時間: ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            } else {
                timerLabel.textContent = "時間已到，無法再點餐！";
                document.querySelectorAll("button.order-btn").forEach(button => button.disabled = true);
                clearInterval(timerInterval);
            }
        }

        const timerInterval = setInterval(updateTimer, 1000);
    </script>
</head>
<body>
    <h1>吃到飽點餐系統</h1>

    <!-- 倒計時 -->
    <h2 id="timer">剩餘時間: 90:00</h2>

    <h2>菜單</h2>
    <table border="1">
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
                <button onclick="showDropdown('<?= htmlspecialchars($row['food_name']) ?>')">選擇</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- 動態顯示下拉選單 -->
    <div id="order_form" style="display: none; margin-top: 20px;">
        <h3>選擇數量</h3>
        <form method="POST">
            <input type="hidden" id="food_name" name="food_name">
            <label for="quantity">數量：</label>
            <select id="quantity" name="quantity" required>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" name="place_order">確認訂單</button>
        </form>
    </div>

    <h2>未出餐訂單</h2>
    <table border="1">
        <tr>
            <th>訂單 ID</th>
            <th>餐點名稱</th>
            <th>數量</th>
            <th>操作</th>
        </tr>
        <?php while ($row = $orderResult->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['food_name']) ?></td>
            <td><?= htmlspecialchars($row['quantity']) ?></td>
            <td>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($row['id']) ?>">
                    <button type="submit" name="remove_order">移除</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
	<h1>歡迎 <?= htmlspecialchars($_SESSION['username']) ?>！</h1>
    <a href="logout.php">登出</a>
    <hr>
    <!-- 點餐系統內容 -->
</body>
</html>
