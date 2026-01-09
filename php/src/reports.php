<div class="content-body">
    <!-- Filters Card -->
    <div class="card" style="margin-bottom: 2rem; border: none; box-shadow: 0 2px 15px rgba(0,0,0,0.05);">
        <h5 style="margin-bottom: 20px; font-size: 18px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 15px;">
            <i class="fas fa-filter" style="color: #0066ff; margin-right: 10px;"></i> กรองข้อมูลรายงาน
        </h5>
        
        <form id="filterForm">
            <div class="form-row align-items-end">
                <div class="col-md-3 mb-3">
                    <label class="form-label text-muted">ประเภทรายงาน</label>
                    <div class="custom-select-wrapper">
                        <select id="reportType" name="type" class="form-control" onchange="runReport()">
                            <option value="sales">รายงานยอดขาย (Sales)</option>
                            <option value="orders">รายงานการสั่งซื้อ (Orders)</option>
                            <?php if ($_SESSION['role'] == 'Admin'): ?>
                            <option value="logs">ประวัติการใช้งาน (System Logs)</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label text-muted">ตั้งแต่วันที่</label>
                    <input type="date" id="startDate" name="start_date" class="form-control" value="<?php echo date('Y-m-01'); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label text-muted">ถึงวันที่</label>
                    <input type="date" id="endDate" name="end_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <?php if ($_SESSION['role'] == 'Admin'): ?>
                <!-- Admin specific filter for users -->
                <div class="col-md-3 mb-3">
                    <label class="form-label text-muted">พนักงาน/ผู้ใช้</label>
                    <?php
                        // Fetch users for dropdown
                        include 'connect.php';
                        $users_res = $conn->query("SELECT user_id, firstname, lastname FROM users ORDER BY firstname");
                    ?>
                     <div class="custom-select-wrapper">
                        <select id="userId" name="user_id" class="form-control">
                            <option value="">-- ทั้งหมด --</option>
                            <?php while($u = $users_res->fetch_assoc()): ?>
                                <option value="<?php echo $u['user_id']; ?>"><?php echo $u['firstname'] . ' ' . $u['lastname']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <div class="col-md-12 text-right">
                    <button type="button" class="btn btn-secondary mr-2" onclick="resetFilters()"><i class="fas fa-undo"></i> ล้างค่า</button>
                    <button type="button" class="btn btn-primary" onclick="runReport()"><i class="fas fa-search"></i> ค้นหา</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Section -->
    <div class="card" style="border: none; box-shadow: 0 2px 15px rgba(0,0,0,0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
            <h5 style="margin: 0; font-size: 18px; color: #333;"><i class="fas fa-table" style="color: #0066ff; margin-right: 10px;"></i> ผลลัพธ์รายงาน</h5>
            <button type="button" class="btn btn-success" onclick="exportCSV()"><i class="fas fa-file-excel"></i> Export Excel</button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="reportTable">
                <thead class="thead-light">
                    <tr id="tableHeader">
                        <!-- Dynamic Headers -->
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Dynamic Body -->
                    <tr><td colspan="5" class="text-center text-muted p-4">กรุณากดค้นหาเพื่อดูข้อมูล</td></tr>
                </tbody>
            </table>
        </div>
        
        <!-- Simple summary footer -->
        <div id="reportSummary" style="margin-top: 15px; font-weight: bold; text-align: right; color: #333;"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    runReport(); // Auto run on load
});

function runReport() {
    const type = document.getElementById('reportType').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    let userId = '';
    
    if (document.getElementById('userId')) {
        userId = document.getElementById('userId').value;
    }

    // Show Loading
    document.getElementById('tableBody').innerHTML = '<tr><td colspan="10" class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><br>กำลังประมวลผล...</td></tr>';

    fetch(`action/report_api.php?type=${type}&start_date=${startDate}&end_date=${endDate}&user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            renderTable(type, data.data);
        })
        .catch(err => {
            console.error(err);
            document.getElementById('tableBody').innerHTML = '<tr><td colspan="10" class="text-center text-danger p-4">เกิดข้อผิดพลาดในการดึงข้อมูล</td></tr>';
        });
}

function renderTable(type, data) {
    const thead = document.getElementById('tableHeader');
    const tbody = document.getElementById('tableBody');
    const summary = document.getElementById('reportSummary');
    
    thead.innerHTML = '';
    tbody.innerHTML = '';
    summary.innerHTML = '';

    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted p-5">ไม่พบข้อมูลในช่วงเวลาที่เลือก</td></tr>';
        return;
    }

    let headers = [];
    let rows = '';
    let totalAmount = 0;

    if (type === 'sales') {
        headers = ['วันที่ขาย', 'ลูกค้า', 'ผู้ขาย', 'เครดิตที่ขาย', 'ยอดเงิน (บาท)'];
        
        data.forEach(row => {
            totalAmount += parseFloat(row.sale_amount);
            rows += `<tr>
                <td>${row.sale_date}</td>
                <td>${row.customer_name}</td>
                <td>${row.firstname} ${row.lastname}</td>
                <td class="text-right">${Number(row.sale_credit).toLocaleString()}</td>
                <td class="text-right">${Number(row.sale_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
            </tr>`;
        });

        summary.innerHTML = `ยอดรวมทั้งหมด: ${totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2})} บาท`;

    } else if (type === 'orders') {
        headers = ['วันที่สั่งซื้อ', 'รหัสคำสั่งซื้อ', 'ซัพพลายเออร์', 'ผู้สั่งซื้อ', 'ปริมาณ', 'สถานะ'];
        
        let totalQty = 0;
        data.forEach(row => {
            totalQty += parseInt(row.order_quantity);
            let statusBadge = '';
            if(row.order_status == 'Approved') statusBadge = '<span class="badge badge-success">อนุมัติแล้ว</span>';
            else if(row.order_status == 'Pending') statusBadge = '<span class="badge badge-warning">รอดำเนินการ</span>';
            else if(row.order_status == 'Rejected') statusBadge = '<span class="badge badge-danger">ถูกปฏิเสธ</span>';
            else if(row.order_status == 'Received') statusBadge = '<span class="badge badge-info">ได้รับเครดิตแล้ว</span>';

            rows += `<tr>
                <td>${row.order_date}</td>
                <td>#${String(row.order_id).padStart(5, '0')}</td>
                <td>${row.agent_name}</td>
                <td>${row.firstname} ${row.lastname}</td>
                <td class="text-right">${Number(row.order_quantity).toLocaleString()}</td>
                <td>${statusBadge}</td>
            </tr>`;
        });
        
        summary.innerHTML = `รวมปริมาณการสั่งซื้อ: ${totalQty.toLocaleString()} เครดิต`;

    } else if (type === 'logs') {
        headers = ['เวลา', 'ผู้ใช้งาน', 'Action', 'รายละเอียด', 'IP Address'];
        
        data.forEach(row => {
            rows += `<tr>
                <td>${row.created_at}</td>
                <td>${row.firstname} ${row.lastname}</td>
                <td><span class="badge badge-secondary">${row.action}</span></td>
                <td><small>${row.details}</small></td>
                <td>${row.ip_address}</td>
            </tr>`;
        });
    }

    // Render Headers
    headers.forEach(h => {
        thead.innerHTML += `<th class="${h.includes('amount') || h.includes('เครดิต') || h.includes('ปริมาณ') ? 'text-right' : ''}">${h}</th>`;
    });

    // Render Body
    tbody.innerHTML = rows;
}

function exportCSV() {
    const type = document.getElementById('reportType').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    let userId = '';
    if (document.getElementById('userId')) {
        userId = document.getElementById('userId').value;
    }
    
    window.location.href = `action/report_export.php?type=${type}&start_date=${startDate}&end_date=${endDate}&user_id=${userId}`;
}

function resetFilters() {
    document.getElementById('startDate').value = "<?php echo date('Y-m-01'); ?>";
    document.getElementById('endDate').value = "<?php echo date('Y-m-d'); ?>";
    if (document.getElementById('userId')) document.getElementById('userId').value = "";
    runReport();
}
</script>
