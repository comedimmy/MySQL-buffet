<?php
session_start();
require 'db_connection.php';

// 處理訂位表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_table'])) {
    $lastName = $_POST['last_name']; // 貴姓
    $reservationTime = $_POST['reservation_time']; // 預定時間
    $phoneNumber = $_POST['phone_number']; // 電話號碼
    $diners = $_POST['diners']; // 用餐人數
    $tableNumber = $_GET['table_number']; // 桌號
	$totalAmount = $diners * 399;
    // 檢查姓氏是否為單一字元
    if (mb_strlen($lastName, 'UTF-8') !== 1) {
        $_SESSION['message'] = "貴姓必須為一個字。";
        header("Location: book_a_table.php?table_number={$tableNumber}");
        exit();
    }

    // 檢查預定時間是否不超過現在時間
    if (strtotime($reservationTime) < time()) {
        $_SESSION['message'] = "預定時間不能早於現在的時間。";
        header("Location: book_a_table.php?table_number={$tableNumber}");
        exit();
    }

    // 檢查電話號碼是否符合格式
    if (!preg_match('/^09\d{2}-?\d{3}-?\d{3}$/', $phoneNumber)) {
        $_SESSION['message'] = "電話號碼必須為 09xx-xxx-xxx 格式，且為10個數字。";
        header("Location: book_a_table.php?table_number={$tableNumber}");
        exit();
    }

    // 檢查人數是否超過限制
    if ($diners < 1 || $diners > 12) {
        $_SESSION['message'] = "人數必須在 1 到 12 人之間。";
        header("Location: book_a_table.php?table_number={$tableNumber}");
        exit();
    }
	// 將訂位資料存入資料庫
	$stmt = $conn->prepare("UPDATE tables SET last_name = ?, reservation_time = ?, phone_number = ?, diners_count = ?,total_amount= ? WHERE table_number = ?");
	$stmt->bind_param('sssiii', $lastName, $reservationTime, $phoneNumber, $diners,$totalAmount, $tableNumber);
	
	if ($stmt->execute()) {
		// 訂位成功，更新該桌的狀態為 reserved
		$updateStatusStmt = $conn->prepare("UPDATE tables SET status = 'reserved' WHERE table_number = ?");
		$updateStatusStmt->bind_param('i', $tableNumber); // 綁定桌號
		$updateStatusStmt->execute(); // 更新桌狀態為 reserved
		
		$_SESSION['message'] = "訂位成功！";
		header("Location: table_management.php?table_number={$tableNumber}");
		exit();
	} else {
		$_SESSION['message'] = "訂位失敗，請稍後再試。";
		header("Location: book_a_table.php?table_number={$tableNumber}");
		exit();
	}
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>桌號 <?= htmlspecialchars($_GET['table_number']) ?> 訂位</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            height: 100vh;
            background-color: #f9f9f9;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        label {
            margin-top: 10px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .back-button {
            margin-top: 10px;
            background-color: #ccc;
            color: black;
        }
        .back-button:hover {
            background-color: #bbb;
        }
        p {
            color: red;
            font-size: 14px;
        }
    </style>
    <script>
        // JavaScript 檢查姓氏的長度，限制輸入一個字
        function checkLastNameLength(input) {
            if (input.value.length > 1) {
                alert("貴姓必須為一個字。");
                input.value = input.value.slice(0, 1); // 只保留第一個字
            }
        }
    </script>
</head>
<body>
    <h1>桌號 <?= htmlspecialchars($_GET['table_number']) ?> 訂位</h1>

    <?php
    if (isset($_SESSION['message'])) {
        echo "<p>{$_SESSION['message']}</p>";
        unset($_SESSION['message']); // 顯示後刪除訊息，防止下次加載時重複顯示
    }
    ?>

    <form method="POST">
        <label for="last_name">貴姓</label>
        <input type="text" id="last_name" name="last_name" maxlength="1" required oninput="checkLastNameLength(this)">

        <label for="reservation_time">預定時間</label>
        <input type="datetime-local" id="reservation_time" name="reservation_time" required>

        <label for="phone_number">電話號碼</label>
        <input type="text" id="phone_number" name="phone_number" required placeholder="09xx-xxx-xxx" 
               pattern="09\d{2}-?\d{3}-?\d{3}" title="請輸入有效的電話號碼" />

        <label for="diners">用餐人數</label>
        <input type="number" id="diners" name="diners" min="1" max="12" required>

        <button type="submit" name="book_table">確定訂位</button>
    </form>

    <button class="back-button" type="button" onclick="window.location.href='table_management.php?table_number=<?= $_GET['table_number'] ?>';">
        返回管理頁面
    </button>
</body>
</html>
