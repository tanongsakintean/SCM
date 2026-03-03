<?php
include 'connect.php'; 

// Fetch Credit Settings (System Credit Info)
$sql_credit = "SELECT credit_balance, credit_min FROM credit_setting WHERE user_id = 2";
$res_credit = $conn->query($sql_credit);
$credit_row = $res_credit->fetch_assoc();
$credit_balance = $credit_row['credit_balance'] ?? 0;
$credit_min = $credit_row['credit_min'] ?? 0;

// Fetch Agents
$agents_result = $conn->query("SELECT * FROM agent ORDER BY agent_name ASC");

// Fetch Categories
$categories_result = $conn->query("SELECT * FROM category");

// Filter inputs
$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
$search_agent = isset($_GET['agent']) ? $_GET['agent'] : '';
$search_date = isset($_GET['d']) ? $_GET['d'] : '';

// Fetch Order History
$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'] ?? 0;

$sql_cond = "";
if ($search_q !== '') {
    $sql_cond .= " AND (pc.order_number LIKE '%" . $conn->real_escape_string($search_q) . "%' OR pc.order_id LIKE '%" . $conn->real_escape_string($search_q) . "%')";
}
if ($search_agent !== '') {
    $sql_cond .= " AND pc.agent_id = '" . $conn->real_escape_string($search_agent) . "'";
}
if ($search_date !== '') {
    $dates = explode(' - ', $search_date);
    if (count($dates) == 2) {
        $start = DateTime::createFromFormat('d/m/Y', trim($dates[0]));
        $end = DateTime::createFromFormat('d/m/Y', trim($dates[1]));
        if ($start && $end) {
            $sql_cond .= " AND DATE(pc.order_date) >= '" . $start->format('Y-m-d') . "' AND DATE(pc.order_date) <= '" . $end->format('Y-m-d') . "'";
        }
    }
}

$sql_orders = "SELECT pc.*, a.agent_name, c.category_name, u.username as requester_name 
               FROM purchase_credit pc 
               JOIN agent a ON pc.agent_id = a.agent_id
               JOIN category c ON pc.category_id = c.category_id
               LEFT JOIN user u ON pc.user_id = u.user_id
               WHERE 1=1 $sql_cond";

// If the user does not have permission to approve orders, they can only see their own orders
if (!has_permission($role_id, 'approve_orders')) {
    $sql_orders .= " AND pc.user_id = $user_id";
}
$sql_orders .= " ORDER BY pc.order_date DESC, pc.order_id DESC";
$orders_result = $conn->query($sql_orders);

$agents_array = [];
$agents_result_for_js = $conn->query("SELECT * FROM agent");
while($ag = $agents_result_for_js->fetch_assoc()) {
    $agents_array[] = $ag;
}
?>

<!-- DateRangePicker Libraries -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<style>
.card-title-header {
    font-size: 17px; 
    color: #333; 
    margin-bottom: 20px; 
    padding-bottom: 14px;
    font-weight: 600;
}
.order-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.order-table thead tr { background: #f8fafc; }
.order-table thead th {
    font-size: 13px;
    color: #64748b;
    font-weight: 600;
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
    white-space: nowrap;
}
.order-table tbody td {
    padding: 16px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    font-size: 14px;
    color: #334155;
}
.order-table tbody tr:hover {
    background-color: #f8fafc;
}
.order-id { color: #0066ff; font-weight: 500; text-decoration: none; }
.order-id:hover { text-decoration: underline; }

.status-appr { display: inline-flex; align-items: center; gap: 5px; background: #e8f5e9; color: #2e7d32; padding: 5px 12px; border-radius: 20px; font-size: 12.5px; font-weight: 500; }
.status-pend { display: inline-flex; align-items: center; gap: 5px; background: #fff8e1; color: #f57f17; padding: 5px 12px; border-radius: 20px; font-size: 12.5px; font-weight: 500; }
.status-rej  { display: inline-flex; align-items: center; gap: 5px; background: #ffebee; color: #c62828; padding: 5px 12px; border-radius: 20px; font-size: 12.5px; font-weight: 500; }
.status-recv { display: inline-flex; align-items: center; gap: 5px; background: #e3f2fd; color: #1565c0; padding: 5px 12px; border-radius: 20px; font-size: 12.5px; font-weight: 500; }

.filter-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
    flex-wrap: wrap;
}
.filter-bar input, .filter-bar select {
    padding: 8px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 13px;
    color: #555;
    background: #fff;
    outline: none;
}
.filter-bar input:focus, .filter-bar select:focus { border-color: #0066ff; }

/* Supplier Info Card */
.supplier-info-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    margin-top: 10px;
}
.supplier-info-title {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 12px;
}
.supplier-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    font-size: 14px;
    color: #334155;
}
.supplier-info-note {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 12px;
}
</style>

<div class="content-body">
    
    <?php if (has_permission($role_id, 'orders')): // Assuming anyone who can access orders page can create them, or could be a specific create_orders permission ?>
    <!-- Order Form Section -->
    <div class="card" style="margin-bottom: 24px; border: none; box-shadow: 0 2px 15px rgba(0,0,0,0.05);">
        <h5 class="card-title-header" style="border-bottom: 1px solid #edf2f7;"><i class="fas fa-cart-plus" style="margin-right: 10px; color: #0066ff;"></i>สร้างคำสั่งซื้อใหม่</h5>
        
        <form action="action/order_create_db.php" method="post" enctype="multipart/form-data">
            <div class="row">
                <!-- Left: Supplier Selection & Info -->
                <div class="col-md-6">
                     <div class="form-group mb-3">
                        <label class="form-label" style="font-weight: 500;">ซัพพลายเออร์ (Supplier) <span style="color: red;">*</span></label>
                        <select name="agent_id" id="agentSelect" class="form-control" style="border-radius: 6px;" required onchange="updateSupplierInfo()">
                            <option value="">-- กรุณาเลือก --</option>
                            <?php 
                            $agents_result->data_seek(0);
                            while($agent = $agents_result->fetch_assoc()): ?>
                                <option value="<?php echo $agent['agent_id']; ?>"><?php echo htmlspecialchars($agent['agent_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Dynamic Supplier Info -->
                    <div class="supplier-info-box" id="supplierInfoBox" style="display: none;">
                        <div class="supplier-info-title">ข้อมูลซัพพลายเออร์</div>
                        <div class="supplier-info-row">
                            <span id="si_agent_name" style="font-weight: 500;">-</span>
                            <span id="si_balance" style="font-weight: 600;"><?php echo number_format($credit_balance); ?> เครดิต (ระบบ)</span>
                        </div>
                        <div class="supplier-info-row" style="margin-bottom: 0;">
                            <span>เครดิตคงเหลือ:</span>
                            <span style="color: <?php echo $credit_balance < $credit_min ? '#e53e3e' : '#38a169'; ?>; font-weight: 500;">
                                <i class="fas fa-circle" style="font-size: 8px; margin-right: 4px;"></i> <?php echo $credit_balance < $credit_min ? 'ต่ำกว่าขั้นต่ำ' : 'ปกติ'; ?>
                            </span>
                        </div>
                        <div class="supplier-info-note">หมายเหตุ : สามารถสั่งขั้นต่ำได้ 10,000 เครดิตขึ้นไป (หรือตามเงื่อนไขซัพพลายเออร์)</div>
                    </div>
                </div>

                <!-- Right: Quantity & Note -->
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" style="font-weight: 500;">ปริมาณการสั่งซื้อ (เครดิต) <span style="color: red;">*</span></label>
                        <input type="number" name="order_quantity" class="form-control" style="border-radius: 6px;" placeholder="เช่น 100000" required min="1">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label" style="font-weight: 500;">หมายเหตุ</label>
                        <textarea name="order_note" class="form-control" rows="1" style="border-radius: 6px; height: 50px;" placeholder="ระบุรายละเอียดเพิ่มเติม..."></textarea>
                    </div>
                    <div style="margin-top: 20px; text-align: right;">
                        <button type="submit" class="btn btn-primary" style="background: #0066ff; border: none; padding: 8px 24px; border-radius: 6px;"><i class="fas fa-paper-plane" style="margin-right: 5px;"></i> ส่งคำสั่งซื้อ</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Order History Section -->
    <div class="card" style="border: none; box-shadow: 0 2px 15px rgba(0,0,0,0.05); margin-bottom: 24px;">
        <h5 class="card-title-header">ประวัติการทำรายการ สั่งซื้อเครดิต</h5>

        <form method="GET" action="index.php" id="historyFilterForm">
            <input type="hidden" name="p" value="orders">
            <div class="filter-bar">
                <input type="text" name="q" placeholder="ค้นหาเลขใบสั่งซื้อ..." value="<?php echo htmlspecialchars($search_q); ?>" style="min-width: 200px;">
                
                <select name="agent" onchange="document.getElementById('historyFilterForm').submit()" style="min-width: 180px;">
                    <option value="">ซัพพลายเออร์ทั้งหมด</option>
                    <?php 
                    $agents_result->data_seek(0);
                    while($ag = $agents_result->fetch_assoc()): ?>
                    <option value="<?php echo $ag['agent_id']; ?>" <?php echo $search_agent == $ag['agent_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($ag['agent_name']); ?></option>
                    <?php endwhile; ?>
                </select>

                <div id="dateRangePicker" style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #555; background: #fff; border: 1px solid #e2e8f0; padding: 7px 14px; border-radius: 6px; cursor: pointer; min-width: 240px;">
                    <i class="fas fa-calendar-alt" style="color: #0066ff;"></i>
                    <span id="dateRangeText">เลือกวันที่...</span>
                    <i class="fas fa-chevron-down" style="margin-left: auto; font-size: 10px; color: #999;"></i>
                    <input type="hidden" name="d" id="search_date_range" value="<?php echo htmlspecialchars($search_date); ?>">
                </div>

                <button type="submit" class="btn btn-primary" style="background: #0066ff; border: none; border-radius: 6px; padding: 7px 16px; font-size: 13px;"><i class="fas fa-search"></i> ค้นหา</button>
                <a href="index.php?p=orders" class="btn btn-light" style="font-size: 13px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 14px; color: #555;">ล้างค่า</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="order-table">
                <thead>
                    <tr>
                        <th>เลขคำสั่งซื้อ</th>
                        <th>ซัพพลายเออร์</th>
                        <th>จำนวนเครดิต</th>
                        <th>วันที่</th>
                        <th>สถานะ</th>
                        <th>หมายเหตุ</th>
                        <th>ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders_result->num_rows > 0): ?>
                        <?php while($row = $orders_result->fetch_assoc()): 
                            $status_class = '';
                            $status_html = '';
                            if ($row['order_status'] == 'Pending') {
                                $status_html = '<span class="status-pend"><i class="fas fa-circle" style="font-size:8px;"></i> รออนุมัติ</span>';
                            } elseif ($row['order_status'] == 'Approved') {
                                $status_html = '<span class="status-appr"><i class="fas fa-check-circle"></i> อนุมัติแล้ว</span>';
                            } elseif ($row['order_status'] == 'Rejected' || $row['order_status'] == 'Cancelled') {
                                $status_html = '<span class="status-rej"><i class="fas fa-times-circle"></i> ปฏิเสธ</span>';
                            } elseif ($row['order_status'] == 'Received') {
                                $status_html = '<span class="status-recv"><i class="fas fa-arrow-circle-down"></i> รับเครดิตแล้ว</span>';
                            }
                            
                            $display_id = !empty($row['order_number']) ? $row['order_number'] : str_pad($row['order_id'], 5, '0', STR_PAD_LEFT);
                            $note_display_text = !empty($row['order_note']) ? htmlspecialchars($row['order_note']) : '-';
                            if (strlen($note_display_text) > 30) {
                                $note_display_text = mb_substr($note_display_text, 0, 30, 'UTF-8') . '...';
                            }
                        ?>
                            <tr>
                                <td>
                                    <a href="javascript:void(0);" onclick="viewOrderDetails(this)" 
                                       class="order-id"
                                       data-real-id="<?php echo $row['order_id']; ?>"
                                       data-id="<?php echo $display_id; ?>"
                                       data-agent="<?php echo htmlspecialchars($row['agent_name']); ?>"
                                       data-category="<?php echo htmlspecialchars($row['category_name'] ?? ''); ?>"
                                       data-quantity="<?php echo number_format($row['order_quantity']); ?>"
                                       data-date="<?php echo date('d/m/Y H:i', strtotime($row['order_date'])); ?>"
                                       data-status="<?php echo strip_tags($status_html); ?>"
                                       data-raw-status="<?php echo $row['order_status']; ?>"
                                       data-note="<?php echo htmlspecialchars($row['order_note'] ?? ''); ?>"
                                       data-attachment="<?php echo htmlspecialchars($row['order_attachment'] ?? ''); ?>"
                                       data-receipt="<?php echo !empty($row['receipt_proof']) ? 'assets/uploads/' . htmlspecialchars($row['receipt_proof']) : ''; ?>"
                                       data-received="<?php echo !empty($row['received_at']) ? date('d/m/Y H:i', strtotime($row['received_at'])) : '-'; ?>"
                                       data-requester="<?php echo htmlspecialchars($row['requester_name']); ?>"
                                    >
                                        #<?php echo $display_id; ?>
                                    </a>
                                </td>
                                <td><div style="font-weight: 500;"><?php echo $row['agent_name']; ?></div></td>
                                <td><?php echo number_format($row['order_quantity']); ?></td>
                                <td style="color: #64748b;"><?php echo date('d/m/y', strtotime($row['order_date'])); ?></td>
                                <td><?php echo $status_html; ?></td>
                                <td style="color: #64748b; font-size: 13px;"><?php echo $note_display_text; ?></td>
                                <td>
                                    <?php 
                                    // only show print when the order has been approved (or already received)
                                    if ( ($row['order_status'] === 'Approved' || $row['order_status'] === 'Received')
                                         && (has_permission($role_id, 'approve_orders') || (isset($_SESSION['user_id']) && $row['user_id'] == $_SESSION['user_id']))): ?>
                                        <a href="print_order.php?id=<?php echo $row['order_id']; ?>" target="_blank" class="btn btn-sm" style="color: #64748b; border: 1px solid #e2e8f0; background: #fff;" title="พิมพ์ใบสั่งซื้อ">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if($row['order_status'] == 'Pending'): ?>
                                        <button type="button" class="btn btn-sm" style="color: #c62828; border: 1px solid #ffebee; background: #fff;" onclick="cancelOrder(<?php echo $row['order_id']; ?>, '<?php echo $display_id; ?>')" title="ยกเลิกคำสั่งซื้อ">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center" style="padding: 40px; color: #94a3b8;">
                            <i class="fas fa-folder-open mb-2" style="font-size: 24px; color: #cbd5e1; display: block;"></i>
                            ไม่พบข้อมูลการสั่งซื้อ
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Dynamic Supplier Info
function updateSupplierInfo() {
    var select = document.getElementById('agentSelect');
    var infoBox = document.getElementById('supplierInfoBox');
    var nameSpan = document.getElementById('si_agent_name');
    
    if (select.value === '') {
        infoBox.style.display = 'none';
    } else {
        var agentName = select.options[select.selectedIndex].text;
        nameSpan.innerText = agentName;
        infoBox.style.display = 'block';
    }
}

// Date Range Picker
$(function() {
    var searchDateRaw = document.getElementById('search_date_range').value;
    var start, end;
    
    if (searchDateRaw) {
        var dates = searchDateRaw.split(' - ');
        if(dates.length === 2 && dates[0] && dates[1]) {
            start = moment(dates[0], 'DD/MM/YYYY');
            end = moment(dates[1], 'DD/MM/YYYY');
        }
    } 
    
    if(!start || !start.isValid()) {
        start = moment().subtract(29, 'days');
        end = moment();
    }

    function cb(s, e) {
        $('#dateRangeText').html(s.format('DD/MM/YYYY') + ' - ' + e.format('DD/MM/YYYY'));
        $('#search_date_range').val(s.format('DD/MM/YYYY') + ' - ' + e.format('DD/MM/YYYY'));
    }

    $('#dateRangePicker').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
           'วันนี้': [moment(), moment()],
           'เมื่อวาน': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           '7 วันล่าสุด': [moment().subtract(6, 'days'), moment()],
           '30 วันล่าสุด': [moment().subtract(29, 'days'), moment()],
           'เดือนนี้': [moment().startOf('month'), moment().endOf('month')],
           'เดือนที่แล้ว': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: "ตกลง",
            cancelLabel: "ยกเลิก",
            customRangeLabel: "กำหนดเอง",
            daysOfWeek: ["อา","จ","อ","พ","พฤ","ศ","ส"],
            monthNames: ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"]
        }
    }, cb);

    if (searchDateRaw) {
        cb(start, end);
    }
});
</script>

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

<?php if (isset($_GET['success_approve'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'อนุมัติคำสั่งซื้อเรียบร้อย',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            window.history.replaceState(null, null, window.location.pathname + '?p=orders');
        });
    });
</script>
<?php endif; ?>
