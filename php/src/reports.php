<div class="content-body">
    <div class="card" style="max-width: 900px; padding: 30px;">
        
        <h5 style="margin-bottom: 15px; font-size: 16px;">สร้างประเภทรายงาน</h5>
        
        <form>
            <div class="form-row" style="margin-bottom: 10px;">
                <div class="col-md-6">
                    <div class="filter-group-label"><i class="fas fa-circle" style="color: #ccc; font-size: 10px; margin-right: 5px;"></i> เลือกประเภทรายงาน</div>
                </div>
                    <div class="col-md-6">
                    <div class="filter-group-label">ช่วงวันที่ (จาก/ถึง)</div>
                </div>
            </div>

            <div class="filter-grid">
                <input type="text" class="filter-input" placeholder="รายงานการขาย">
                <input type="text" class="filter-input" placeholder="รายงานยอดเครดิต">
                <input type="text" class="filter-input" placeholder="รายงานการสั่งซื้อ">
                <input type="text" class="filter-input" placeholder="เลือกซัพพลายเออร์">
                <input type="text" class="filter-input" placeholder="เลือกลูกค้า">
                <input type="text" class="filter-input" placeholder="เลือกพนักงาน">
            </div>

            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 15px; margin-bottom: 30px;">
                <button type="button" class="btn-primary" style="padding: 8px 30px;">ดูรายงาน</button>
                <a href="#" style="color: #333; text-decoration: none; font-size: 14px; font-weight: 600;">ส่งออกไฟล์ <i class="fas fa-sign-out-alt" style="transform: rotate(270deg);"></i></a>
            </div>
        </form>

        <h5 style="margin-bottom: 15px; font-size: 16px;">ตัวอย่างรายงานที่สร้างขึ้น</h5>
        
        <table class="table table-striped">
            <thead style="background-color: #e3effd;">
                <tr>
                    <th style="background-color: #e3effd; color: #333;">วันที่</th>
                    <th style="background-color: #e3effd; color: #333;">ประเภทธุรกรรม</th>
                    <th style="background-color: #e3effd; color: #333;">รหัสคำสั่งซื้อ/ลูกค้า</th>
                    <th style="background-color: #e3effd; color: #333;">ราคาต่อหน่วย</th>
                    <th style="background-color: #e3effd; color: #333;">จำนวนเงินทั้งหมด</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="color: #888;">15 มกราคม 2567</td>
                    <td style="color: #888;">Sales</td>
                    <td style="color: #888;">AlphaCom</td>
                    <td style="color: #888;">5.00</td>
                    <td style="color: #888;">4,250.00</td>
                </tr>
                <tr>
                    <td style="color: #888;">2 มีนาคม 2567</td>
                    <td style="color: #888;">Sales</td>
                    <td style="color: #888;">4X-0001</td>
                    <td style="color: #888;">0.85</td>
                    <td style="color: #888;">7.85</td>
                </tr>
                <tr>
                    <td style="color: #888;">18 มิถุนายน 2567</td>
                    <td style="color: #888;">Purchase</td>
                    <td style="color: #888;">SUP-032</td>
                    <td style="color: #888;">1.20</td>
                    <td style="color: #888;">6,000.00</td>
                </tr>
                <tr style="background-color: #e3effd;">
                    <td style="color: #888;">25 กันยายน 2567</td>
                    <td style="color: #888;">Sales</td>
                    <td style="color: #888;">BetaCorp</td>
                    <td style="color: #888;">0.80</td>
                    <td style="color: #888;">4.20</td>
                </tr>
            </tbody>
        </table>

    </div>
</div>
