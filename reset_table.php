<?php
// reset_table.php
session_start();

// 確認是否有桌號
if (isset($_SESSION['table_number'])) {
    $tableNumber = $_SESSION['table_number'];
    require 'db_connection.php';

    // 開始處理資料庫更新
    $conn->begin_transaction();

    try {
        // 更新 tables 資料
        $stmt = $conn->prepare("UPDATE tables SET status = 'vacant', check_in_time = NULL, diners_count = 0, total_amount = 0 WHERE table_number = ?");
        $stmt->bind_param('i', $tableNumber);
        $stmt->execute();

        // 清空已出餐訂單與未出餐訂單
        $conn->query("DELETE FROM served_orders WHERE table_number = {$tableNumber}");
        $conn->query("DELETE FROM unserved_orders WHERE table_number = {$tableNumber}");

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
