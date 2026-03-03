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
.report-table thead th.text-center,
.report-table tbody td.text-center {
    text-align: center !important;
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
                    <th class="text-center">เครดิต/ปริมาณ</th>
                    <th class="text-center">จำนวนเงินทั้งหมด</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="5" class="text-center text-muted p-4">กำลังโหลดข้อมูล...</td></tr>
            </tbody>
        </table>
    </div>
    <div id="reportSummary" style="margin-top: 16px; font-weight: 700; text-align: right; color: #1e293b; font-size: 15px;"></div>
    <div id="paginationContainer" style="display: flex; justify-content: center; align-items: center; gap: 6px; margin-top: 16px; flex-wrap: wrap;"></div>

</div>

<script>
function toggleFilterFields() {
    var type = document.getElementById('reportType').value;
    var supGroup = document.getElementById('supplierFilterGroup');
    var custGroup = document.getElementById('customerFilterGroup');
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

var PAGE_SIZE = 15;
var _allData = [];
var _curType = '';
var _curPage = 1;

var _thaiM = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
var _thaiMS= ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];

function formatThaiDate(s) {
    var d = new Date(s);
    if (isNaN(d.getTime())) return s || '-';
    return d.getDate() + ' ' + _thaiM[d.getMonth()] + ' ' + (d.getFullYear()+543);
}
function formatThaiDateTime(s) {
    if (!s) return '-';
    var d = new Date(s);
    if (isNaN(d.getTime())) return s;
    var dd = String(d.getDate()).padStart(2,'0');
    var hh = String(d.getHours()).padStart(2,'0');
    var mm = String(d.getMinutes()).padStart(2,'0');
    return dd + ' ' + _thaiMS[d.getMonth()] + ' ' + (d.getFullYear()+543) + ' ' + hh + ':' + mm;
}

function runReport() {
    var type = document.getElementById('reportType').value;
    var startDate = document.getElementById('startDate').value;
    var endDate = document.getElementById('endDate').value;
    var customerId = document.getElementById('customerId') ? document.getElementById('customerId').value : '';
    var agentId = document.getElementById('agentId') ? document.getElementById('agentId').value : '';
    var userId = document.getElementById('userId') ? document.getElementById('userId').value : '';

    document.getElementById('tableBody').innerHTML = '<tr><td colspan="5" class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><br>กำลังประมวลผล...</td></tr>';
    document.getElementById('paginationContainer').innerHTML = '';
    document.getElementById('reportSummary').innerHTML = '';

    fetch('action/report_api.php?type='+type+'&start_date='+startDate+'&end_date='+endDate+'&user_id='+userId+'&customer_id='+customerId+'&agent_id='+agentId)
        .then(function(r){ return r.json(); })
        .then(function(data){
            if(data.error) {
                document.getElementById('tableBody').innerHTML = '<tr><td colspan="5" class="text-center text-danger p-4">'+data.error+'</td></tr>';
            } else {
                renderTable(type, data.data);
            }
        })
        .catch(function(err){
            console.error(err);
            document.getElementById('tableBody').innerHTML = '<tr><td colspan="5" class="text-center text-danger p-4">เกิดข้อผิดพลาดในการดึงข้อมูล</td></tr>';
        });
}

function renderTable(type, data) {
    _allData = data || [];
    _curType = type;
    _curPage = 1;

    var thead = document.getElementById('tableHeader');
    var summary = document.getElementById('reportSummary');
    thead.innerHTML = '';
    summary.innerHTML = '';
    document.getElementById('paginationContainer').innerHTML = '';

    var headers = [];
    if (type === 'combined') headers = ['วันที่','ประเภทธุรกรรม','รหัสคำสั่งซื้อ/ลูกค้า','เครดิต/ปริมาณ','จำนวนเงินทั้งหมด'];
    else if (type === 'sales')  headers = ['วันที่','ประเภทธุรกรรม','ลูกค้า','เครดิตที่ขาย','จำนวนเงินทั้งหมด'];
    else if (type === 'orders') headers = ['วันที่','ประเภทธุรกรรม','รหัสคำสั่งซื้อ/ซัพพลายเออร์','ปริมาณ','สถานะ'];
    else if (type === 'logs')   headers = ['เวลา','พนักงาน','การทำงาน','รายละเอียด','IP Address'];

    headers.forEach(function(h){
        var align = (h.indexOf('จำนวนเงน')>=0||h.indexOf('เครดต')>=0||h.indexOf('ปรมาณ')>=0||h.indexOf('สถานะ')>=0)?'text-center':'';
        thead.innerHTML += '<th class="'+align+'">'+h+'</th>';
    });

    if (!_allData.length) {
        document.getElementById('tableBody').innerHTML = '<tr><td colspan="5" class="text-center text-muted p-5">ไม่พบข้อมูลในช่วงเวลาที่เลือก</td></tr>';
        return;
    }

    if (type === 'combined') {
        var tA=0,tQ=0;
        _allData.forEach(function(r){ tA+=parseFloat(r.total_amount)||0; tQ+=parseFloat(r.qty)||0; });
        summary.innerHTML = 'ยอดรวมปริมาณเครดิต : '+tQ.toLocaleString()+' | ยอดรวมเงิน : '+tA.toLocaleString(undefined,{minimumFractionDigits:2})+' บาท | ทั้งหมด '+_allData.length+' รายการ';
    } else if (type === 'sales') {
        var tA2=0;
        _allData.forEach(function(r){ tA2+=parseFloat(r.sale_amount)||0; });
        summary.innerHTML = 'ยอดรวมทั้งหมด: '+tA2.toLocaleString(undefined,{minimumFractionDigits:2})+' บาท | ทั้งหมด '+_allData.length+' รายการ';
    } else {
        summary.innerHTML = 'ทั้งหมด '+_allData.length+' รายการ';
    }

    renderPage(1);
}

function renderPage(page) {
    var totalPages = Math.ceil(_allData.length / PAGE_SIZE);
    _curPage = Math.max(1, Math.min(page, totalPages));
    var slice = _allData.slice((_curPage-1)*PAGE_SIZE, _curPage*PAGE_SIZE);

    var actionColors = {
        'Login':           {bg:'#dbeafe',c:'#1d4ed8'},
        'Create Sale':     {bg:'#d1fae5',c:'#065f46'},
        'Create Order':    {bg:'#d1fae5',c:'#065f46'},
        'Create User':     {bg:'#d1fae5',c:'#065f46'},
        'Create Customer': {bg:'#d1fae5',c:'#065f46'},
        'Order Approved':  {bg:'#d1fae5',c:'#059669'},
        'Order Rejected':  {bg:'#fee2e2',c:'#dc2626'},
        'Cancel Order':    {bg:'#fee2e2',c:'#dc2626'},
        'Credit Received': {bg:'#cffafe',c:'#0e7490'},
        'View Report':     {bg:'#f3f4f6',c:'#6b7280'},
        'Export Report':   {bg:'#f3f4f6',c:'#6b7280'}
    };

    var rows = '';
    var type = _curType;
    slice.forEach(function(row){
        if (type === 'combined') {
            var amt=parseFloat(row.total_amount)||0, qty=parseFloat(row.qty)||0;
            var typeLabel = row.transaction_type==='Sales'
                ? '<span style="color:#059669"><i class="fas fa-tag" style="margin-right:4px"></i>ขาย</span>'
                : '<span style="color:#0284c7"><i class="fas fa-shopping-bag" style="margin-right:4px"></i>สั่งซื้อ</span>';
            rows += '<tr><td>'+formatThaiDate(row.t_date)+'</td><td>'+typeLabel+'</td><td>'+(row.reference||'-')+'</td>'
                +'<td class="text-center">'+Number(qty).toLocaleString()+'</td>'
                +'<td class="text-center">'+(amt>0?Number(amt).toLocaleString(undefined,{minimumFractionDigits:2}):'-')+'</td></tr>';
        } else if (type === 'sales') {
            var amt2=parseFloat(row.sale_amount)||0;
            rows += '<tr><td>'+formatThaiDate(row.sale_date)+'</td><td style="color:#059669">Sales</td><td>'+row.customer_name+'</td>'
                +'<td class="text-center">'+Number(row.sale_credit).toLocaleString()+'</td>'
                +'<td class="text-center">'+Number(amt2).toLocaleString(undefined,{minimumFractionDigits:2})+'</td></tr>';
        } else if (type === 'orders') {
            var sb='';
            if(row.order_status=='Approved') sb='<span style="color:#059669;font-weight:500">อนุมัติแล้ว</span>';
            else if(row.order_status=='Pending') sb='<span style="color:#d97706;font-weight:500">รอดำเนินการ</span>';
            else if(row.order_status=='Rejected') sb='<span style="color:#dc2626;font-weight:500">ถูกปฏิเสธ</span>';
            else if(row.order_status=='Received') sb='<span style="color:#0284c7;font-weight:500">ได้รับเครดิตแล้ว</span>';
            var oid = row.order_number ? row.order_number : '#'+String(row.order_id).padStart(5,'0');
            rows += '<tr><td>'+formatThaiDate(row.order_date)+'</td><td style="color:#0284c7">Purchase</td>'
                +'<td>'+oid+' / '+row.agent_name+'</td>'
                +'<td class="text-center">'+Number(row.order_quantity).toLocaleString()+'</td>'
                +'<td class="text-center">'+sb+'</td></tr>';
        } else if (type === 'logs') {
            var ac = actionColors[row.action] || {bg:'#f1f5f9',c:'#475569'};
            var badge = '<span style="background:'+ac.bg+';color:'+ac.c+';padding:2px 10px;border-radius:999px;font-size:12px;font-weight:600">'+row.action+'</span>';
            rows += '<tr><td style="white-space:nowrap">'+formatThaiDateTime(row.timestamp)+'</td>'
                +'<td>'+row.firstname+' '+row.lastname+'</td>'
                +'<td>'+badge+'</td>'
                +'<td style="font-size:13px;color:#64748b">'+(row.details||'-')+'</td>'
                +'<td style="font-size:12px;color:#94a3b8">'+(row.ip_address||'-')+'</td></tr>';
        }
    });

    document.getElementById('tableBody').innerHTML = rows || '<tr><td colspan="5" class="text-center text-muted p-4">ไม่พบข้อมูล</td></tr>';
    renderPagination(_curPage, totalPages, _allData.length);
}

function renderPagination(page, totalPages, totalItems) {
    var container = document.getElementById('paginationContainer');
    container.innerHTML = '';
    if (totalPages <= 1) return;

    var startItem = (page-1)*PAGE_SIZE+1;
    var endItem = Math.min(page*PAGE_SIZE, totalItems);

    var prev = document.createElement('button');
    prev.innerHTML = '<i class="fas fa-chevron-left"></i>';
    prev.disabled = (page === 1);
    prev.onclick = function(){ renderPage(page-1); };
    container.appendChild(prev);

    var startP = Math.max(1, page-2);
    var endP = Math.min(totalPages, startP+4);
    startP = Math.max(1, endP-4);

    if (startP > 1) {
        addPageBtn(container, 1, page);
        if (startP > 2) { var d=document.createElement('span'); d.className='page-info'; d.textContent=''; container.appendChild(d); }
    }
    for (var i=startP; i<=endP; i++) addPageBtn(container, i, page);
    if (endP < totalPages) {
        if (endP < totalPages-1) { var d2=document.createElement('span'); d2.className='page-info'; d2.textContent=''; container.appendChild(d2); }
        addPageBtn(container, totalPages, page);
    }

    var next = document.createElement('button');
    next.innerHTML = '<i class="fas fa-chevron-right"></i>';
    next.disabled = (page === totalPages);
    next.onclick = function(){ renderPage(page+1); };
    container.appendChild(next);

    var info = document.createElement('span');
    info.className = 'page-info';
    info.textContent = 'แสดง '+startItem+'-'+endItem+' จาก '+totalItems+' รายการ';
    container.appendChild(info);
}

function addPageBtn(container, i, cur) {
    var btn = document.createElement('button');
    btn.textContent = i;
    if (i === cur) btn.classList.add('active');
    btn.onclick = function(){ renderPage(i); };
    container.appendChild(btn);
}

function exportCSV() {
    var type = document.getElementById('reportType').value;
    var startDate = document.getElementById('startDate').value;
    var endDate = document.getElementById('endDate').value;
    var customerId = document.getElementById('customerId') ? document.getElementById('customerId').value : '';
    var agentId = document.getElementById('agentId') ? document.getElementById('agentId').value : '';
    var userId = document.getElementById('userId') ? document.getElementById('userId').value : '';
    window.location.href = 'action/report_export.php?type='+type+'&start_date='+startDate+'&end_date='+endDate+'&user_id='+userId+'&customer_id='+customerId+'&agent_id='+agentId;
}

function exportPDF() {
    var tBody = document.getElementById('tableBody').textContent || '';
    if (tBody.indexOf('ไม่พบข้อมูล')>=0 || tBody.indexOf('กำลังประมวลผล')>=0 || tBody.indexOf('กำลังโหลดข้อมูล')>=0) {
        Swal.fire('ประมวลผลไม่สำเร็จ','ไม่มีข้อมูลสำหรับสร้างเอกสาร PDF โปรดกดดูรายงานก่อน','warning');
        return;
    }
    window.print();
}

document.addEventListener('DOMContentLoaded', function() {
    toggleFilterFields();
    runReport();
});
</script>

<style>
#paginationContainer button {
    padding: 6px 13px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #475569;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: 0.15s;
    font-family: 'Prompt', sans-serif;
}
#paginationContainer button:hover:not(:disabled) { background: #f1f5f9; border-color: #cbd5e1; }
#paginationContainer button.active               { background: #0066ff; color: #fff; border-color: #0066ff; }
#paginationContainer button:disabled             { opacity: 0.4; cursor: default; }
#paginationContainer .page-info                  { font-size: 13px; color: #64748b; padding: 0 6px; }
</style>