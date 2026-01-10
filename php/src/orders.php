<?php
include 'connect.php'; 

// Fetch Agents
$agents_result = $conn->query("SELECT * FROM agent");

// Fetch Categories
$categories_result = $conn->query("SELECT * FROM category");

// Fetch Order History
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$sql_orders = "SELECT pc.*, a.agent_name, c.category_name, u.username as requester_name 
               FROM purchase_credit pc 
               JOIN agent a ON pc.agent_id = a.agent_id
               JOIN category c ON pc.category_id = c.category_id
               LEFT JOIN user u ON pc.user_id = u.user_id";

if ($role == 'Staff') {
    $sql_orders .= " WHERE pc.user_id = $user_id";
}
$sql_orders .= " ORDER BY pc.order_date DESC, pc.order_id DESC";
$orders_result = $conn->query($sql_orders);
?>

<div class="content-body">
    
    <?php if ($role == 'Staff' || $role == 'Admin'): ?>
    <!-- Order Form Section -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">
            <h5 style="margin: 0; font-size: 18px; color: #333;"><i class="fas fa-cart-plus" style="margin-right: 10px; color: #0066ff;"></i>สร้างคำสั่งซื้อใหม่</h5>
        </div>
        
        <form action="action/order_create_db.php" method="post" enctype="multipart/form-data">
            <div class="form-row">
                <!-- Row 1: Category & Supplier -->
                <!-- Row 1: Supplier -->
                <div class="col-md-12">
                     <div class="form-group">
                        <label class="form-label">ซัพพลายเออร์ (Supplier) <span style="color: red;">*</span></label>
                        <div class="custom-select-wrapper">
                            <select name="agent_id" class="form-control" required>
                                <option value="">-- กรุณาเลือก --</option>
                                <?php while($agent = $agents_result->fetch_assoc()): ?>
                                    <option value="<?php echo $agent['agent_id']; ?>"><?php echo $agent['agent_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>


                <!-- Row 2: Quantity -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label">ปริมาณการสั่งซื้อ <span style="color: red;">*</span></label>
                        <input type="number" name="order_quantity" class="form-control" placeholder="เช่น 100000" required min="1">
                    </div>
                </div>


                <!-- Row 3: File & Note -->
                <!-- Attachment Field Removed -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label">หมายเหตุ</label>
                        <textarea name="order_note" class="form-control" rows="1" placeholder="ระบุรายละเอียดเพิ่มเติม..." style="height: 48px;"></textarea>
                    </div>
                </div>
            </div>

            <div style="margin-top: 10px; text-align: right;">
                <button type="reset" class="btn-secondary" style="margin-right: 10px;">ล้างค่า</button>
                <button type="submit" class="btn-primary"><i class="fas fa-paper-plane" style="margin-right: 5px;"></i> ส่งคำสั่งซื้อ</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Order History Section -->
    <div class="card">
        <!-- ... header code remains ... -->

        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 15%;">เลขที่ใบเสนอราคา</th>
                        <th style="width: 25%;">ซัพพลายเออร์</th>
                        <th style="width: 15%;">ปริมาณ</th>
                        <th style="width: 15%;">วันที่สั่งซื้อ</th>
                        <th style="width: 15%;">ชื่อผู้ที่ออกใบคำสั่งซื้อ</th>
                        <th style="width: 15%;">สถานะ</th>
                        <th style="width: 10%;">ดำเนินการ</th>
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
                                elseif ($row['order_status'] == 'Received') $status_class = 'status-received';
                                elseif ($row['order_status'] == 'Cancelled') $status_class = 'status-rejected'; // Use rejected style for cancelled

                                if ($row['order_status'] == 'Pending') $status_text = 'รอดำเนินการ';
                                elseif ($row['order_status'] == 'Approved') $status_text = 'อนุมัติแล้ว';
                                elseif ($row['order_status'] == 'Rejected') $status_text = 'ถูกปฏิเสธ';
                                elseif ($row['order_status'] == 'Received') $status_text = 'ได้รับเครดิตแล้ว';
                                elseif ($row['order_status'] == 'Cancelled') $status_text = 'ยกเลิกแล้ว';
                                
                                $display_id = !empty($row['order_number']) ? $row['order_number'] : str_pad($row['order_id'], 5, '0', STR_PAD_LEFT);
                            ?>
                            <tr>
                                <td>
                                    <a href="javascript:void(0);" onclick="viewOrderDetails(this)" 
                                       style="color: #0066ff; font-weight: 500;"
                                       data-real-id="<?php echo $row['order_id']; ?>"
                                       data-id="<?php echo $display_id; ?>"
                                       data-agent="<?php echo htmlspecialchars($row['agent_name']); ?>"
                                       data-category="<?php echo htmlspecialchars($row['category_name']); ?>"
                                       data-quantity="<?php echo number_format($row['order_quantity']); ?>"
                                       data-date="<?php echo date('d/m/Y H:i', strtotime($row['order_date'])); ?>"
                                       data-status="<?php echo $status_text; ?>"
                                       data-raw-status="<?php echo $row['order_status']; ?>"
                                       data-note="<?php echo htmlspecialchars($row['order_note'] ?? '-'); ?>"
                                       data-attachment="<?php echo htmlspecialchars($row['order_attachment'] ?? ''); ?>"
                                       data-receipt="<?php echo !empty($row['receipt_proof']) ? 'assets/uploads/' . htmlspecialchars($row['receipt_proof']) : ''; ?>"
                                       data-received="<?php echo !empty($row['received_at']) ? date('d/m/Y H:i', strtotime($row['received_at'])) : '-'; ?>"
                                       data-requester="<?php echo htmlspecialchars($row['requester_name']); ?>"
                                    >
                                        #<?php echo $display_id; ?>
                                    </a>
                                </td>
                                <td>
                                    <div style="font-weight: 500;"><?php echo $row['agent_name']; ?></div>
                                </td>
                                <td><?php echo number_format($row['order_quantity']); ?></td>
                                <td>
                                    <div><?php echo date('d/m/Y', strtotime($row['order_date'])); ?></div>
                                    <div style="font-size: 12px; color: #aaa;"><?php echo date('H:i', strtotime($row['order_date'])); ?> น.</div>
                                </td>
                                <td><?php echo $row['requester_name']; ?></td>
                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                                <td>
                                    <?php if($row['order_status'] == 'Approved' || $row['order_status'] == 'Received'): ?>
                                        <a href="print_order.php?id=<?php echo $row['order_id']; ?>" target="_blank" class="btn btn-sm btn-outline-info" title="พิมพ์ใบสั่งซื้อ">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if($row['order_status'] == 'Pending'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="cancelOrder(<?php echo $row['order_id']; ?>, '<?php echo $display_id; ?>')" title="ยกเลิกคำสั่งซื้อ">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center" style="padding: 30px; color: #888;">ไม่พบข้อมูลการสั่งซื้อ</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            <span style="font-size: 14px; color: #888; margin-right: auto;">แสดง 1 ถึง 10 จาก <?php echo $orders_result->num_rows; ?> รายการ</span>
            <ul class="pagination" style="margin: 0;">
                <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a></li>
            </ul>
        </div>
    </div>
</div>

<!-- View Order Details Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-file-invoice" style="margin-right: 10px; color: #0066ff;"></i>รายละเอียดคำสั่งซื้อ</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <h5 style="color: #0066ff; margin-bottom: 0;" id="view_order_id"></h5>
            <small class="text-muted" id="view_order_date"></small>
        </div>

        <div class="row mb-2">
            <div class="col-4 text-muted">ซัพพลายเออร์:</div>
            <div class="col-8 font-weight-bold" id="view_agent"></div>
        </div>

         <div class="row mb-2">
            <div class="col-4 text-muted">ปริมาณ:</div>
            <div class="col-8 font-weight-bold" id="view_quantity"></div>
        </div>
        <div class="row mb-2">
            <div class="col-4 text-muted">สถานะ:</div>
            <div class="col-8" id="view_status"></div>
        </div>
        
        <hr>
        


        <div class="mb-2" id="receipt_section" style="display:none;">
            <label class="text-muted d-block">หลักฐานการโอนเงิน (Receipt):</label>
            <span id="view_receipt"></span>
            <div class="mt-1" id="received_info"></div>
        </div>

        <div class="mb-2">
            <label class="text-muted d-block">หมายเหตุ:</label>
            <div class="p-2 bg-light rounded" id="view_note"></div>
        </div>
      </div>
      <div class="modal-footer" style="justify-content: space-between;">
        <a href="#" id="btn_print_po" target="_blank" class="btn btn-info"><i class="fas fa-print"></i> พิมพ์ใบสั่งซื้อ (PDF)</a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
      </div>
    </div>
  </div>
</div>

<script>
function viewOrderDetails(element) {
    // ... existing logic ...
    // Get data
    const id = element.getAttribute('data-id');
    const agent = element.getAttribute('data-agent');
    const category = element.getAttribute('data-category');
    const quantity = element.getAttribute('data-quantity');
    const date = element.getAttribute('data-date');
    const status = element.getAttribute('data-status');
    const rawStatus = element.getAttribute('data-raw-status');
    const note = element.getAttribute('data-note');
    const attachment = element.getAttribute('data-attachment');
    const receipt = element.getAttribute('data-receipt');
    const received_at = element.getAttribute('data-received');

    // Populate Modal
    document.getElementById('view_order_id').innerText = '#' + id;
    document.getElementById('view_order_date').innerText = 'สั่งซื้อเมื่อ: ' + date;
    document.getElementById('view_agent').innerText = agent;

    document.getElementById('view_quantity').innerText = quantity;
    document.getElementById('view_status').innerText = status;
    document.getElementById('view_note').innerText = note !== '' ? note : '-';



    // Handle receipt
    const receiptSection = document.getElementById('receipt_section');
    const receiptLink = document.getElementById('view_receipt');
    const receivedInfo = document.getElementById('received_info');
    if (receipt && receipt !== '') {
        receiptSection.style.display = 'block';
        receiptLink.innerHTML = `<a href="${receipt}" target="_blank" class="btn btn-sm btn-outline-success"><i class="fas fa-receipt"></i> ดูหลักฐานการโอนเงิน</a>`;
        if (received_at && received_at !== '-') {
            receivedInfo.innerHTML = `<small class="text-muted">ได้รับเครดิตเมื่อ: ${received_at}</small>`;
        } else {
            receivedInfo.innerHTML = '';
        }
    } else {
        receiptSection.style.display = 'none';
        receiptLink.innerHTML = '';
        receivedInfo.innerHTML = '';
    }

    // Set Print Button URL
    const realId = element.getAttribute('data-real-id');
    const printBtn = document.getElementById('btn_print_po');
    if (printBtn) {
        printBtn.href = 'print_order.php?id=' + realId;
        
        // Only allow printing if Approved or Received
        if (rawStatus === 'Approved' || rawStatus === 'Received') {
            printBtn.style.display = 'inline-block';
        } else {
            printBtn.style.display = 'none';
        }
    }
    
    // Show Modal
    $('#viewOrderModal').modal('show');
}

function cancelOrder(id, displayId) {
    Swal.fire({
        title: 'ยืนยันการยกเลิก?',
        text: "คุณต้องการยกเลิกคำสั่งซื้อ #" + displayId + " ใช่หรือไม่?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ยกเลิกเดี๋ยวนี้!',
        cancelButtonText: 'ไม่, เก็บไว้'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create a form to submit via POST
            var form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", "action/order_cancel_db.php");

            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", "order_id");
            hiddenField.setAttribute("value", id);

            form.appendChild(hiddenField);
            document.body.appendChild(form);
            form.submit();
        }
    })
}

// Check for cancel success
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('success_cancel')) {
    Swal.fire({
        icon: 'success',
        title: 'ยกเลิกคำสั่งซื้อสำเร็จ',
        showConfirmButton: false,
        timer: 1500
    }).then(() => {
        // Clear param
         window.history.replaceState(null, null, window.location.pathname + '?p=orders');
    });
}

</script>

<!-- Updating the Success Script -->
<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'สร้างคำสั่งซื้อสำเร็จ',
            text: 'คำสั่งซื้อของคุณถูกส่งเข้าสู่ระบบแล้ว',
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#0066ff',
            showCancelButton: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.history.replaceState(null, null, window.location.pathname + '?p=orders');
            }
        });
    });
</script>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let errorMsg = 'เกิดข้อผิดพลาดในการสร้างคำสั่งซื้อ';
        let errorType = '<?php echo htmlspecialchars($_GET['error']); ?>';
        
        if(errorType === 'missing_fields') errorMsg = 'กรุณากรอกข้อมูลให้ครบถ้วน';
        else if(errorType === 'invalid_quantity') errorMsg = 'ปริมาณการสั่งซื้อไม่ถูกต้อง';
        else if(errorType === 'limit_exceeded') errorMsg = 'ปริมาณการสั่งซื้อเกินกำหนด';
        
        Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด',
            text: errorMsg,
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#d33'
        });
    });
</script>
<?php endif; ?>
