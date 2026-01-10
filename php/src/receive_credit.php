<?php
include 'connect.php';

// Fetch Approved Orders
$sql_approved = "SELECT pc.*, a.agent_name 
                FROM purchase_credit pc 
                JOIN agent a ON pc.agent_id = a.agent_id 
                WHERE pc.order_status = 'Approved' 
                ORDER BY pc.order_date ASC";
$result_approved = $conn->query($sql_approved);
?>

<div class="content-body">
    <div class="card" style="margin-bottom: 2rem; max-width: 900px; padding-bottom: 0;">
        <h5 style="margin-bottom: 1rem; padding-left: 15px;">รายการที่ต้องรับเครดิต</h5>
        <div class="table-responsive">
            <table class="table" style="margin-top: 0;">
                <thead>
                    <tr style="background-color: transparent;">
                        <th>เลขที่ใบเสนอราคา</th>
                        <th>ซัพพลายเออร์</th>
                        <th>ปริมาณ</th>
                        <th>วันที่อนุมัติ</th> <!-- ideally this would be approval_date from approve table, but order_date is fine for listing -->
                        <th>สถานะ</th>
                        <th>ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_approved->num_rows > 0): ?>
                        <?php while($row = $result_approved->fetch_assoc()): ?>
                        <tr style="background-color: #f8f9fa;">
                            <td>#<?php echo $row['order_number']; ?></td>
                            <td><?php echo $row['agent_name']; ?></td>
                            <td><?php echo number_format($row['order_quantity']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['order_date'])); ?></td>
                            <td><span class="badge badge-success">อนุมัติ</span></td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm btn-receive-trigger" 
                                    data-id="<?php echo $row['order_id']; ?>"
                                    data-agent="<?php echo htmlspecialchars($row['agent_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-qty="<?php echo $row['order_quantity']; ?>">
                                    <i class="fas fa-file-import"></i> รับเครดิต
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center" style="color:#aaa;">ไม่มีรายการที่ต้องดำเนินการ</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Receive Credit Modal -->
<div class="modal fade" id="receiveModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="action/receive_credit_db.php" method="post" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title">บันทึกการรับเครดิต</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="order_id" id="receive_order_id">
            
            <div class="alert alert-info">
                <strong>รายการ:</strong> #<span id="disp_id"></span><br>
                <strong>Supplier:</strong> <span id="disp_agent"></span><br>
                <strong>จำนวนเครดิต:</strong> <span id="disp_qty"></span>
            </div>

            <div class="form-group">
                <label>แนบหลักฐานการโอน/ใบเสร็จ <span class="text-danger">*</span></label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" name="receipt_proof" id="receiptFile" required accept="image/*, .pdf">
                    <label class="custom-file-label" for="receiptFile">เลือกไฟล์...</label>
                </div>
                <small class="text-muted">รองรับไฟล์ JPG, PNG, PDF</small>
            </div>
            
            <div class="form-group">
                 <p class="text-muted" style="font-size: 0.9rem;">
                    <i class="fas fa-info-circle"></i> เมื่อบันทึกแล้ว ยอดเครดิตจะถูกเพิ่มเข้าสู่ระบบทันที
                 </p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
            <button type="submit" class="btn btn-primary">ยืนยันรับเครดิต</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
// Event Delegation for "Receive Credit" buttons
document.addEventListener('click', function(e) {
    // Find the closest button element (in case user clicks icon inside)
    var btn = e.target.closest('.btn-receive-trigger');
    if (btn) {
        var id = btn.getAttribute('data-id');
        var agent = btn.getAttribute('data-agent');
        var qty = btn.getAttribute('data-qty');
        
        // Debug check
        if (!id) {
            alert('Error: Order ID missing from button attribute.');
            return;
        }

        document.getElementById('receive_order_id').value = id;
        document.getElementById('disp_id').innerText = id;
        document.getElementById('disp_agent').innerText = agent;
        document.getElementById('disp_qty').innerText = new Intl.NumberFormat().format(qty);
        
        // Reset file from previous
        var fileParams = document.getElementById('receiptFile'); 
        if(fileParams) fileParams.value = '';
        var lbl = document.querySelector('.custom-file-label');
        if(lbl) {
            lbl.classList.remove('selected');
            lbl.innerHTML = 'เลือกไฟล์...';
        }

        $('#receiveModal').modal('show');
    }
});

// Custom file input label change
// Custom file input label change (Vanilla JS to avoid jQuery loading order issues)
document.addEventListener('change', function(e) {
    if (e.target && e.target.classList.contains('custom-file-input')) {
        var fileName = e.target.value.split('\\').pop();
        var label = e.target.nextElementSibling;
        
        if (label && label.classList.contains('custom-file-label')) {
            label.classList.add("selected");
            label.innerHTML = fileName;
        }
    }
});
</script>

<?php if(isset($_GET['success'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'บันทึกข้อมูลสำเร็จ',
        text: 'ยอดเครดิตถูกเพิ่มเข้าสู่ระบบเรียบร้อยแล้ว',
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            // Optional: Clean URL
            window.location.href = 'index.php?p=receive_credit';
        }
    });
</script>
<?php endif; ?>
