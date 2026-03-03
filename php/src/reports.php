<style>
.report-header {
    margin-bottom: 24px;
}
.report-header h4 {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 20px;
}
.filter-label {
    font-size: 13.5px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.filter-label .radio-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #cbd5e1;
    display: inline-block;
}
.filter-label.active .radio-dot {
    background: #0066ff;
    box-shadow: 0 0 0 3px #e0e7ff;
}

.report-filters .form-control, .report-filters .custom-select {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 10px 14px;
    font-size: 14px;
    color: #475569;
    height: auto;
    background: #fff;
    box-shadow: none;
}
.report-filters .form-control:focus, .report-filters .custom-select:focus {
    border-color: #0066ff;
}

.btn-view-report {
    background: #0066ff;
    color: #fff;
    border: none;
    padding: 10px 24px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    transition: 0.2s;
}
.btn-view-report:hover { background: #0052cc; color: #fff; }

.btn-export {
    background: #fff;
    color: #334155;
    border: 1px solid #e2e8f0;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: 0.2s;
}
.btn-export:hover { background: #f8fafc; border-color: #cbd5e1; }

.report-result-title {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 16px;
    margin-top: 32px;
}

.report-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.report-table thead th {
    background: #e2e8f0;
    color: #334155;
    font-size: 13px;
    font-weight: 700;
    padding: 12px 16px;
    border: none;
    white-space: nowrap;
}
.report-table thead th:first-child { border-top-left-radius: 6px; border-bottom-left-radius: 6px; }
.report-table thead th:last-child { border-top-right-radius: 6px; border-bottom-right-radius: 6px; }

.report-table tbody td {
    padding: 14px 16px;
    font-size: 14px;
    color: #475569;
    border-bottom: 1px solid #f1f5f9;
}
.report-table tbody tr:nth-child(even) td {
    background-color: #f8fafc; /* Alternating row color */
}
.report-table tbody tr:hover td {
    background-color: #f1f5f9;
}

@media print {
    body { background-color: white !important; }
    .sidebar, .top-navbar, .report-filters, .report-header, .btn-export, .btn-view-report, .toolbar {
        display: none !important;
    }
    .content-page, .wrapper, .content-body {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        box-shadow: none !important;
        background: white !important;
    }
}
</style>

<!-- Standard Date Picker Inputs (Replaced DateRangePicker) -->
    
<?php
include 'connect.php';
$users_res = $conn->query("SELECT user_id, firstname, lastname FROM user ORDER BY firstname");
$customers_res = $conn->query("SELECT customer_id, customer_name FROM customer ORDER BY customer_name");
$agents_res = $conn->query("SELECT agent_id, agent_name FROM agent ORDER BY agent_name");
?>

<div class="content-body" style="background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
    
    <div class="report-header">
        <h4>สร้างประเภทรายงาน</h4>
    </div>

    <!-- Filters Section based on mockup -->
    <div class="report-filters">
        <div class="row align-items-end mb-3">
            <div class="col-md-3 mb-3">
                <div class="filter-label active"><span class="radio-dot"></span> เลือกประเภทธุรกรรม</div>
                <select id="reportType" class="custom-select" onchange="toggleFilterFields()">
                    <option value="combined">รายงานรวม (Combined)</option>
                    <option value="sales">รายงานการขาย</option>
                    <option value="orders">รายงานการสั่งซื้อ</option>
                    <?php if ($_SESSION['role'] == 'Admin'): ?>
                    <option value="logs">ประวัติการใช้งาน (System Logs)</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-5 mb-3">
                <div class="filter-label">ช่วงวันที่ (จาก/ถึง)</div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="date" id="startDate" class="form-control" value="<?php echo date('Y-m-01'); ?>">
                    <span style="color: #64748b; font-weight: 500;">-</span>
                    <input type="date" id="endDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="filter-label" style="visibility: hidden;">Spacer</div>
                <!-- Empty for layout alignment -->
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4 mb-3" id="supplierFilterGroup">
                <select id="agentId" class="custom-select">
                    <option value="">เลือกซัพพลายเออร์</option>
                    <?php while($ag = $agents_res->fetch_assoc()): ?>
                        <option value="<?php echo $ag['agent_id']; ?>"><?php echo $ag['agent_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3" id="customerFilterGroup">
                <select id="customerId" class="custom-select">
                    <option value="">เลือกลูกค้า</option>
                    <?php while($c = $customers_res->fetch_assoc()): ?>
                        <option value="<?php echo $c['customer_id']; ?>"><?php echo $c['customer_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'Manager'): ?>
            <div class="col-md-4 mb-3" id="employeeFilterGroup">
                <select id="userId" class="custom-select">
                    <option value="">เลือกพนักงาน</option>
                    <?php while($u = $users_res->fetch_assoc()): ?>
                        <option value="<?php echo $u['user_id']; ?>"><?php echo $u['firstname'] . ' ' . $u['lastname']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-12 text-right">
                <button type="button" class="btn-view-report mr-2" onclick="runReport()">ดูรายงาน</button>
                <button type="button" class="btn-export mr-2" onclick="exportPDF()" style="background-color: #ef4444; color: white;">ส่งออก PDF <i class="fas fa-file-pdf"></i></button>
                <button type="button" class="btn-export" style="background-color:green;color:white;" onclick="exportCSV()">ส่งออก Excel <i class="fas fa-file-excel"></i></button>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="report-result-title">ตัวอย่างรายงานที่สร้างขึ้น</div>
    
    <div class="table-responsive">
        <table class="report-table" id="reportTable">
            <thead>
                <tr id="tableHeader">
                    <th>วันที่</th>
                    <th>ประเภทธุรกรรม</th>
                    <th>รหัสคำสั่งซื้อ/ลูกค้า</th>
                    <th class="text-right">เครดิต/ปริมาณ</th>
                    <th class="text-right">จำนวนเงินทั้งหมด</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="5" class="text-center text-muted p-4">กำลังโหลดข้อมูล...</td></tr>
            </tbody>
        </table>
    </div>
    <div id="reportSummary" style="margin-top: 16px; font-weight: 700; text-align: right; color: #1e293b; font-size: 15px;"></div>

</div>

<script>
function toggleFilterFields() {
    const type = document.getElementById('reportType').value;
    const supGroup = document.getElementById('supplierFilterGroup');
    const custGroup = document.getElementById('customerFilterGroup');
    const empGroup = document.getElementById('employeeFilterGroup');
    
    if(type === 'sales') {
        if(supGroup) supGroup.style.display = 'none';
        if(custGroup) custGroup.style.display = 'block';
    } else if (type === 'orders') {
        if(supGroup) supGroup.style.display = 'block';
        if(custGroup) custGroup.style.display = 'none';
    } else {
        if(supGroup) supGroup.style.display = 'block';
        if(custGroup) custGroup.style.display = 'block';
    }
}

function runReport() {
    const type = document.getElementById('reportType').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const customerId = document.getElementById('customerId') ? document.getElementById('customerId').value : '';
    const agentId = document.getElementById('agentId') ? document.getElementById('agentId').value : '';
    const userId = document.getElementById('userId') ? document.getElementById('userId').value : '';

    document.getElementById('tableBody').innerHTML = '<tr><td colspan="6" class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><br>กำลังประมวลผล...</td></tr>';

    fetch(`action/report_api.php?type=${type}&start_date=${startDate}&end_date=${endDate}&user_id=${userId}&customer_id=${customerId}&agent_id=${agentId}`)
        .then(response => response.json())
        .then(data => {
            if(data.error) {
                document.getElementById('tableBody').innerHTML = `<tr><td colspan="6" class="text-center text-danger p-4">${data.error}</td></tr>`;
            } else {
                renderTable(type, data.data);
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('tableBody').innerHTML = '<tr><td colspan="6" class="text-center text-danger p-4">เกิดข้อผิดพลาดในการดึงข้อมูล</td></tr>';
        });
}

function renderTable(type, data) {
    const thead = document.getElementById('tableHeader');
    const tbody = document.getElementById('tableBody');
    const summary = document.getElementById('reportSummary');
    
    thead.innerHTML = '';
    tbody.innerHTML = '';
    summary.innerHTML = '';

    // Format Date function to match "15 มกราคม 2567"
    const thaiMonths = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
    const formatThaiDate = (dateString) => {
        const d = new Date(dateString);
        if(isNaN(d.getTime())) return dateString;
        return `${d.getDate()} ${thaiMonths[d.getMonth()]} ${d.getFullYear() + 543}`;
    };

    if (!data || data.length === 0) {
        thead.innerHTML = `<th>วันที่</th><th>ประเภทธุรกรรม</th><th>รหัสคำสั่งซื้อ/ลูกค้า</th><th class="text-right">เครดิต/ปริมาณ</th><th class="text-right">จำนวนเงินทั้งหมด</th>`;
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted p-5">ไม่พบข้อมูลในช่วงเวลาที่เลือก</td></tr>';
        return;
    }

    let headers = [];
    let rows = '';
    let totalAmt = 0;
    let totalQty = 0;

    if (type === 'combined') {
        headers = ['วันที่', 'ประเภทธุรกรรม', 'รหัสคำสั่งซื้อ/ลูกค้า', 'เครดิต/ปริมาณ', 'จำนวนเงินทั้งหมด'];
        
        data.forEach(row => {
            let amt = parseFloat(row.total_amount) || 0;
            let qty = parseFloat(row.qty) || 0;
            totalAmt += amt;
            totalQty += qty;
            
            rows += `<tr>
                <td>${formatThaiDate(row.t_date)}</td>
                <td style="color: ${row.transaction_type === 'Sales' ? '#059669' : '#0284c7'};">${row.transaction_type}</td>
                <td>${row.reference || '-'}</td>
                <td class="text-right">${Number(qty).toLocaleString()}</td>
                <td class="text-right">${amt > 0 ? Number(amt).toLocaleString(undefined, {minimumFractionDigits: 2}) : '-'}</td>
            </tr>`;
        });
        summary.innerHTML = `ยอดรวมปริมาณเครดิต : ${totalQty.toLocaleString()} | ยอดรวมเงิน : ${totalAmt.toLocaleString(undefined, {minimumFractionDigits: 2})} บาท`;

    } else if (type === 'sales') {
        headers = ['วันที่', 'ประเภทธุรกรรม', 'ลูกค้า', 'เครดิตที่ขาย', 'จำนวนเงินทั้งหมด'];
        
        data.forEach(row => {
            let amt = parseFloat(row.sale_amount) || 0;
            totalAmt += amt;
            rows += `<tr>
                <td>${formatThaiDate(row.sale_date)}</td>
                <td style="color: #059669;">Sales</td>
                <td>${row.customer_name}</td>
                <td class="text-right">${Number(row.sale_credit).toLocaleString()}</td>
                <td class="text-right">${Number(amt).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
            </tr>`;
        });
        summary.innerHTML = `ยอดรวมทั้งหมด: ${totalAmt.toLocaleString(undefined, {minimumFractionDigits: 2})} บาท`;

    } else if (type === 'orders') {
        headers = ['วันที่', 'ประเภทธุรกรรม', 'รหัสคำสั่งซื้อ/ซัพพลายเออร์', 'ปริมาณ', 'สถานะ'];
        
        data.forEach(row => {
            let statusBadge = '';
            if(row.order_status == 'Approved') statusBadge = '<span style="color: #059669; font-weight: 500;">อนุมัติแล้ว</span>';
            else if(row.order_status == 'Pending') statusBadge = '<span style="color: #d97706; font-weight: 500;">รอดำเนินการ</span>';
            else if(row.order_status == 'Rejected') statusBadge = '<span style="color: #dc2626; font-weight: 500;">ถูกปฏิเสธ</span>';
            else if(row.order_status == 'Received') statusBadge = '<span style="color: #0284c7; font-weight: 500;">ได้รับเครดิตแล้ว</span>';

            let orderId = row.order_number ? row.order_number : '#' + String(row.order_id).padStart(5, '0');
            
            rows += `<tr>
                <td>${formatThaiDate(row.order_date)}</td>
                <td style="color: #0284c7;">Purchase</td>
                <td>${orderId} / ${row.agent_name}</td>
                <td class="text-right">${Number(row.order_quantity).toLocaleString()}</td>
                <td class="text-right">${statusBadge}</td>
            </tr>`;
        });
    } else if (type === 'logs') {
        headers = ['เวลา', 'พนักงาน', 'การทำงาน', 'รายละเอียด', 'IP Address'];
        
        const actionColors = {
            'Login':           { bg: '#dbeafe', color: '#1d4ed8' },
            'Create Sale':     { bg: '#d1fae5', color: '#065f46' },
            'Create Order':    { bg: '#d1fae5', color: '#065f46' },
            'Create User':     { bg: '#d1fae5', color: '#065f46' },
            'Create Customer': { bg: '#d1fae5', color: '#065f46' },
            'Order Approved':  { bg: '#d1fae5', color: '#059669' },
            'Order Rejected':  { bg: '#fee2e2', color: '#dc2626' },
            'Cancel Order':    { bg: '#fee2e2', color: '#dc2626' },
            'Credit Received': { bg: '#cffafe', color: '#0e7490' },
            'View Report':     { bg: '#f3f4f6', color: '#6b7280' },
            'Export Report':   { bg: '#f3f4f6', color: '#6b7280' },
        };
        
        data.forEach(row => {
            const ac = actionColors[row.action] || { bg: '#f1f5f9', color: '#475569' };
            const badge = `<span style="background: ${ac.bg}; color: ${ac.color}; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;">${row.action}</span>`;
            rows += `<tr>
                <td>${row.created_at}</td>
                <td>${row.firstname} ${row.lastname}</td>
                <td>${badge}</td>
                <td style="font-size: 13px; color: #64748b;">${row.details}</td>
                <td style="font-size: 12px; color: #94a3b8;">${row.ip_address}</td>
            </tr>`;
        });
    }

    headers.forEach(h => {
        thead.innerHTML += `<th class="${h.includes('จำนวนเงิน') || h.includes('เครดิต') || h.includes('ปริมาณ') || h.includes('สถานะ') ? 'text-right' : ''}">${h}</th>`;
    });
    tbody.innerHTML = rows;
}

function exportCSV() {
    const type = document.getElementById('reportType').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const customerId = document.getElementById('customerId') ? document.getElementById('customerId').value : '';
    const agentId = document.getElementById('agentId') ? document.getElementById('agentId').value : '';
    const userId = document.getElementById('userId') ? document.getElementById('userId').value : '';
    
    window.location.href = `action/report_export.php?type=${type}&start_date=${startDate}&end_date=${endDate}&user_id=${userId}&customer_id=${customerId}&agent_id=${agentId}`;
}

function exportPDF() {
    const tBody = document.getElementById('tableBody').textContent || '';
    if (tBody.includes('ไม่พบข้อมูล') || tBody.includes('กำลังโหลดข้อมูล')) {
        Swal.fire('ประมวลผลไม่สำเร็จ', 'ไม่มีข้อมูลสำหรับสร้างเอกสาร PDF โปรดกดดูรายงานก่อน', 'warning');
        return;
    }
    window.print();
}

document.addEventListener('DOMContentLoaded', function() {
    toggleFilterFields();
    runReport(); 
});
</script>
