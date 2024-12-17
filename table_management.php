<?php
session_start();
require 'db_connection.php'; 

// 確保有桌號參數
if (!isset($_GET['table_number']) || !in_array($_GET['table_number'], [1,2,3,4,5,6,7,8,9,10])) {
    echo "無效的桌號";
    exit();
}

$tableNumber = $_GET['table_number']; // 獲取桌號

// 處理用餐人數設定
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_diners'])) {
    $diners = $_POST['diners']; // 用餐人數
    $totalAmount = $diners * 399; // 計算金額，假設每人399元

	$_SESSION['message'] = "設置成功！該桌人數 {$diners} 人，每人399，應收金額為 NT$ {$totalAmount}";
    // 更新該桌的用餐狀況和人數
    $stmt = $conn->prepare("UPDATE tables SET status = 'occupied', diners_count = ?, total_amount = ?,check_in_time=now() WHERE table_number = ? ");
    $stmt->bind_param('iii', $diners, $totalAmount, $tableNumber);
    if ($stmt->execute()) {
        header("Location: table_management.php?table_number={$tableNumber}");
        exit(); // 重定向後退出，避免再執行後續代碼
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="refresh" content="60"> <!-- 每60秒刷新一次頁面 -->
    <title>桌號 <?= $tableNumber ?> 管理頁面</title>
</head>
<body>
    <h1>桌號 <?= $tableNumber ?> 管理頁面</h1>

	<!-- 顯示訊息 -->
    <?php
    if (isset($_SESSION['message'])) {
        echo "<p>{$_SESSION['message']}</p>";
        unset($_SESSION['message']); // 顯示後刪除訊息，防止下次加載時重複顯示
    }
    ?>

    <!-- 用餐人數設定表單 -->
    <form method="POST">
        <label for="diners">用餐人數：</label>
        <input type="number" id="diners" name="diners" min="1" required>
        <button type="submit" name="set_diners">設定用餐人數</button>
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
            echo "餐點已標記為送達！";
            // 重定向回頁面以刷新餐點清單
            header("Location: table_management.php?table_number={$tableNumber}");
            exit();
        } else {
            echo "標記送達失敗，請稍後再試。";
        }
    }
    ?>
	<button type="button" onclick="window.location.href='admin.php';">返回主介面</button>
</body>
</html>
