<?php
include 'connect.php';

// Pending Orders
$sql_pending = "SELECT pc.*, a.agent_name 
                FROM purchase_credit pc 
                JOIN agent a ON pc.agent_id = a.agent_id 
                WHERE pc.order_status = 'Pending' 
                ORDER BY pc.order_date ASC";
$result_pending = $conn->query($sql_pending);

// History (Last 5 processed actions) from Approve table
// Join approve -> purchase_credit -> agent
$sql_history = "SELECT ap.*, pc.order_id, a.agent_name 
                FROM approve ap 
                JOIN purchase_credit pc ON ap.order_id = pc.order_id 
                JOIN agent a ON pc.agent_id = a.agent_id 
                ORDER BY ap.approval_date DESC, ap.approval_id DESC 
                LIMIT 5";
$result_history = $conn->query($sql_history);
?>

<div class="content-body">
    <!-- Approval Table -->
    <div class="card" style="margin-bottom: 2rem; max-width: 800px; padding-bottom: 0;">
        <h5 style="margin-bottom: 1rem; padding-left: 15px;">รายการรออนุมัติ</h5>
        <table class="table" style="margin-top: 0;">
            <thead>
                <tr style="background-color: transparent;">
                    <th style="background-color: transparent; border-bottom: none;">ซัพพลายเออร์</th>
                    <th style="background-color: transparent; border-bottom: none;">ปริมาณ</th>
                    <th style="background-color: transparent; border-bottom: none;">วันที่</th>
                    <th style="background-color: transparent; border-bottom: none;">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_pending->num_rows > 0): ?>
                    <?php while($row = $result_pending->fetch_assoc()): ?>
                    <tr style="background-color: #f8f9fa;">
                        <td><?php echo $row['agent_name']; ?></td>
                        <td><?php echo number_format($row['order_quantity']); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['order_date'])); ?></td>
                        <td>
                            <form action="action/order_approve_db.php" method="post" style="display:inline;">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <button type="submit" name="action" value="Approved" class="btn-icon" style="color: #28a745; border:none; background:none; cursor:pointer;" title="อนุมัติ">
                                    <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i>
                                </button>
                                <button type="submit" name="action" value="Rejected" class="btn-icon" style="color: #dc3545; border:none; background:none; cursor:pointer;" title="ปฏิเสธ">
                                    <i class="fas fa-times-circle" style="font-size: 1.2rem;"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center" style="color:#aaa;">ไม่มีรายการรออนุมัติ</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Status History Log -->
    <div class="card" style="max-width: 800px; padding: 0; overflow: hidden; background-color: transparent; box-shadow: none; border: none;">
        <h5 style="margin-bottom: 5px; font-size: 16px; color: #333;">ประวัติสถานะการสั่งซื้อล่าสุด</h5>
        <div style="background-color: #eef2f7; border-radius: 8px; overflow: hidden;">
            <div style="padding: 10px 15px; background-color: #e3effd; color: #666; font-size: 13px; font-weight: 600;">
                รายการล่าสุด
            </div>
            <ul class="history-log-list" style="margin-top: 0; padding: 10px;">
                <?php if ($result_history->num_rows > 0): ?>
                    <?php while($hist = $result_history->fetch_assoc()): ?>
                        <li class="history-log-item" style="list-style: none; border-bottom: 1px solid #ddd; padding: 8px 0;">
                            หมายเลขคำสั่งซื้อ #<?php echo $hist['order_id']; ?> (<?php echo $hist['agent_name']; ?>) - 
                            <?php if ($hist['approval_status'] == 'Approved'): ?>
                                <span style="color: #28a745; font-weight: bold;">อนุมัติ</span>
                            <?php else: ?>
                                <span style="color: #dc3545; font-weight: bold;">ถูกปฏิเสธ</span>
                            <?php endif; ?>
                            <span style="font-size: 0.8rem; color: #999;">(<?php echo date('d/m/Y', strtotime($hist['approval_date'])); ?>)</span>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="history-log-item" style="list-style: none; padding: 10px; color:#aaa;">ยังไม่มีประวัติ</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
