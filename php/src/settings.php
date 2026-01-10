<?php
include 'connect.php';

// Fetch current setting (Admin's wallet settings - User ID 2)
$sql = "SELECT * FROM credit_setting WHERE user_id = 2";
$result = $conn->query($sql);
$setting = $result->fetch_assoc();

$current_min = $setting['credit_min'] ?? 10000;
?>

<div class="content-body" style="padding-top: 20px;">
    <!-- Main Card Container -->
    <div style="background: white;height: 100%; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);  margin: 0 auto; position: relative; border: 1px solid #eee;">

        <!-- Header Section -->
        <div style="margin-bottom: 30px;">
             <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h2 style="font-size: 26px; font-weight: 700; color: #000; margin-bottom: 5px;">การตั้งค่าเครดิตขั้นต่ำ</h2>
                </div>
             </div>
        </div>
        
        <?php 
        $channels = explode(',', $setting['notify_channels'] ?? 'dashboard');
        ?>
        <form action="action/setting_update_db.php" method="post">
            <!-- Form Group 1: Credit Limit -->
            <div class="form-group" style="margin-bottom: 40px;">
                <label class="form-label" style="font-size: 16px; color: #555; margin-bottom: 10px;">เกณฑ์เครดิตขั้นต่ำ</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <input type="number" name="credit_min" 
                           value="<?php echo $current_min; ?>" 
                           class="form-control" 
                           style="width: 250px; font-size: 24px; font-weight: 600; padding: 10px 15px; height: auto;" 
                           min="0" required>
                    
                    <?php if(isset($_GET['success'])): ?>
                        <div style="
                            background-color: #00c853; 
                            color: white; 
                            padding: 8px 15px; 
                            border-radius: 6px; 
                            font-size: 16px; 
                            font-weight: 500;
                            display: flex; 
                            align-items: center;
                            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
                            font-size: 16px; 
                            font-weight: 500;
                            display: flex; 
                            align-items: center;
                            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        ">
                            <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form Group 2: Notifications Removed -->

            <!-- Submit Button -->
            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" class="btn-primary" style="padding: 10px 40px; font-size: 16px; border-radius: 6px;">บันทึก</button>
            </div>
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
