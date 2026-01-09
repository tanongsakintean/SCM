<?php
include 'connect.php'; 

// Fetch Agents
$agents_result = $conn->query("SELECT * FROM agent");

// Fetch Categories
$categories_result = $conn->query("SELECT * FROM category");

// Fetch Order History
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
    <!-- Order Form Section -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">
            <h5 style="margin: 0; font-size: 18px; color: #333;"><i class="fas fa-cart-plus" style="margin-right: 10px; color: #0066ff;"></i>สร้างคำสั่งซื้อใหม่</h5>
        </div>
        
        <form action="action/order_create_db.php" method="post" enctype="multipart/form-data">
            <div class="form-row">
                <!-- Row 1: Category & Supplier -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">เลือกประเภทสินค้า <span style="color: red;">*</span></label>
                        <div class="custom-select-wrapper">
                            <select name="category_id" class="form-control" required>
                                <option value="">-- กรุณาเลือก --</option>
                                <?php while($cat = $categories_result->fetch_assoc()): ?>
                                    <option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['category_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">ซัพพลายเออร์ <span style="color: red;">*</span></label>
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

                <!-- Row 2: Quantity & Date -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">ปริมาณการสั่งซื้อ <span style="color: red;">*</span></label>
                        <input type="number" name="order_quantity" class="form-control" placeholder="เช่น 100000" required min="1">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">วันที่ต้องการรับเครดิต</label>
                        <input type="date" name="expected_date" class="form-control">
                    </div>
                </div>

                <!-- Row 3: File & Note -->
                <div class="col-md-6">
                     <div class="form-group">
                        <label class="form-label">แนบใบเสนอราคา (PDF/Image)</label>
                        <div class="file-upload-wrapper" style="width: 100%;">
                            <input type="file" name="order_attachment" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png" style="padding: 9px;">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h5 style="margin: 0; font-size: 18px; color: #333;"><i class="fas fa-history" style="margin-right: 10px; color: #0066ff;"></i>ประวัติการสั่งซื้อ</h5>
            <div style="display: flex; gap: 10px;">
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="ค้นหาเลขออเดอร์...">
                </div>
                <button class="btn-upload" style="background-color: #f8f9fa;" onclick="toggleFilters()">
                    <i class="fas fa-filter"></i> ตัวกรอง
                </button>
            </div>
        </div>

        <!-- Filter Bar (Hidden by default) -->
        <div class="filter-grid" id="filterSection" style="display: none;">
           <div>
               <label class="filter-group-label">สถานะ</label>
               <select class="filter-input">
                   <option value="">ทั้งหมด</option>
                   <option value="Pending">รอดำเนินการ</option>
                   <option value="Approved">อนุมัติแล้ว</option>
                   <option value="Rejected">ถูกปฏิเสธ</option>
               </select>
           </div>
           <div>
               <label class="filter-group-label">วันที่เริ่มต้น</label>
               <input type="date" class="filter-input">
           </div>
           <div>
               <label class="filter-group-label">ถึงวันที่</label>
               <input type="date" class="filter-input">
           </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 15%;">รหัสคำสั่งซื้อ</th>
                        <th style="width: 25%;">ซัพพลายเออร์</th>
                        <th style="width: 15%;">ประเภท</th>
                        <th style="width: 15%;">ปริมาณ</th>
                        <th style="width: 15%;">วันที่สั่งซื้อ</th>
                        <th style="width: 15%;">สถานะ</th>
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

                                if ($row['order_status'] == 'Pending') $status_text = 'รอดำเนินการ';
                                elseif ($row['order_status'] == 'Approved') $status_text = 'อนุมัติแล้ว';
                                elseif ($row['order_status'] == 'Rejected') $status_text = 'ถูกปฏิเสธ';
                                elseif ($row['order_status'] == 'Received') $status_text = 'ได้รับเครดิตแล้ว';
                            ?>
                            <tr>
                                <td>
                                    <a href="javascript:void(0);" onclick="viewOrderDetails(this)" 
                                       style="color: #0066ff; font-weight: 500;"
                                       data-id="<?php echo str_pad($row['order_id'], 5, '0', STR_PAD_LEFT); ?>"
                                       data-agent="<?php echo htmlspecialchars($row['agent_name']); ?>"
                                       data-category="<?php echo htmlspecialchars($row['category_name']); ?>"
                                       data-quantity="<?php echo number_format($row['order_quantity']); ?>"
                                       data-date="<?php echo date('d/m/Y H:i', strtotime($row['order_date'])); ?>"
                                       data-status="<?php echo $status_text; ?>"
                                       data-note="<?php echo htmlspecialchars($row['order_note'] ?? '-'); ?>"
                                       data-attachment="<?php echo htmlspecialchars($row['order_attachment'] ?? ''); ?>"
                                       data-receipt="<?php echo htmlspecialchars($row['receipt_proof'] ?? ''); ?>"
                                       data-received="<?php echo !empty($row['received_at']) ? date('d/m/Y H:i', strtotime($row['received_at'])) : '-'; ?>"
                                    >
                                        #<?php echo str_pad($row['order_id'], 5, '0', STR_PAD_LEFT); ?>
                                    </a>
                                </td>
                                <td>
                                    <div style="font-weight: 500;"><?php echo $row['agent_name']; ?></div>
                                    <div style="font-size: 12px; color: #888;"><?php echo $row['category_name']; ?></div>
                                </td>
                                <td><?php echo $row['category_name']; ?></td>
                                <td><?php echo number_format($row['order_quantity']); ?></td>
                                <td>
                                    <div><?php echo date('d/m/Y', strtotime($row['order_date'])); ?></div>
                                    <div style="font-size: 12px; color: #aaa;"><?php echo date('H:i', strtotime($row['order_date'])); ?> น.</div>
                                </td>
                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
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
            <div class="col-4 text-muted">ประเภทสินค้า:</div>
            <div class="col-8" id="view_category"></div>
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
        
        <div class="mb-2">
            <label class="text-muted d-block">เอกสารแนบ (ใบเสนอราคา):</label>
            <span id="view_attachment"></span>
        </div>

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
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
      </div>
    </div>
  </div>
</div>

<script>
function viewOrderDetails(element) {
    // Get data from attributes
    const id = element.getAttribute('data-id');
    const agent = element.getAttribute('data-agent');
    const category = element.getAttribute('data-category');
    const quantity = element.getAttribute('data-quantity');
    const date = element.getAttribute('data-date');
    const status = element.getAttribute('data-status');
    const note = element.getAttribute('data-note');
    const attachment = element.getAttribute('data-attachment');
    const receipt = element.getAttribute('data-receipt');
    const received = element.getAttribute('data-received');

    // Populate Modal
    document.getElementById('view_order_id').innerText = '#' + id;
    document.getElementById('view_order_date').innerText = 'สั่งซื้อเมื่อ: ' + date;
    document.getElementById('view_agent').innerText = agent;
    document.getElementById('view_category').innerText = category;
    document.getElementById('view_quantity').innerText = quantity;
    document.getElementById('view_status').innerText = status;
    document.getElementById('view_note').innerText = note !== '' ? note : '-';

    // Handle Attachment Link
    const attachSpan = document.getElementById('view_attachment');
    if (attachment && attachment !== 'null' && attachment !== '') {
        attachSpan.innerHTML = `<a href="${attachment}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-paperclip"></i> ดูเอกสารแนบ</a>`;
    } else {
        attachSpan.innerText = '-';
    }

    // Handle Receipt Section
    const receiptSection = document.getElementById('receipt_section');
    const receiptSpan = document.getElementById('view_receipt');
    const receivedInfo = document.getElementById('received_info');

    if (receipt && receipt !== 'null' && receipt !== '') {
        receiptSection.style.display = 'block';
        // Check if path already contains assets/uploads to avoid double prefixing (just in case)
        let receiptPath = receipt;
        if (!receipt.includes('assets/uploads/')) {
            receiptPath = 'assets/uploads/' + receipt;
        }
        receiptSpan.innerHTML = `<a href="${receiptPath}" target="_blank" class="btn btn-sm btn-outline-success"><i class="fas fa-file-invoice-dollar"></i> ดูหลักฐานการโอน</a>`;
        if (received && received !== '-') {
             receivedInfo.innerHTML = `<small class="text-muted">ได้รับเมื่อ: ${received}</small>`;
        } else {
             receivedInfo.innerHTML = '';
        }
    } else {
        receiptSection.style.display = 'none';
        receiptSpan.innerHTML = '';
        receivedInfo.innerHTML = '';
    }

    // Show Modal
    $('#viewOrderModal').modal('show');
}
function toggleFilters() {
    const filterSection = document.getElementById('filterSection');
    if (filterSection.style.display === 'none') {
        filterSection.style.display = 'grid';
    } else {
        filterSection.style.display = 'none';
    }
}
</script>

<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'สร้างคำสั่งซื้อสำเร็จ',
            text: 'คำสั่งซื้อของคุณถูกส่งเข้าสู่ระบบแล้ว',
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#0066ff'
        }).then((result) => {
            if (result.isConfirmed) {
                // Optional: Clear URL params
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
