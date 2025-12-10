<?php
include 'connect.php';

// Fetch Users with Role
$sql_users = "SELECT u.*, p.permission_name as role 
              FROM user u 
              LEFT JOIN permission p ON u.user_id = p.user_id 
              ORDER BY u.user_id DESC";
$result_users = $conn->query($sql_users);

// Fetch Customers
$sql_customers = "SELECT * FROM customer ORDER BY customer_id DESC";
$result_customers = $conn->query($sql_customers);

// Fetch Agents
$sql_agents = "SELECT * FROM agent ORDER BY agent_id DESC";
$result_agents = $conn->query($sql_agents);



// Determine active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'user';
?>
<div class="content-body">
    <div class="card" style="max-width: 900px; padding: 0 0 0 0; overflow: hidden; background-color: #f7f9fc; border: none; box-shadow: none;">
        
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <ul class="nav-tabs" style="margin-bottom: 20px; background-color: #f7f9fc;">
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'user' ? 'active' : ''; ?>" href="#" onclick="switchTab('user')">ผู้ใช้</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'customer' ? 'active' : ''; ?>" href="#" onclick="switchTab('customer')">ลูกค้า</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'supplier' ? 'active' : ''; ?>" href="#" onclick="switchTab('supplier')">ซัพพลายเออร์</a>
            </li>
        </ul>

        <!-- User Section -->
        <div id="user-section" style="display: <?php echo $active_tab == 'user' ? 'block' : 'none'; ?>;">
            <!-- Toolbar -->
            <div class="toolbar">
                <button class="btn-toolbar" onclick="openAddUserModal()" style="cursor: pointer;">เพิ่มผู้ใช้</button>
                <div class="search-container">
                    <input type="text" id="userSearchInput" class="search-input" placeholder="ค้นหาผู้ใช้" onkeyup="filterTable('userSearchInput', 'userTableBody')">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>

            <!-- User Table -->
            <div style="background-color: white; border-radius: 8px; border: 1px solid #ddd; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <table class="table" style="margin-top: 0;">
                    <thead>
                        <tr>
                            <th>ชื่อ</th>
                            <th>ชื่อผู้ใช้</th>
                            <th>อีเมล</th>
                            <th>บทบาท</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <?php if ($result_users->num_rows > 0): ?>
                            <?php while($row = $result_users->fetch_assoc()): ?>
                                <?php 
                                    $roleClass = 'badge-role-user';
                                    if ($row['role'] == 'Admin') $roleClass = 'badge-role-admin';
                                    elseif ($row['role'] == 'Manager') $roleClass = 'badge-role-manager';
                                    
                                    $userData = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                                ?>
                                <tr>
                                    <td><?php echo $row['firstname'] . ' ' . $row['lastname']; ?></td>
                                    <td><?php echo $row['username']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><span class="badge-role <?php echo $roleClass; ?>"><?php echo $row['role'] ?? 'User'; ?></span></td>
                                    <td>
                                        <button class="btn-secondary" style="padding: 4px 10px; font-size: 12px; margin-right: 5px;" onclick="openEditUserModal(<?php echo $userData; ?>)">แก้ไข</button>
                                        <button class="btn-warning" style="padding: 4px 10px; font-size: 12px; margin-right: 5px; color: #fff; background-color: #ffc107; border: none; cursor: pointer;" onclick="openPermissionModal(<?php echo $userData; ?>)">สิทธิ์</button>
                                        <button class="btn-danger" style="padding: 4px 10px; font-size: 12px;" onclick="confirmDeleteUser(<?php echo $row['user_id']; ?>, '<?php echo $row['username']; ?>')">ลบ</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">ไม่พบผู้ใช้งาน</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Customer Section -->
        <div id="customer-section" style="display: <?php echo $active_tab == 'customer' ? 'block' : 'none'; ?>;">
            <!-- Toolbar -->
            <div class="toolbar">
                <button class="btn-toolbar" onclick="openAddCustomerModal()" style="cursor: pointer;">เพิ่มลูกค้า</button>
                <div class="search-container">
                    <input type="text" id="customerSearchInput" class="search-input" placeholder="ค้นหาลูกค้า" onkeyup="filterTable('customerSearchInput', 'customerTableBody')">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>

            <!-- Customer Table -->
            <div style="background-color: white; border-radius: 8px; border: 1px solid #ddd; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <table class="table" style="margin-top: 0;">
                    <thead>
                        <tr>
                            <th>ชื่อลูกค้า</th>
                            <th>เบอร์โทรศัพท์</th>
                            <th>อีเมล</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="customerTableBody">
                        <?php if ($result_customers && $result_customers->num_rows > 0): ?>
                            <?php while($row = $result_customers->fetch_assoc()): ?>
                                <?php 
                                    $customerData = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                                ?>
                                <tr>
                                    <td><?php echo $row['customer_name']; ?></td>
                                    <td><?php echo $row['customer_phone']; ?></td>
                                    <td><?php echo $row['customer_email']; ?></td>
                                    <td>
                                        <button class="btn-secondary" style="padding: 4px 10px; font-size: 12px; margin-right: 5px;" onclick="openEditCustomerModal(<?php echo $customerData; ?>)">แก้ไข</button>
                                        <button class="btn-danger" onclick="confirmDeleteCustomer(<?php echo $row['customer_id']; ?>, '<?php echo $row['customer_name']; ?>')">ลบ</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">ไม่พบข้อมูลลูกค้า</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Supplier Section -->
        <div id="supplier-section" style="display: <?php echo $active_tab == 'supplier' ? 'block' : 'none'; ?>;">
            <!-- Toolbar -->
            <div class="toolbar">
                <button class="btn-toolbar" onclick="openAddAgentModal()">เพิ่มซัพพลายเออร์</button>
                <div class="search-container">
                    <input type="text" id="agentSearchInput" class="search-input" placeholder="ค้นหาซัพพลายเออร์" onkeyup="filterTable('agentSearchInput', 'agentTableBody')">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>

            <!-- Agents Table -->
            <div style="background-color: white; border-radius: 8px; border: 1px solid #ddd; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <table class="table" style="margin-top: 0;">
                    <thead>
                        <tr>
                            <th>ชื่อ</th>
                            <th>เบอร์โทร</th>
                            <th>อีเมล</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="agentTableBody">
                        <?php if ($result_agents && $result_agents->num_rows > 0): ?>
                            <?php while($row = $result_agents->fetch_assoc()): ?>
                                <?php $agentData = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>
                                <tr>
                                    <td><?php echo $row['agent_name']; ?></td>
                                    <td><?php echo $row['agent_phone']; ?></td>
                                    <td><?php echo $row['agent_email']; ?></td>
                                    <td>
                                        <button class="btn-secondary" style="padding: 4px 10px; font-size: 12px; margin-right: 5px;" onclick="openEditAgentModal(<?php echo $agentData; ?>)">แก้ไข</button>
                                        <button class="btn-danger" onclick="confirmDeleteAgent(<?php echo $row['agent_id']; ?>, '<?php echo $row['agent_name']; ?>')">ลบ</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">ไม่พบข้อมูลซัพพลายเออร์</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Modal Overlay: Add User -->
<div class="modal-overlay" id="addUserModal" style="display: none;">
    <div class="modal-box" style="width: 600px; text-align: left;">
        <div class="modal-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
            เพิ่มผู้ใช้งานใหม่
        </div>
        <div class="modal-body" style="padding: 0 30px 20px;">
            <form id="addUserForm" action="action/user_create_db.php" method="post">
                <div class="form-row">
                    <div class="col-md-6" style="margin-bottom: 15px;">
                        <label class="form-label">ชื่อผู้ใช้ (Username)</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="col-md-6" style="margin-bottom: 15px;">
                        <label class="form-label">รหัสผ่าน (Password)</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6" style="margin-bottom: 15px;">
                        <label class="form-label">ชื่อจริง (Firstname)</label>
                        <input type="text" name="firstname" class="form-control" required>
                    </div>
                    <div class="col-md-6" style="margin-bottom: 15px;">
                        <label class="form-label">นามสกุล (Lastname)</label>
                        <input type="text" name="lastname" class="form-control" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">ที่อยู่ (Address)</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-row">
                    <div class="col-md-6" style="margin-bottom: 15px;">
                        <label class="form-label">อีเมล (Email)</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6" style="margin-bottom: 15px;">
                        <label class="form-label">เบอร์โทรศัพท์ (Phone)</label>
                        <input type="text" name="phone" class="form-control" pattern="^0[0-9]{8,9}$" title="กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง (ขึ้นต้นด้วย 0, 9-10 หลัก)" required>
                    </div>
                </div>

                 <div class="modal-footer" style="padding: 10px 0 0; justify-content: flex-end;">
                    <button type="button" class="btn-secondary" onclick="closeAddUserModal()" style="margin-right: 10px;">ยกเลิก</button>
                    <button type="submit" class="btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Overlay: Edit User -->
<div class="modal-overlay" id="editUserModal" style="display: none;">
    <div class="modal-box" style="width: 600px; text-align: left;">
        <div class="modal-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
            แก้ไขข้อมูลผู้ใช้งาน
        </div>
        <div class="modal-body" style="padding: 0 30px 20px;">
            <form id="editUserForm" action="action/user_update_db.php" method="post">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="form-row">
                    <div class="col-md-6" style="margin-bottom: 15px;">
                        <label class="form-label">ชื่อผู้ใช้ (Username)</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                    </div>
                    <div class="col-md-6" style="margin-bottom: 15px;">
                        <label class="form-label">รหัสผ่านใหม่ (ว่างไว้ถ้าไม่เปลี่ยน)</label>
                        <input type="password" name="password" id="edit_password" class="form-control" placeholder="เปลี่ยนรหัสผ่าน">
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6" style="margin-bottom: 15px;">
                        <label class="form-label">ชื่อจริง (Firstname)</label>
                        <input type="text" name="firstname" id="edit_firstname" class="form-control" required>
                    </div>
                    <div class="col-md-6" style="margin-bottom: 15px;">
                        <label class="form-label">นามสกุล (Lastname)</label>
                        <input type="text" name="lastname" id="edit_lastname" class="form-control" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">ที่อยู่ (Address)</label>
                    <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-row">
                    <div class="col-md-6" style="margin-bottom: 15px;">
                        <label class="form-label">อีเมล (Email)</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="col-md-6" style="margin-bottom: 15px;">
                        <label class="form-label">เบอร์โทรศัพท์ (Phone)</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control" pattern="^0[0-9]{8,9}$" title="กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง (ขึ้นต้นด้วย 0, 9-10 หลัก)" required>
                    </div>
                </div>

                 <div class="modal-footer" style="padding: 10px 0 0; justify-content: flex-end;">
                    <button type="button" class="btn-secondary" onclick="closeEditUserModal()" style="margin-right: 10px;">ยกเลิก</button>
                    <button type="submit" class="btn-primary">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Overlay: Permission -->
<div class="modal-overlay" id="permissionModal" style="display: none;">
    <div class="modal-box" style="width: 400px; text-align: left;">
        <div class="modal-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
             จัดการสิทธิ์ผู้ใช้งาน
        </div>
        <div class="modal-body" style="padding: 0 30px 20px;">
            <form action="action/user_permission_update_db.php" method="post">
                <input type="hidden" name="user_id" id="perm_user_id">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">เลือกสิทธิ์ (Role)</label>
                    <select name="permission_name" id="perm_role" class="form-control">
                        <option value="Admin">ผู้ดูแลระบบ (Admin)</option>
                        <option value="Staff">พนักงาน (Staff)</option>
                        <option value="Manager">ผู้บริหาร (Manager)</option>
                    </select>
                </div>
                 <div class="modal-footer" style="padding: 10px 0 0; justify-content: flex-end;">
                    <button type="button" class="btn-secondary" onclick="closePermissionModal()" style="margin-right: 10px;">ยกเลิก</button>
                    <button type="submit" class="btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Overlay: Add Customer -->
<div class="modal-overlay" id="addCustomerModal" style="display: none;">
    <div class="modal-box" style="width: 500px; text-align: left;">
        <div class="modal-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
            เพิ่มลูกค้าใหม่
        </div>
        <div class="modal-body" style="padding: 0 30px 20px;">
            <form action="action/customer_create_db.php" method="post">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">ชื่อลูกค้า (Name)</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">เบอร์โทรศัพท์ (Phone)</label>
                    <input type="text" name="customer_phone" class="form-control" pattern="^0[0-9]{8,9}$" title="กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง (ขึ้นต้นด้วย 0, 9-10 หลัก)" required>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">อีเมล (Email)</label>
                    <input type="email" name="customer_email" class="form-control" required>
                </div>
                 <div class="modal-footer" style="padding: 10px 0 0; justify-content: flex-end;">
                    <button type="button" class="btn-secondary" onclick="closeAddCustomerModal()" style="margin-right: 10px;">ยกเลิก</button>
                    <button type="submit" class="btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Overlay: Edit Customer -->
<div class="modal-overlay" id="editCustomerModal" style="display: none;">
    <div class="modal-box" style="width: 500px; text-align: left;">
        <div class="modal-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
            แก้ไขข้อมูลลูกค้า
        </div>
        <div class="modal-body" style="padding: 0 30px 20px;">
            <form action="action/customer_update_db.php" method="post">
                <input type="hidden" name="customer_id" id="edit_customer_id">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">ชื่อลูกค้า (Name)</label>
                    <input type="text" name="customer_name" id="edit_customer_name" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">เบอร์โทรศัพท์ (Phone)</label>
                    <input type="text" name="customer_phone" id="edit_customer_phone" class="form-control" pattern="^0[0-9]{8,9}$" title="กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง (ขึ้นต้นด้วย 0, 9-10 หลัก)" required>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">อีเมล (Email)</label>
                    <input type="email" name="customer_email" id="edit_customer_email" class="form-control" required>
                </div>
                 <div class="modal-footer" style="padding: 10px 0 0; justify-content: flex-end;">
                    <button type="button" class="btn-secondary" onclick="closeEditCustomerModal()" style="margin-right: 10px;">ยกเลิก</button>
                    <button type="submit" class="btn-primary">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Overlay: Add Agent -->
<div class="modal-overlay" id="addAgentModal" style="display: none;">
    <div class="modal-box" style="width: 500px; text-align: left;">
        <div class="modal-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
            เพิ่มซัพพลายเออร์ใหม่
        </div>
        <div class="modal-body" style="padding: 0 30px 20px;">
            <form action="action/agent_create_db.php" method="post">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">ชื่อ (Name)</label>
                    <input type="text" name="agent_name" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">เบอร์โทรศัพท์ (Phone)</label>
                    <input type="text" name="agent_phone" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">อีเมล (Email)</label>
                    <input type="email" name="agent_email" class="form-control" required>
                </div>
                 <div class="modal-footer" style="padding: 10px 0 0; justify-content: flex-end;">
                    <button type="button" class="btn-secondary" onclick="closeAddAgentModal()" style="margin-right: 10px;">ยกเลิก</button>
                    <button type="submit" class="btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Overlay: Edit Agent -->
<div class="modal-overlay" id="editAgentModal" style="display: none;">
    <div class="modal-box" style="width: 500px; text-align: left;">
        <div class="modal-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
            แก้ไขข้อมูลซัพพลายเออร์
        </div>
        <div class="modal-body" style="padding: 0 30px 20px;">
            <form action="action/agent_update_db.php" method="post">
                <input type="hidden" name="agent_id" id="edit_agent_id">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">ชื่อ (Name)</label>
                    <input type="text" name="agent_name" id="edit_agent_name" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">เบอร์โทรศัพท์ (Phone)</label>
                    <input type="text" name="agent_phone" id="edit_agent_phone" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">อีเมล (Email)</label>
                    <input type="email" name="agent_email" id="edit_agent_email" class="form-control" required>
                </div>
                 <div class="modal-footer" style="padding: 10px 0 0; justify-content: flex-end;">
                    <button type="button" class="btn-secondary" onclick="closeEditAgentModal()" style="margin-right: 10px;">ยกเลิก</button>
                    <button type="submit" class="btn-primary">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
    // Tab Switching
    function switchTab(tabName) {
        // Hide all sections
        document.getElementById('user-section').style.display = 'none';
        document.getElementById('customer-section').style.display = 'none';
        document.getElementById('supplier-section').style.display = 'none';
        
        // Remove active class from all tabs
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => link.classList.remove('active'));
        
        // Show selected section and add active class
        if (tabName === 'user') {
            document.getElementById('user-section').style.display = 'block';
            navLinks[0].classList.add('active');
        } else if (tabName === 'customer') {
            document.getElementById('customer-section').style.display = 'block';
            navLinks[1].classList.add('active');
        } else if (tabName === 'supplier') {
            document.getElementById('supplier-section').style.display = 'block';
            navLinks[2].classList.add('active');
        }
    }

    // --- User Functions ---
    function confirmDeleteUser(userId, userName) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: "คุณต้องการลบผู้ใช้ " + userName + " หรือไม่?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ลบข้อมูล'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'action/user_delete_db.php?id=' + userId;
            }
        })
    }
    function openAddUserModal() { document.getElementById('addUserModal').style.display = 'flex'; }
    function closeAddUserModal() { document.getElementById('addUserModal').style.display = 'none'; }
    function openEditUserModal(user) {
        document.getElementById('edit_user_id').value = user.user_id;
        document.getElementById('edit_username').value = user.username;
        document.getElementById('edit_firstname').value = user.firstname;
        document.getElementById('edit_lastname').value = user.lastname;
        document.getElementById('edit_address').value = user.address;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_phone').value = user.phone;
        document.getElementById('edit_password').value = '';
        document.getElementById('editUserModal').style.display = 'flex';
    }
    function closeEditUserModal() { document.getElementById('editUserModal').style.display = 'none'; }
    
    function openPermissionModal(user) {
        document.getElementById('perm_user_id').value = user.user_id;
        document.getElementById('perm_role').value = user.role || 'Staff'; 
        document.getElementById('permissionModal').style.display = 'flex';
    }
    function closePermissionModal() { document.getElementById('permissionModal').style.display = 'none'; }

    // --- Customer Functions ---
    function confirmDeleteCustomer(custId, custName) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: "คุณต้องการลบลูกค้า " + custName + " หรือไม่?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ลบข้อมูล'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'action/customer_delete_db.php?id=' + custId;
            }
        })
    }
    function openAddCustomerModal() { document.getElementById('addCustomerModal').style.display = 'flex'; }
    function closeAddCustomerModal() { document.getElementById('addCustomerModal').style.display = 'none'; }
    function openEditCustomerModal(cust) {
        document.getElementById('edit_customer_id').value = cust.customer_id;
        document.getElementById('edit_customer_name').value = cust.customer_name;
        document.getElementById('edit_customer_phone').value = cust.customer_phone;
        document.getElementById('edit_customer_email').value = cust.customer_email;
        document.getElementById('editCustomerModal').style.display = 'flex';
    }
    function closeEditCustomerModal() { document.getElementById('editCustomerModal').style.display = 'none'; }

    // --- Agent Functions ---
    function confirmDeleteAgent(id, name) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: "คุณต้องการลบซัพพลายเออร์ " + name + " หรือไม่?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ลบข้อมูล'
        }).then((result) => {
            if (result.isConfirmed) window.location.href = 'action/agent_delete_db.php?id=' + id;
        })
    }
    function openAddAgentModal() { document.getElementById('addAgentModal').style.display = 'flex'; }
    function closeAddAgentModal() { document.getElementById('addAgentModal').style.display = 'none'; }
    function openEditAgentModal(data) {
        document.getElementById('edit_agent_id').value = data.agent_id;
        document.getElementById('edit_agent_name').value = data.agent_name;
        document.getElementById('edit_agent_phone').value = data.agent_phone;
        document.getElementById('edit_agent_email').value = data.agent_email;
        document.getElementById('editAgentModal').style.display = 'flex';
    }
    function closeEditAgentModal() { document.getElementById('editAgentModal').style.display = 'none'; }



    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target == document.getElementById('addUserModal')) closeAddUserModal();
        if (event.target == document.getElementById('editUserModal')) closeEditUserModal();
        if (event.target == document.getElementById('addCustomerModal')) closeAddCustomerModal();
        if (event.target == document.getElementById('editCustomerModal')) closeEditCustomerModal();
        if (event.target == document.getElementById('addAgentModal')) closeAddAgentModal();
        if (event.target == document.getElementById('editAgentModal')) closeEditAgentModal();
        if (event.target == document.getElementById('permissionModal')) closePermissionModal();
    }

    // Search Function
    function filterTable(inputId, tableId) {
        var input, filter, tbody, tr, td, i, j, txtValue;
        input = document.getElementById(inputId);
        filter = input.value.toUpperCase();
        tbody = document.getElementById(tableId);
        tr = tbody.getElementsByTagName("tr");

        for (i = 0; i < tr.length; i++) {
            var display = "none";
            // Loop through all columns
            td = tr[i].getElementsByTagName("td");
            for (j = 0; j < td.length; j++) {
                if (td[j]) {
                    txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        display = "";
                        break; // Found match in this row
                    }
                }
            }
            tr[i].style.display = display;
        }
    }

    // Auto-hide alerts after 1400ms
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = "0";
            setTimeout(function() {
                alert.style.display = "none";
            }, 500); // Wait for transition to finish
        });
    }, 1400);
</script>
