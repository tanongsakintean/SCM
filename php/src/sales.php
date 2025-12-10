<?php
include 'connect.php';

// Fetch Customers
$customers_result = $conn->query("SELECT * FROM customer");

// Fetch System Credit Balance (from Admin - User ID 1)
$credit_res = $conn->query("SELECT credit_balance FROM credit_setting WHERE user_id = 1");
$credit_row = $credit_res->fetch_assoc();
$current_credit = $credit_row['credit_balance'] ?? 0;
?>


<div class="content-body">
    <div class="card" style="max-width: 900px; padding: 40px;">
        
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> บันทึกการขายสําเร็จ!
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> เกิดข้อผิดพลาด: <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form action="action/sale_save_db.php" method="post">
            <!-- Customer Selection Row -->
            <div class="form-group" style="margin-bottom: 2rem;">
                <div style="display: flex; gap: 15px;">
                    <div style="flex-grow: 1;" class="custom-select-wrapper">
                        <select name="customer_id" class="form-control" required>
                            <option value="">เลือกลูกค้า</option>
                            <?php 
                            if ($customers_result->num_rows > 0) {
                                while($cust = $customers_result->fetch_assoc()) {
                                    echo '<option value="'.$cust['customer_id'].'">'.$cust['customer_name'].'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <button type="button" class="btn-primary" style="white-space: nowrap; padding: 10px 30px;">เพิ่มลูกค้าใหม่</button>
                </div>
            </div>

            <!-- Main Split Layout -->
            <div style="display: flex; flex-wrap: wrap; margin-top: 30px;">
                
                <!-- Left Column: Summary & Action -->
                <div style="width: 35%; padding-right: 20px;">
                    <div style="margin-bottom: 15px;">
                        <label class="form-label" style="font-weight: 600;">ยอดรวม (บาท)</label>
                        <h1 id="totalDisplay" style="margin: 5px 0 20px 0; font-size: 36px; font-weight: 500;">0.00</h1>
                    </div>
                    
                    <button type="submit" class="btn-primary" style="width: 100%; margin-bottom: 15px;">บันทึกการขาย</button>
                    
                    <div style="margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
                        <small style="color: #666;">เครดิตคงเหลือในระบบ:</small>
                        <div style="font-weight: bold; color: #0066ff;"><?php echo number_format($current_credit); ?> เครดิต</div>
                    </div>
                </div>

                <!-- Right Column: Inputs Grid -->
                <div style="width: 65%; padding-left: 20px; border-left: 1px solid #eee;">
                    <div style="margin-bottom: 20px;">
                        <label class="form-label" style="font-weight: 600; font-size: 16px;">ข้อมูลการขาย</label>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6" style="margin-bottom: 15px;">
                            <label class="form-label" style="font-size: 14px;">จำนวนเครดิตที่ขาย</label>
                            <input type="number" name="sale_credit" id="sale_credit" class="form-control" placeholder="เช่น 5000" required oninput="calculateTotal()">
                        </div>
                        <div class="col-md-6" style="margin-bottom: 15px;">
                            <label class="form-label" style="font-size: 14px;">ราคาต่อหน่วย (บาท)</label>
                            <input type="number" name="sale_price" id="sale_price" step="0.01" class="form-control" placeholder="เช่น 0.85" required oninput="calculateTotal()">
                        </div>
                        
                        <!-- Hidden input for total amount calculation result to be sent -->
                        <input type="hidden" name="sale_amount" id="sale_amount" value="0">

                        <div class="col-md-12" style="margin-bottom: 15px;">
                            <label class="form-label" style="font-size: 14px;">วิธีการชำระเงิน</label>
                            <select class="form-control">
                                <option>เงินสด</option>
                                <option>โอนเงิน</option>
                                <option>บัตรเครดิต</option>
                            </select>
                        </div>
                        
                        <div class="col-md-12">
                             <label class="form-label" style="font-size: 14px;">หมายเหตุ</label>
                             <textarea name="note" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
function calculateTotal() {
    const credit = parseFloat(document.getElementById('sale_credit').value) || 0;
    const price = parseFloat(document.getElementById('sale_price').value) || 0;
    const total = credit * price;
    
    document.getElementById('totalDisplay').innerText = total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('sale_amount').value = total.toFixed(2);
}
</script>
