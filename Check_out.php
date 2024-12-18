<?php
session_start();
require 'db_connection.php';  // 假設這裡包含的是 $conn 的連接設定

if (!isset($_SESSION['table_number'])) {
    echo "無法辨識桌號，請重新掃描 QR Code！";
    exit();
}

$tableNumber = $_SESSION['table_number'];
// 取得已出餐訂單
$stmt = $conn->prepare("SELECT food_name, quantity FROM served_orders WHERE table_number = ?");
$stmt->bind_param('i', $tableNumber);
$stmt->execute();
$servedResult = $stmt->get_result();

// 取得總金額
$stmt2 = $conn->prepare("SELECT total_amount FROM tables WHERE table_number = ?");
$stmt2->bind_param('i', $tableNumber);
$stmt2->execute();
$result = $stmt2->get_result();
$totalAmount = $result->fetch_assoc()['total_amount'] ?? 0;

// 如果使用者不是訪客，計算打九折的金額
$discountMessage = '';
if (isset($_SESSION['username']) && $_SESSION['username'] !== '訪客') {
    $totalAmount = round($totalAmount * 0.9, 0); // 打九折並四捨五入到小數點後兩位
    $discountMessage = '套用會員優惠九折！'; // 顯示折扣訊息
}

if ($tableNumber) {
    // 清除總金額、已出餐清單和未出餐清單
    $stmt3 = $conn->prepare(
	"UPDATE tables SET 
	total_amount = 0,status='vacant', diners_count=0,reservation_time=NULL,check_in_time=NULL,Last_name='' ,phone_number='0900-000000'
	WHERE table_number = ?"
	);
    $stmt3->bind_param('i', $tableNumber);
    $stmt3->execute();

    // 刪除已出餐訂單
    $stmt4 = $conn->prepare("DELETE FROM served_orders WHERE table_number = ?");
    $stmt4->bind_param('i', $tableNumber);
    $stmt4->execute();

    // 刪除未出餐訂單
    $stmt5 = $conn->prepare("DELETE FROM unserved_orders WHERE table_number = ?");
    $stmt5->bind_param('i', $tableNumber);
    $stmt5->execute();
	$message = " ";
} else {
    $message = "無法找到對應的桌號";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>結帳資訊</title>
    <style>
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
		
        body { 
            font-family: Arial, sans-serif; 
            text-align: center; 
            margin: 0; 
            padding: 0; 
        }
        h1, h2 { 
            margin-top: 20px; 
        }
        table { 
            margin: auto; 
            border-collapse: collapse; 
            width: 50%; 
        }
        th, td {
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: center; 
        }
        th { background-color: #f2f2f2; }
        p { font-size: 1.2em; }
        .amount { color: red; font-size: 1.5em; font-weight: bold; }
        .discount-message {
            color: green;
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>請至櫃檯結帳</h1>
    <h2>桌號：<?= htmlspecialchars($_SESSION['table_number']) ?></h2>

    <?php if ($discountMessage): ?>
        <p class="discount-message"><?= $discountMessage ?></p>
    <?php endif; ?>

    <h3>點餐內容：</h3>
    <table>
        <tr>
            <th>餐點名稱</th>
            <th>數量</th>
        </tr>
        <?php while ($row = $servedResult->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['food_name']) ?></td>
            <td><?= htmlspecialchars($row['quantity']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <p>應付金額： <span class="amount">NT$ <?= htmlspecialchars($totalAmount) ?></span></p>
    <p><?= $message ?></p>
    <button class="back-button" type="button" onclick="window.location.href='index.php';">返回登入畫面</button>
</body>
</html>
