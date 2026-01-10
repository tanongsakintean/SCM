<?php
include 'connect.php';

// Fetch Customers
$customers_result = $conn->query("SELECT * FROM customer");

// Fetch System Credit Balance (from Admin - User ID 1)
$credit_res = $conn->query("SELECT credit_balance FROM credit_setting WHERE user_id = 2");
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
                <div style="flex-grow: 1;">
                        <select name="customer_id" id="customerSelect" class="form-control" required style="width: 100%;">
                            <option value="">เลือกลูกค้า</option>
                            <?php 
                            if ($customers_result->num_rows > 0) {
                                while($cust = $customers_result->fetch_assoc()) {
                                    $balance_text = $cust['credit_balance'] > 0 ? " (เครดิต: " . number_format($cust['credit_balance']) . ")" : "";
                                    echo '<option value="'.$cust['customer_id'].'">'.$cust['customer_name'] . $balance_text . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <button type="button" class="btn-primary" style="white-space: nowrap; padding: 10px 30px;" onclick="$('#addCustomerModal').modal('show')">เพิ่มลูกค้าใหม่</button>
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

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 20px;">
                <h5 class="modal-title" style="font-weight: 600; color: #333;"><i class="fas fa-user-plus" style="color: #0066ff; margin-right: 10px;"></i> เพิ่มลูกค้าใหม่</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="action/add_customer_db.php" method="post">
                <div class="modal-body" style="padding: 25px;">
                    <div class="form-group">
                        <label>ชื่อลูกค้า <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                        <input type="text" name="customer_phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>อีเมล</label>
                        <input type="email" name="customer_email" class="form-control">
                    </div>
                </div>
                <div class="modal-footer" style="background-color: #f8f9fa; border-top: 1px solid #eee; padding: 15px 25px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#customerSelect').select2({
        theme: 'bootstrap4',
        placeholder: "ค้นหาและเลือกลูกค้า",
        allowClear: true
    });
});

function calculateTotal() {
    const credit = parseFloat(document.getElementById('sale_credit').value) || 0;
    const price = parseFloat(document.getElementById('sale_price').value) || 0;
    const total = credit * price;
    
    document.getElementById('totalDisplay').innerText = total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('sale_amount').value = total.toFixed(2);
}

// Check for success add param
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('success_add')) {
    Swal.fire({
        icon: 'success',
        title: 'เพิ่มลูกค้าสำเร็จ',
        showConfirmButton: false,
        timer: 1500
    });
}
</script>
