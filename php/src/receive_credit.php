<?php
include 'connect.php';

// Fetch Approved Orders (Pending Receive)
$sql_approved = "SELECT pc.*, a.agent_name 
                FROM purchase_credit pc 
                JOIN agent a ON pc.agent_id = a.agent_id 
                WHERE pc.order_status = 'Approved' 
                ORDER BY pc.order_date ASC";
$result_approved = $conn->query($sql_approved);

// History Filters
$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
$search_date = isset($_GET['d']) ? $_GET['d'] : '';
$search_status = isset($_GET['status']) ? $_GET['status'] : 'Received';

$sql_history_cond = "";
if ($search_q !== '') {
    $sql_history_cond .= " AND (pc.order_number LIKE '%" . $conn->real_escape_string($search_q) . "%' OR pc.order_id LIKE '%" . $conn->real_escape_string($search_q) . "%')";
}
if ($search_date !== '') {
    $dates = explode(' - ', $search_date);
    if (count($dates) == 2) {
        $start = DateTime::createFromFormat('d/m/Y', trim($dates[0]));
        $end = DateTime::createFromFormat('d/m/Y', trim($dates[1]));
        if ($start && $end) {
            $sql_history_cond .= " AND DATE(pc.received_at) >= '" . $start->format('Y-m-d') . "' AND DATE(pc.received_at) <= '" . $end->format('Y-m-d') . "'";
        }
    }
}
if ($search_status !== '') {
     $sql_history_cond .= " AND pc.order_status = '" . $conn->real_escape_string($search_status) . "'";
} else {
     $sql_history_cond .= " AND pc.order_status = 'Received'";
}

// Fetch History
$sql_history = "SELECT pc.*, a.agent_name, CONCAT(u.firstname, ' ', u.lastname) as receiver_name 
                FROM purchase_credit pc 
                JOIN agent a ON pc.agent_id = a.agent_id 
                LEFT JOIN user u ON pc.received_by = u.user_id 
                WHERE 1=1 $sql_history_cond 
                ORDER BY pc.received_at DESC 
                LIMIT 50";
$result_history = $conn->query($sql_history);
?>

<!-- DateRangePicker Libraries -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<style>
/* ===== UI Styling ===== */
.card-title-header {
    font-size: 17px; 
    color: #333; 
    margin-bottom: 18px; 
    border-bottom: 1px solid #eee; 
    padding-bottom: 14px;
    font-weight: 600;
}
.rc-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.rc-table thead th {
    font-size: 13px;
    color: #718096;
    font-weight: 600;
    padding: 12px 16px;
    border-bottom: 2px solid #edf2f7;
    text-align: left;
    white-space: nowrap;
    letter-spacing: 0.3px;
}
.rc-table tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
    font-size: 14px;
    color: #333;
}
.rc-table tbody tr:hover {
    background-color: #f7fafc;
}
.order-id-tag {
    display: inline-block;
    color: #555;
    font-size: 14px;
    font-weight: 500;
}
.badge-appr {
    background: #e8f5e9;
    color: #2e7d32;
    padding: 5px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.btn-rc-blue {
    background: #0066ff;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-rc-blue:hover {
    background: #005ce6;
    box-shadow: 0 3px 8px rgba(0,102,255,0.2);
}
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
.filter-bar input:focus, .filter-bar select:focus {
    border-color: #0066ff;
}
</style>

<div class="content-body">
    <!-- ===== Section 1: Pending Receive ===== -->
    <div class="card" style="margin-bottom: 24px; border: none; box-shadow: 0 2px 15px rgba(0,0,0,0.05);">
        <h5 class="card-title-header">รายการที่ต้องรับเครดิต</h5>
        <div class="table-responsive">
            <table class="rc-table">
                <thead>
                    <tr>
                        <th>เลขที่ใบเสนอราคา</th>
                        <th>ซัพพลายเออร์</th>
                        <th>ปริมาณ</th>
                        <th>วันที่อนุมัติ</th>
                        <th>สถานะ</th>
                        <th>ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_approved->num_rows > 0): ?>
                        <?php while($row = $result_approved->fetch_assoc()): 
                            $display_id = !empty($row['order_number']) ? $row['order_number'] : str_pad($row['order_id'], 5, '0', STR_PAD_LEFT);
                        ?>
                        <tr>
                            <td><span class="order-id-tag">#<?php echo htmlspecialchars($display_id); ?></span></td>
                            <td><?php echo htmlspecialchars($row['agent_name']); ?></td>
                            <td><?php echo number_format($row['order_quantity']); ?></td>
                            <td style="color: #666;"><?php echo date('d M Y', strtotime($row['order_date'])); ?></td>
                            <td><span class="badge-appr">อนุมัติ</span></td>
                            <td>
                                <button type="button" class="btn-rc-blue btn-receive-trigger" 
                                    data-id="<?php echo $row['order_id']; ?>"
                                    data-agent="<?php echo htmlspecialchars($row['agent_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-qty="<?php echo $row['order_quantity']; ?>">
                                    <i class="fas fa-file-import"></i> รับเครดิต
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center" style="padding: 30px; color:#aaa;">ไม่มีรายการที่ต้องดำเนินการ</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== Section 2: History ===== -->
    <div class="card" style="border: none; box-shadow: 0 2px 15px rgba(0,0,0,0.05);">
        <h5 class="card-title-header">ประวัติการบันทึกรับเครดิต</h5>
        
        <!-- Filter Form -->
        <form method="GET" action="index.php" id="historyFilterForm">
            <input type="hidden" name="p" value="receive_credit">
            <div class="filter-bar">
                <input type="text" name="q" placeholder="ค้นหาเลขใบเสนอราคา..." value="<?php echo htmlspecialchars($search_q); ?>" style="min-width: 200px;">
                
                <div id="dateRangePicker" style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #555; background: #fff; border: 1px solid #e2e8f0; padding: 7px 14px; border-radius: 6px; cursor: pointer; min-width: 240px;">
                    <i class="fas fa-calendar-alt" style="color: #0066ff;"></i>
                    <span id="dateRangeText">เลือกวันที่...</span>
                    <i class="fas fa-chevron-down" style="margin-left: auto; font-size: 10px; color: #999;"></i>
                    <input type="hidden" name="d" id="search_date_range" value="<?php echo htmlspecialchars($search_date); ?>">
                </div>

                <select name="status" onchange="document.getElementById('historyFilterForm').submit()" style="min-width: 140px;">
                    <option value="">ทุกสถานะ</option>
                    <option value="Received" <?php echo $search_status == 'Received' ? 'selected' : ''; ?>>บันทึกแล้ว</option>
                    <option value="Approved" <?php echo $search_status == 'Approved' ? 'selected' : ''; ?>>รอรับเครดิต</option>
                </select>

                <button type="submit" class="btn-rc-blue" style="padding: 7px 16px;"><i class="fas fa-search"></i> ค้นหา</button>
                <a href="index.php?p=receive_credit" class="btn btn-light" style="font-size: 13px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 14px; color: #555;">ล้างค่า</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="rc-table">
                <thead>
                    <tr>
                        <th>เลขที่ใบเสนอราคา</th>
                        <th>ซัพพลายเออร์</th>
                        <th>ปริมาณ</th>
                        <th>วันที่บันทึก</th>
                        <th>ผู้บันทึก</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_history->num_rows > 0): ?>
                        <?php while($h_row = $result_history->fetch_assoc()): 
                            $h_display_id = !empty($h_row['order_number']) ? $h_row['order_number'] : str_pad($h_row['order_id'], 5, '0', STR_PAD_LEFT);
                            $is_received = ($h_row['order_status'] === 'Received');
                        ?>
                        <tr>
                            <td><span class="order-id-tag">#<?php echo htmlspecialchars($h_display_id); ?></span></td>
                            <td><?php echo htmlspecialchars($h_row['agent_name']); ?></td>
                            <td><?php echo number_format($h_row['order_quantity']); ?></td>
                            <td style="color: #666;">
                                <?php 
                                    if (!empty($h_row['received_at'])) {
                                        echo date('d M Y H:i', strtotime($h_row['received_at'])); 
                                    } else {
                                        echo '-';
                                    }
                                ?>
                            </td>
                            <td style="color: #666;"><?php echo !empty($h_row['receiver_name']) ? htmlspecialchars($h_row['receiver_name']) : '-'; ?></td>
                            <td>
                                <?php if($is_received): ?>
                                    <span class="badge-appr">บันทึกแล้ว</span>
                                <?php else: ?>
                                    <span class="badge-appr" style="background: #fff3e0; color: #e65100;">รอรับเครดิต</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center" style="padding: 30px; color:#aaa;">ไม่พบประวัติการบันทึก</td></tr>
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

<script>
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
    
    // If invalid or empty, default to wide range or leave empty. 
    // We'll leave it empty unless they explicitly select. 
    // To conform with daterangepicker, we default to the past 30 days but don't force it in the input yet.
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
