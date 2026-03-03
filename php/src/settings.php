<?php
include 'connect.php';

// Fetch current setting (Admin's wallet settings - User ID 2)
$sql = "SELECT * FROM credit_setting WHERE user_id = 2";
$result = $conn->query($sql);
$setting = $result->fetch_assoc();

$current_min = $setting['credit_min'] ?? 10000;
$current_balance = $setting['credit_balance'] ?? 0;

// Fetch last update history
$log_sql = "
    SELECT sl.timestamp, u.firstname, u.lastname, u.username 
    FROM system_log sl 
    LEFT JOIN user u ON sl.user_id = u.user_id 
    WHERE sl.action = 'Update Settings' 
    ORDER BY sl.timestamp DESC 
    LIMIT 1
";
$log_result = $conn->query($log_sql);
$last_update = null;
if ($log_result && $log_result->num_rows > 0) {
    $last_update = $log_result->fetch_assoc();
}
?>

<div class="content-body" style="padding-top: 20px;">
    <!-- Main Card Container -->
    <div style="background: white;height: 100%; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);  margin: 0 auto; position: relative; border: 1px solid #eee;">

        <!-- Header Section -->
        <div style="margin-bottom: 20px;">
             <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h2 style="font-size: 26px; font-weight: 700; color: #000; margin-bottom: 5px;">การตั้งค่าเครดิตขั้นต่ำ</h2>
                </div>
             </div>
        </div>
        
        <!-- Description Banner -->
        <div style="background-color: #f8f9fa; border-radius: 8px; padding: 16px 20px; font-size: 15px; color: #444; margin-bottom: 30px;">
            <span style="color: #6c757d; font-weight: 500; margin-right: 5px;">[จัดการระบบ]</span> ตั้งค่าเกณฑ์เครดิตขั้นต่ำที่ใช้ทั้งระบบ เพื่อแจ้งเตือนและพิจารณาคำสั่งซื้อเครดิต
        </div>
        
        <?php 
        $channels = explode(',', $setting['notify_channels'] ?? 'dashboard');
        ?>
        <form action="action/setting_update_db.php" method="post">
            <!-- Form Group 1: Credit Limit -->
            <div class="form-group" style="margin-bottom: 40px;">
                <label class="form-label" style="font-size: 16px; color: #333; font-weight: 600; margin-bottom: 15px;">เกณฑ์เครดิตขั้นต่ำ <span style="color: #dc3545;">*</span></label>
                
                <div style="display: flex; align-items: flex-start; gap: 30px; flex-wrap: wrap;">
                    
                    <!-- Left: Input & Help text -->
                    <div style="flex: 1; max-width: 400px;">
                        <input type="number" name="credit_min" 
                               value="<?php echo $current_min; ?>" 
                               class="form-control" 
                               style="width: 100%; font-size: 22px; font-weight: 500; padding: 12px 18px; height: auto;" 
                               min="0" required>
                        <div style="font-size: 13px; color: #777; margin-top: 8px;">
                            ระบบจะใช้เกณฑ์นี้เพื่อแจ้งเตือนเมื่อเครดิตคงเหลือต่ำกว่าที่กำหนด
                        </div>

                        <?php if(isset($_GET['success'])): ?>
                            <div style="
                                background-color: #00c853; 
                                color: white; 
                                padding: 8px 15px; 
                                border-radius: 6px; 
                                font-size: 15px; 
                                font-weight: 500;
                                margin-top: 15px;
                                display: inline-flex; 
                                align-items: center;
                            ">
                                <i class="fas fa-check-circle" style="margin-right: 8px;"></i> บันทึกเรียบร้อยแล้ว!
                            </div>
                        <?php endif; ?>
                        <?php if(isset($_GET['error'])): ?>
                            <div style="
                                background-color: #dc3545; 
                                color: white; 
                                padding: 8px 15px; 
                                border-radius: 6px; 
                                font-size: 15px; 
                                font-weight: 500;
                                margin-top: 15px;
                                display: inline-flex; 
                                align-items: center;
                            ">
                                <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Right: Current Balance Info Box -->
                    <div style="border: 1px solid #eaeaea; border-radius: 8px; padding: 20px 25px; background: #fff; min-width: 280px;">
                        <div style="font-size: 15px; margin-bottom: 12px; color: #333;">
                            เครดิตคงเหลือปัจจุบัน: <span style="font-weight: 600; font-size: 16px; margin-left: 5px;"><?php echo number_format($current_balance); ?></span>
                        </div>
                        <div style="font-size: 15px; color: #333; display: flex; align-items: center;">
                            สถานะ: 
                            <?php if ($current_balance >= $current_min): ?>
                                <span style="color: #10b981; font-weight: 600; margin-left: 8px; display: flex; align-items: center; gap: 5px;">
                                    <div style="width: 10px; height: 10px; border-radius: 50%; background-color: #10b981;"></div> ปกติ
                                </span>
                            <?php else: ?>
                                <span style="color: #ef4444; font-weight: 600; margin-left: 8px; display: flex; align-items: center; gap: 5px;">
                                    <div style="width: 10px; height: 10px; border-radius: 50%; background-color: #ef4444;"></div> ต่ำกว่าเกณฑ์
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn-primary" style="padding: 10px 40px; font-size: 16px; border-radius: 6px; background-color: #2563eb; border-color: #2563eb;">บันทึก</button>
            </div>
            
            <!-- Edit History -->
            <?php if ($last_update): 
                $editor_name = trim($last_update['firstname'] . ' ' . $last_update['lastname']);
                if (empty($editor_name)) $editor_name = $last_update['username'];
                $edit_date = date('d/m/y', strtotime($last_update['timestamp']));
            ?>
            <div style="text-align: center; margin-top: 30px; font-size: 13.5px; color: #718096; border-top: 1px solid #edf2f7; padding-top: 20px;">
                แก้ไขล่าสุดโดย <?php echo htmlspecialchars($editor_name); ?> เมื่อ <?php echo $edit_date; ?>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
    <?php if(isset($_GET['success'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'บันทึกสำเร็จ',
        text: 'ข้อมูลการตั้งค่าถูกบันทึกเรียบร้อยแล้ว',
        showConfirmButton: false,
        timer: 1500
    }).then(() => {
        // Optional: clear the query param
        window.history.replaceState(null, null, window.location.pathname + '?p=settings');
    });
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
    Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: '<?php echo htmlspecialchars($_GET['error']); ?>',
        confirmButtonText: 'ตกลง'
    });
    <?php endif; ?>
</script>
