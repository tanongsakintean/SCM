<?php
include 'connect.php';

$pending_agent_id = isset($_GET['pending_agent']) ? $_GET['pending_agent'] : '';
$pending_date_range = isset($_GET['pending_date_range']) ? $_GET['pending_date_range'] : '';
$history_agent_id = isset($_GET['history_agent']) ? $_GET['history_agent'] : '';
$history_status = isset($_GET['history_status']) ? $_GET['history_status'] : '';
$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Fetch credit balance for the detail popup
$sql_credit = "SELECT credit_balance, credit_min FROM credit_setting WHERE user_id = 2";
$res_credit = $conn->query($sql_credit);
$credit_row = $res_credit->fetch_assoc();
$credit_balance = $credit_row['credit_balance'] ?? 0;
$credit_min = $credit_row['credit_min'] ?? 0;

// Pending Orders
$sql_pending_cond = "";
if ($search_q !== '') {
    $sql_pending_cond .= " AND (pc.order_number LIKE '%" . $conn->real_escape_string($search_q) . "%' OR pc.order_id LIKE '%" . $conn->real_escape_string($search_q) . "%')";
}
if ($pending_agent_id !== '') {
    $sql_pending_cond .= " AND pc.agent_id = '" . $conn->real_escape_string($pending_agent_id) . "'";
}
if ($pending_date_range !== '') {
    $dates = explode(' - ', $pending_date_range);
    if (count($dates) == 2) {
        $start = DateTime::createFromFormat('d/m/Y', trim($dates[0]));
        $end = DateTime::createFromFormat('d/m/Y', trim($dates[1]));
        if ($start && $end) {
            $sql_pending_cond .= " AND DATE(pc.order_date) >= '" . $start->format('Y-m-d') . "' AND DATE(pc.order_date) <= '" . $end->format('Y-m-d') . "'";
        }
    }
}

$sql_pending = "SELECT pc.*, a.agent_name, CONCAT(u.firstname, ' ', u.lastname) as requester_name
                FROM purchase_credit pc 
                JOIN agent a ON pc.agent_id = a.agent_id 
                LEFT JOIN user u ON pc.user_id = u.user_id
                WHERE pc.order_status = 'Pending' $sql_pending_cond
                ORDER BY pc.order_date ASC";
$result_pending = $conn->query($sql_pending);

// History (Last 10 processed actions) from Approve table
$sql_history_cond = "";
if ($search_q !== '') {
    $sql_history_cond .= " AND (pc.order_number LIKE '%" . $conn->real_escape_string($search_q) . "%' OR pc.order_id LIKE '%" . $conn->real_escape_string($search_q) . "%')";
}
if ($history_agent_id !== '') {
    $sql_history_cond .= " AND pc.agent_id = '" . $conn->real_escape_string($history_agent_id) . "'";
}
if ($history_status !== '') {
    $sql_history_cond .= " AND ap.approval_status = '" . $conn->real_escape_string($history_status) . "'";
}

$sql_history = "SELECT ap.*, pc.order_id, pc.order_number, pc.order_quantity, a.agent_name,
                       CONCAT(u.firstname, ' ', u.lastname) as approver_name
                FROM approve ap 
                JOIN purchase_credit pc ON ap.order_id = pc.order_id 
                JOIN agent a ON pc.agent_id = a.agent_id
                LEFT JOIN user u ON ap.user_id = u.user_id
                WHERE 1=1 $sql_history_cond
                ORDER BY ap.approval_date DESC, ap.approval_id DESC 
                LIMIT 10";
$result_history = $conn->query($sql_history);

// Recent orders for purchase history popup (last 3 months)
$sql_recent = "SELECT pc.order_number, pc.order_quantity, a.agent_name
               FROM purchase_credit pc
               JOIN agent a ON pc.agent_id = a.agent_id
               WHERE pc.order_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
               ORDER BY pc.order_date DESC
               LIMIT 5";
$result_recent = $conn->query($sql_recent);
$recent_orders = [];
if ($result_recent) {
    while ($r = $result_recent->fetch_assoc()) {
        $recent_orders[] = $r;
    }
}

// Fetch all agents for filter dropdown
$sql_agents = "SELECT agent_id, agent_name FROM agent ORDER BY agent_name ASC";
$result_agents = $conn->query($sql_agents);
$agents = [];
if ($result_agents) {
    while ($ag = $result_agents->fetch_assoc()) {
        $agents[] = $ag;
    }
}
?>

<style>
/* ===== Approve Orders Page ===== */
.approve-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.approve-header h2 {
    font-size: 22px;
    font-weight: 700;
    color: #2d3748;
    margin: 0;
}

/* Filter Bar */
.filter-bar {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.filter-bar .filter-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #555;
    font-weight: 500;
}
.filter-bar .filter-label i {
    color: #0066ff;
}

/* Pending Table */
.approve-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.approve-table thead th {
    font-size: 13px;
    color: #718096;
    font-weight: 600;
    padding: 12px 16px;
    border-bottom: 2px solid #edf2f7;
    text-align: left;
    white-space: nowrap;
    letter-spacing: 0.3px;
}
.approve-table tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
    font-size: 14px;
    color: #333;
}
.approve-table tbody tr:hover {
    background-color: #f7fafc;
}
.supplier-name {
    font-weight: 600;
    color: #2d3748;
}
.order-id-tag {
    display: inline-block;
    background: #f1f3f5;
    color: #718096;
    font-size: 12px;
    padding: 2px 8px;
    border-radius: 4px;
    margin-left: 8px;
}
.qty-badge {
    background: #e3f2fd;
    color: #1565c0;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.status-pending {
    background: #fff3e0;
    color: #e65100;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.status-approved {
    background: #e8f5e9;
    color: #2e7d32;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.status-rejected {
    background: #ffebee;
    color: #c62828;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}

/* Action Buttons */
.action-btns {
    display: flex;
    align-items: center;
    gap: 6px;
}
.action-btns .btn-act {
    height: 32px;
    border-radius: 6px;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.15s;
    white-space: nowrap;
    padding: 0 10px;
}
.action-btns .btn-act.btn-approve,
.action-btns .btn-act.btn-reject {
    width: 32px;
    padding: 0;
}
.action-btns .btn-act:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
}
.btn-detail { background: #e3f2fd; color: #1565c0; gap: 6px; font-weight: 500; }
.btn-approve { background: #e8f5e9; color: #2e7d32; }
.btn-reject { background: #ffebee; color: #c62828; }

/* Detail Popup (Inline, not modal) */
.detail-popup {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.18);
    z-index: 1050;
    min-width: 380px;
    max-width: 420px;
    padding: 24px;
}
.detail-popup .popup-close {
    position: absolute;
    top: 14px;
    right: 14px;
    background: none;
    border: none;
    font-size: 18px;
    color: #999;
    cursor: pointer;
}
.detail-popup .popup-title {
    font-size: 16px;
    font-weight: 700;
    color: #333;
    margin-bottom: 18px;
}
.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    font-size: 14px;
}
.detail-row .label { color: #718096; }
.detail-row .value { font-weight: 600; color: #2d3748; }
.detail-row .value.credit-low { color: #e53e3e; }
.detail-row .value.credit-ok { color: #38a169; }

.detail-divider {
    border: none;
    border-top: 1px solid #edf2f7;
    margin: 14px 0;
}

.history-title {
    font-size: 13px;
    font-weight: 600;
    color: #555;
    margin-bottom: 10px;
}
.history-list {
    max-height: 120px;
    overflow-y: auto;
}
.history-list-item {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    padding: 5px 0;
    border-bottom: 1px dashed #f1f3f5;
}
.history-list-item .hl-id { color: #0066ff; font-weight: 500; }
.history-list-item .hl-qty { color: #555; }
.popup-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.3);
    z-index: 1040;
}

/* Reject Popup */
.reject-popup {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.18);
    z-index: 1050;
    width: 400px;
    padding: 24px;
}
.reject-popup .popup-close {
    position: absolute;
    top: 14px;
    right: 14px;
    background: none;
    border: none;
    font-size: 18px;
    color: #999;
    cursor: pointer;
}
.reject-popup .popup-title {
    font-size: 16px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.reject-popup .popup-title i {
    color: #ef4444;
    font-size: 18px;
}
.reject-popup .popup-desc {
    font-size: 13px;
    color: #718096;
    margin-bottom: 18px;
}
.reject-popup textarea {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px;
    font-size: 14px;
    resize: vertical;
    min-height: 80px;
    margin-bottom: 18px;
    font-family: inherit;
}
.reject-popup textarea:focus {
    outline: none;
    border-color: #c62828;
    box-shadow: 0 0 0 3px rgba(198,40,40,0.1);
}
.reject-popup .popup-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
.reject-popup .btn-confirm-reject {
    background: #ef4444;
    color: #fff;
    border: none;
    padding: 10px 24px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}
.reject-popup .btn-confirm-reject:hover { background: #dc2626; }
.reject-popup .btn-cancel {
    background: #64748b;
    color: #fff;
    border: none;
    padding: 10px 24px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}
.reject-popup .btn-cancel:hover { background: #475569; }

/* History Feed */
.history-feed {
    background: #fff;
    border-radius: 8px;
}
.history-feed-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 14px 16px;
    border-bottom: 1px solid #f1f5f9;
}
.history-feed-item:last-child {
    border-bottom: none;
}
.hf-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px; /* Pill shape */
    font-size: 13px;
    font-weight: 500;
    white-space: nowrap;
    min-width: 100px;
    justify-content: center;
}
.hf-badge.approved { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
.hf-badge.rejected { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }

.hf-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 16px;
    flex: 1;
    font-size: 14px;
}
.hf-info-col1 {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.hf-info-col2 {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    color: #64748b;
    font-size: 13px;
}

.hf-id { color: #0066ff; font-weight: 600; background: #eef2ff; padding: 2px 6px; border-radius: 4px; font-size: 12px; display: inline-block; margin-right: 8px; }
.hf-supplier { color: #334155; font-weight: 500; }
.hf-date { color: #94a3b8; font-size: 12px; display: flex; align-items: center; gap: 4px; }
</style>

<!-- DateRangePicker Libraries -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<form method="GET" action="index.php" id="filterForm">
    <input type="hidden" name="p" value="approve_orders">
<div class="content-body">

    <!-- ===== Section 1: Pending Orders ===== -->
    <div class="card" style="margin-bottom: 24px; border: none; box-shadow: 0 2px 15px rgba(0,0,0,0.05);">
        <h5 style="font-size: 17px; color: #333; margin-bottom: 18px; border-bottom: 1px solid #eee; padding-bottom: 14px;">
            <i class="fas fa-clipboard-check" style="color: #0066ff; margin-right: 8px;"></i> รายการรออนุมัติ
        </h5>

        <!-- Filter Bar -->
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 18px; flex-wrap: wrap;">
            <input type="text" name="q" placeholder="ค้นหาเลขใบสั่งซื้อ..." value="<?php echo htmlspecialchars($search_q); ?>" style="padding: 8px 14px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #555; background: #fff; min-width: 200px; outline: none;">
            
            <select name="pending_agent" onchange="document.getElementById('filterForm').submit()" style="padding: 8px 14px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #555; min-width: 180px; background: #fff;">
                <option value="">ซัพพลายเออร์ทั้งหมด</option>
                <?php foreach ($agents as $ag): ?>
                <option value="<?php echo $ag['agent_id']; ?>" <?php echo $pending_agent_id == $ag['agent_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($ag['agent_name']); ?></option>
                <?php endforeach; ?>
            </select>
            <div id="dateRangePicker" style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #555; background: #fff; border: 1px solid #e2e8f0; padding: 7px 14px; border-radius: 6px; cursor: pointer; min-width: 240px;">
                <i class="fas fa-calendar-alt" style="color: #0066ff;"></i>
                <span id="dateRangeText">วันที่ 01/01/2024 - 31/01/2026</span>
                <i class="fas fa-chevron-down" style="margin-left: auto; font-size: 10px; color: #999;"></i>
                <input type="hidden" name="pending_date_range" id="pending_date_range" value="<?php echo htmlspecialchars($pending_date_range); ?>">
            </div>

            <button type="submit" class="btn btn-primary" style="background: #0066ff; color: #fff; border: none; border-radius: 6px; padding: 7px 16px; font-size: 13px; cursor: pointer;"><i class="fas fa-search"></i> ค้นหา</button>
            <a href="index.php?p=approve_orders" style="font-size: 13px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 7px 14px; color: #555; text-decoration: none; background: #fff;"><i class="fas fa-times"></i> ล้างค่า</a>
        </div>

        <div class="table-responsive">
            <table class="approve-table">
                <thead>
                    <tr>
                        <th>ซัพพลายเออร์</th>
                        <th>ปริมาณ</th>
                        <th>วันที่</th>
                        <th>ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_pending->num_rows > 0): ?>
                        <?php while($row = $result_pending->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <span class="supplier-name"><?php echo $row['agent_name']; ?></span>
                                <?php $display_id = !empty($row['order_number']) ? $row['order_number'] : str_pad($row['order_id'], 5, '0', STR_PAD_LEFT); ?>
                                <span class="order-id-tag"><?php echo $display_id; ?></span>
                                <span class="status-pending" style="margin-left: 8px;">รออนุมัติ</span>
                            </td>
                            <td><?php echo number_format($row['order_quantity']); ?></td>
                            <td style="color: #666;"><?php echo date('d M Y', strtotime($row['order_date'])); ?></td>
                            <td>
                                <div class="action-btns">
                                    <button type="button" class="btn-act btn-detail" onclick="showDetail(<?php echo htmlspecialchars(json_encode($row)); ?>)" title="ดูรายละเอียด">
                                        <i class="fas fa-eye"></i> ดูรายละเอียด
                                    </button>
                                    <button type="button" class="btn-act btn-approve" onclick="openApproveModal(<?php echo $row['order_id']; ?>)" title="อนุมัติ">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn-act btn-reject" onclick="openRejectPopup(<?php echo $row['order_id']; ?>)" title="ปฏิเสธ">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center" style="padding: 40px; color: #999;">
                            <i class="far fa-folder-open" style="font-size: 32px; display: block; margin-bottom: 10px; color: #ddd;"></i>
                            ไม่มีรายการรออนุมัติ
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== Section 2: Approval History ===== -->
    <div class="card" style="border: none; box-shadow: 0 2px 15px rgba(0,0,0,0.05);">
        <h5 style="font-size: 17px; color: #333; margin-bottom: 18px; border-bottom: 1px solid #eee; padding-bottom: 14px;">
            <i class="fas fa-history" style="color: #0066ff; margin-right: 8px;"></i> ประวัติการอนุมัติล่าสุด
        </h5>

        <!-- History Filters -->
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 18px; flex-wrap: wrap;">
            <select name="history_agent" onchange="document.getElementById('filterForm').submit()" style="padding: 8px 14px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #555; min-width: 180px; background: #fff;">
                <option value="">ซัพพลายเออร์ทั้งหมด</option>
                <?php foreach ($agents as $ag): ?>
                <option value="<?php echo $ag['agent_id']; ?>" <?php echo $history_agent_id == $ag['agent_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($ag['agent_name']); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="history_status" onchange="document.getElementById('filterForm').submit()" style="padding: 8px 14px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #555; min-width: 160px; background: #fff;">
                <option value="">สถานะทั้งหมด</option>
                <option value="Approved" <?php echo $history_status == 'Approved' ? 'selected' : ''; ?>>อนุมัติ</option>
                <option value="Rejected" <?php echo $history_status == 'Rejected' ? 'selected' : ''; ?>>ไม่อนุมัติ</option>
            </select>
        </div>

        <div style="font-size: 13px; color: #718096; margin-bottom: 12px;">ประวัติการอนุมัติล่าสุด</div>

        <div class="history-feed">
            <?php if ($result_history->num_rows > 0): ?>
                <?php while($hist = $result_history->fetch_assoc()):
                    $is_approved = ($hist['approval_status'] == 'Approved');
                    $statusClass = $is_approved ? 'approved' : 'rejected';
                    $statusText = $is_approved ? 'อนุมัติ' : 'ไม่อนุมัติ';
                    $hist_display_id = !empty($hist['order_number']) ? $hist['order_number'] : '#' . str_pad($hist['order_id'], 5, '0', STR_PAD_LEFT);
                ?>
                <div class="history-feed-item">
                    <div class="hf-badge <?php echo $statusClass; ?>">
                        <i class="fas <?php echo $is_approved ? 'fa-check' : 'fa-times'; ?>"></i>
                        <?php echo $statusText; ?>
                    </div>
                    <div class="hf-info">
                        <div class="hf-info-col1">
                            <div><span class="hf-id">#<?php echo $hist_display_id; ?></span><span class="hf-supplier"><?php echo htmlspecialchars($hist['agent_name']); ?></span></div>
                            <div class="hf-date"><i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($hist['approval_date'])); ?> <i class="far fa-clock" style="margin-left: 4px;"></i> <?php echo date('H:i', strtotime($hist['approval_date'])); ?></div>
                        </div>
                        <div class="hf-info-col2">
                            <?php echo date('d M Y H:i', strtotime($hist['approval_date'])); ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center text-muted" style="padding: 20px;">ยังไม่มีประวัติการดำเนินการ</div>
            <?php endif; ?>
        </div>
    </div>
</div>
</form>

<!-- ===== Detail Popup Overlay ===== -->
<div class="popup-overlay" id="popupOverlay" onclick="closeDetailPopup()"></div>

<!-- ===== Detail Popup ===== -->
<div class="detail-popup" id="detailPopup">
    <button class="popup-close" onclick="closeDetailPopup()">&times;</button>
    <div class="popup-title">ข้อมูลซัพพลายเออร์</div>

    <div class="detail-row">
        <span class="label" id="dp_agent_name">-</span>
        <span class="value" id="dp_quantity">-</span>
    </div>
    <div class="detail-row">
        <span class="label">
            # <span id="dp_order_id">-</span>
        </span>
        <span class="value <?php echo $credit_balance < $credit_min ? 'credit-low' : 'credit-ok'; ?>">
            <i class="fas fa-circle" style="font-size: 8px; margin-right: 4px;"></i> 
            <?php echo $credit_balance < $credit_min ? 'ใกล้ขั้นต่ำ' : 'ปกติ'; ?>
        </span>
    </div>

    <hr class="detail-divider">

    <div class="history-title">ประวัติการสั่งซื้อ (3 เดือน)</div>
    <div class="history-list" id="dp_history_list">
        <?php foreach ($recent_orders as $ro): ?>
        <div class="history-list-item">
            <span class="hl-id"># <?php echo $ro['order_number'] ?? '-'; ?></span>
            <span class="hl-qty"><?php echo number_format($ro['order_quantity']); ?> เครดิต สั่ง</span>
        </div>
        <?php endforeach; ?>
        <?php if (empty($recent_orders)): ?>
        <div style="text-align: center; color: #999; font-size: 13px; padding: 10px 0;">ไม่มีประวัติ</div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== Reject Popup ===== -->
<div class="reject-popup" id="rejectPopup">
    <button class="popup-close" onclick="closeRejectPopup()">&times;</button>
    <div class="popup-title">
        <i class="fas fa-times-circle"></i> ปฎิเสธคำสั่งซื้อ
    </div>
    <div class="popup-desc">โปรดยืนยันการปฏิเสธคำสั่งซื้อเครดิตนี้<br>กรุณาระบุเหตุผลในการปฏิเสธ</div>
    
    <form id="rejectForm" action="action/order_approve_db.php" method="post">
        <input type="hidden" name="order_id" id="reject_order_id">
        <input type="hidden" name="action" value="Rejected">
        <textarea name="note" id="reject_note" placeholder="กรอกเหตุผลในการปฏิเสธ" required></textarea>
        <div class="popup-actions" style="justify-content: flex-start; gap: 12px;">
            <button type="submit" class="btn-confirm-reject">ยืนยันการปฏิเสธ</button>
            <button type="button" class="btn-cancel" onclick="closeRejectPopup()">ยกเลิก</button>
        </div>
    </form>
</div>

<!-- ===== Approve Modal (Bootstrap) ===== -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="action/order_approve_db.php" method="post">
          <div class="modal-header">
            <h5 class="modal-title">ยืนยันการอนุมัติ</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="order_id" id="approve_order_id">
            <input type="hidden" name="action" value="Approved">
            <div class="form-group">
                <label>หมายเหตุเพิ่มเติ่ม (ถ้ามี)</label>
                <textarea name="note" class="form-control" rows="3"></textarea>
            </div>
            <p>คุณต้องการอนุมัติคำสั่งซื้อนี้ใช่หรือไม่?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
            <button type="submit" class="btn btn-success">ยืนยันอนุมัติ</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
// ===== Detail Popup =====
function showDetail(data) {
    document.getElementById('dp_agent_name').innerText = data.agent_name;
    document.getElementById('dp_quantity').innerText = Number(data.order_quantity).toLocaleString();
    let displayId = data.order_number ? data.order_number : data.order_id.toString().padStart(5, '0');
    document.getElementById('dp_order_id').innerText = displayId;

    document.getElementById('popupOverlay').style.display = 'block';
    document.getElementById('detailPopup').style.display = 'block';
}

function closeDetailPopup() {
    document.getElementById('popupOverlay').style.display = 'none';
    document.getElementById('detailPopup').style.display = 'none';
}

// ===== Approve Modal =====
function openApproveModal(id) {
    document.getElementById('approve_order_id').value = id;
    $('#approveModal').modal('show');
}

// ===== Reject Popup =====
function openRejectPopup(id) {
    document.getElementById('reject_order_id').value = id;
    document.getElementById('reject_note').value = '';
    document.getElementById('popupOverlay').style.display = 'block';
    document.getElementById('rejectPopup').style.display = 'block';
}

function closeRejectPopup() {
    document.getElementById('popupOverlay').style.display = 'none';
    document.getElementById('rejectPopup').style.display = 'none';
}

// ===== Date Range Picker Initialization =====
$(function() {
    var pendingDateRaw = document.getElementById('pending_date_range').value;
    var start, end;
    
    if (pendingDateRaw) {
        var dates = pendingDateRaw.split(' - ');
        start = moment(dates[0], 'DD/MM/YYYY');
        end = moment(dates[1], 'DD/MM/YYYY');
    } else {
        start = moment().subtract(29, 'days');
        end = moment();
    }

    var initialized = false;

    function cb(s, e) {
        $('#dateRangeText').html('วันที่ ' + s.format('DD/MM/YYYY') + ' - ' + e.format('DD/MM/YYYY'));
        $('#pending_date_range').val(s.format('DD/MM/YYYY') + ' - ' + e.format('DD/MM/YYYY'));
        
        if (initialized) {
            document.getElementById('filterForm').submit();
        }
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

    // Initial setup
    $('#dateRangeText').html('วันที่ ' + start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
    $('#pending_date_range').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
    initialized = true;
});
</script>
