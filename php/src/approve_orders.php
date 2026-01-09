<?php
include 'connect.php';

// Pending Orders
$sql_pending = "SELECT pc.*, a.agent_name 
                FROM purchase_credit pc 
                JOIN agent a ON pc.agent_id = a.agent_id 
                WHERE pc.order_status = 'Pending' 
                ORDER BY pc.order_date ASC";
$result_pending = $conn->query($sql_pending);

// History (Last 5 processed actions) from Approve table
// Join approve -> purchase_credit -> agent
$sql_history = "SELECT ap.*, pc.order_id, a.agent_name 
                FROM approve ap 
                JOIN purchase_credit pc ON ap.order_id = pc.order_id 
                JOIN agent a ON pc.agent_id = a.agent_id 
                ORDER BY ap.approval_date DESC, ap.approval_id DESC 
                LIMIT 5";
$result_history = $conn->query($sql_history);
?>

<div class="content-body">
    <!-- Approval Table -->
    <div class="card" style="margin-bottom: 2rem; border: none; box-shadow: 0 2px 15px rgba(0,0,0,0.05);">
        <h5 style="margin-bottom: 20px; font-size: 18px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 15px;">
            <i class="fas fa-clipboard-check" style="color: #0066ff; margin-right: 10px;"></i> รายการรออนุมัติ
        </h5>
        
        <div class="table-responsive">
            <table class="table table-hover" style="margin-top: 0;">
                <thead class="thead-light">
                    <tr>
                        <th style="border-top: none; border-bottom: 2px solid #eef2f7; color: #555; font-weight: 600;">ซัพพลายเออร์</th>
                        <th style="border-top: none; border-bottom: 2px solid #eef2f7; color: #555; font-weight: 600;">ปริมาณ</th>
                        <th style="border-top: none; border-bottom: 2px solid #eef2f7; color: #555; font-weight: 600;">วันที่</th>
                        <th style="border-top: none; border-bottom: 2px solid #eef2f7; color: #555; font-weight: 600;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_pending->num_rows > 0): ?>
                        <?php while($row = $result_pending->fetch_assoc()): ?>
                        <tr>
                            <td class="align-middle">
                                <div style="font-weight: 600; color: #333;"><?php echo $row['agent_name']; ?></div>
                                <small class="text-muted"><i class="fas fa-tag" style="font-size: 10px; margin-right: 3px;"></i> ID: <?php echo $row['order_id']; ?></small>
                            </td>
                            <td class="align-middle"><span style="background: #e3f2fd; color: #0066ff; padding: 4px 10px; border-radius: 20px; font-size: 13px; font-weight: 600;"><?php echo number_format($row['order_quantity']); ?></span></td>
                            <td class="align-middle" style="color: #666;"><?php echo date('d M Y', strtotime($row['order_date'])); ?></td>
                            <td class="align-middle">
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewDetails(<?php echo htmlspecialchars(json_encode($row)); ?>)" title="ดูรายละเอียด" style="margin-right: 5px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-success" onclick="openApproveModal(<?php echo $row['order_id']; ?>)" title="อนุมัติ" style="margin-right: 5px; box-shadow: 0 2px 5px rgba(40, 167, 69, 0.3);">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="openRejectModal(<?php echo $row['order_id']; ?>)" title="ปฏิเสธ" style="box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3);">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center" style="padding: 40px; color: #999; background-color: #fcfcfc;">
                            <i class="far fa-folder-open" style="font-size: 32px; display: block; margin-bottom: 10px; color: #ddd;"></i>
                            ไม่มีรายการรออนุมัติ
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status History Log -->
    <div class="card" style="border: none; box-shadow: 0 2px 15px rgba(0,0,0,0.05);">
        <h5 style="margin-bottom: 20px; font-size: 18px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 15px;">
            <i class="fas fa-history" style="color: #0066ff; margin-right: 10px;"></i> ประวัติการอนุมัติล่าสุด
        </h5>
        
        <div class="activity-feed">
            <?php if ($result_history->num_rows > 0): ?>
                <?php while($hist = $result_history->fetch_assoc()): ?>
                    <?php 
                        $statusClass = ($hist['approval_status'] == 'Approved') ? 'feed-approved' : 'feed-rejected';
                        $iconClass = ($hist['approval_status'] == 'Approved') ? 'fa-check' : 'fa-times';
                        $statusText = ($hist['approval_status'] == 'Approved') ? 'อนุมัติคำสั่งซื้อ' : 'ปฏิเสธคำสั่งซื้อ';
                    ?>
                    <div class="feed-item">
                        <div class="feed-icon <?php echo $statusClass; ?>">
                            <i class="fas <?php echo $iconClass; ?>"></i>
                        </div>
                        <div class="feed-content">
                            <div class="feed-text">
                                <span class="feed-action"><?php echo $statusText; ?></span>
                                <span class="feed-order">#<?php echo $hist['order_id']; ?></span>
                            </div>
                            <div class="feed-meta">
                                <span class="feed-agent"><i class="fas fa-store"></i> <?php echo $hist['agent_name']; ?></span>
                                <span class="feed-date"><i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($hist['approval_date'])); ?></span>
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

<!-- View Details Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">รายละเอียดคำสั่งซื้อ</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p><strong>Order ID:</strong> <span id="view_order_id"></span></p>
        <p><strong>Agent:</strong> <span id="view_agent_name"></span></p>
        <p><strong>Quantity:</strong> <span id="view_quantity"></span></p>
        <p><strong>Date:</strong> <span id="view_date"></span></p>
        <p><strong>Status:</strong> <span id="view_status"></span></p>
        <p><strong>Attachment:</strong> <span id="view_attachment"></span></p>
        <p><strong>Note:</strong> <span id="view_note"></span></p>
      </div>
    </div>
  </div>
</div>

<!-- Approve Modal -->
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

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="action/order_approve_db.php" method="post">
          <div class="modal-header">
            <h5 class="modal-title">ยืนยันการปฏิเสธ</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="order_id" id="reject_order_id">
            <input type="hidden" name="action" value="Rejected">
            <div class="form-group">
                <label>เหตุผลการปฏิเสธ <span class="text-danger">*</span></label>
                <textarea name="note" class="form-control" rows="3" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
            <button type="submit" class="btn btn-danger">ยืนยันปฏิเสธ</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
function viewDetails(data) {
    document.getElementById('view_order_id').innerText = data.order_id;
    document.getElementById('view_agent_name').innerText = data.agent_name;
    document.getElementById('view_quantity').innerText = data.order_quantity;
    document.getElementById('view_date').innerText = data.order_date;
    document.getElementById('view_status').innerText = data.order_status;
    
    // Attachment link
    let attachSpan = document.getElementById('view_attachment');
    if(data.order_attachment) {
        attachSpan.innerHTML = '<a href="'+data.order_attachment+'" target="_blank">View File</a>';
    } else {
        attachSpan.innerText = '-';
    }

    // Note
    document.getElementById('view_note').innerText = data.order_note ? data.order_note : '-';

    $('#viewModal').modal('show');
}

function openApproveModal(id) {
    document.getElementById('approve_order_id').value = id;
    $('#approveModal').modal('show');
}

function openRejectModal(id) {
    document.getElementById('reject_order_id').value = id;
    $('#rejectModal').modal('show');
}
</script>
