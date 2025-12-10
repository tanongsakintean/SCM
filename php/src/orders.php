<?php
include 'connect.php'; 

// Fetch Agents
$agents_result = $conn->query("SELECT * FROM agent");

// Fetch Categories
$categories_result = $conn->query("SELECT * FROM category");

// Fetch Order History (Your orders if Staff, All if Admin/Manager?)
// Let's assume Staff sees their own, others see all? Or simplistic: All see all for now as per "History" usually implies system history or personal.
// Given requirement: "Staff: Order Credit", "Manager: Approve".
// Let's filter by user_id for Staff, show all for Admin/Manager?
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$sql_orders = "SELECT pc.*, a.agent_name, c.category_name 
               FROM purchase_credit pc 
               JOIN agent a ON pc.agent_id = a.agent_id
               JOIN category c ON pc.category_id = c.category_id";

if ($role == 'Staff') {
    $sql_orders .= " WHERE pc.user_id = $user_id";
}
$sql_orders .= " ORDER BY pc.order_date DESC, pc.order_id DESC";
$orders_result = $conn->query($sql_orders);
?>

<div class="content-body">
    
    <?php if ($role == 'Staff' || $role == 'Admin'): ?>
    <!-- Order Form Card -->
    <div class="card" style="margin-bottom: 2rem; max-width: 800px;">
        <div style="font-size: 14px; color: #888; margin-bottom: 1rem;">(ออเดอร์ใหม่)</div>
        <form action="action/order_create_db.php" method="post">
            <div class="form-group">
                <label class="form-label">ปริมาณการสั่งซื้อ</label>
                <input type="number" name="order_quantity" class="form-control" placeholder="เช่น 100000" required>
            </div>

            <div class="form-group">
                <label class="form-label">เลือกประเภท</label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- เลือกประเภท --</option>
                    <?php while($cat = $categories_result->fetch_assoc()): ?>
                        <option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['category_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">ชื่อซัพพลายเออร์</label>
                <select name="agent_id" class="form-control" required>
                    <option value="">-- เลือกซัพพลายเออร์ --</option>
                    <?php while($agent = $agents_result->fetch_assoc()): ?>
                        <option value="<?php echo $agent['agent_id']; ?>"><?php echo $agent['agent_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn-primary">ส่งคำสั่งซื้อ</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Order History Table -->
    <div class="card" style="max-width: 800px;">
        <h5 style="margin-bottom: 1rem; font-size: 16px;">ประวัติการสั่งซื้อ</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>รหัสคำสั่งซื้อ</th>
                    <th>ซัพพลายเออร์</th>
                    <th>ปริมาณ</th>
                    <th>วันที่</th>
                    <th>สถานะ</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders_result->num_rows > 0): ?>
                    <?php while($row = $orders_result->fetch_assoc()): ?>
                        <?php 
                            $status_class = '';
                            if ($row['order_status'] == 'Pending') $status_class = 'status-pending';
                            elseif ($row['order_status'] == 'Approved') $status_class = 'status-approved';
                            elseif ($row['order_status'] == 'Rejected') $status_class = 'status-rejected';
                            // Map 'Approved' to 'ได้รับการอนุมัติ' if desire Thai UI, but let's stick to simple
                            $status_text = $row['order_status'];
                            if ($row['order_status'] == 'Pending') $status_text = 'รอดำเนินการ';
                            elseif ($row['order_status'] == 'Approved') $status_text = 'อนุมัติแล้ว';
                            elseif ($row['order_status'] == 'Rejected') $status_text = 'ถูกปฏิเสธ';
                        ?>
                        <tr>
                            <td>#<?php echo $row['order_id']; ?></td>
                            <td><?php echo $row['agent_name']; ?></td>
                            <td><?php echo number_format($row['order_quantity']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['order_date'])); ?></td>
                            <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">ไม่พบข้อมูล</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
