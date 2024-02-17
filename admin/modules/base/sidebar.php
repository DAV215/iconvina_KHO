<link rel="stylesheet" href="../asset/css/admin/sidebar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<div class="sidebar">
    <div class="sideMenu-Top">
        <ul>
            <li onclick="changeActive(this)">Admin IconVina</li>
            <li>
                <button><i class="fa-solid fa-bars"></i></button>
            </li>
        </ul>

    </div>
    <div class="sideMenu">
        <div href="" class="sideMenu-Item">
            <div class="Main-sideMenu">
                <div class="sideMenu-icon-title">
                    <span class="sideMenu-icon"> <i class="fa-regular fa-user"></i></span>
                    <span class="sideMenu-Title">Quản lý nhân sự</span>
                </div>
                <button onclick="toggleSubMenu(this)"><i class="fa-solid fa-angle-right"></i></button>
            </div>
            <div class="sub-sideMenu ">
                <ul>
                    <li><a href="admin.php?job=QLNS&action=personnel" onclick="changeActive(this)">Nhân sự</a></li>
                    <li><a href="admin.php?job=QLNS&action=permission" onclick="changeActive(this)">Quản lý các quyền</a></li>
                    <li><a href="admin.php?job=QLNS&action=department" onclick="changeActive(this)">Quản lý phòng ban</a></li>
                </ul>
            </div>
        </div>
        <div href="" class="sideMenu-Item">
            <div class="Main-sideMenu">
                <div class="sideMenu-icon-title">
                    <span class="sideMenu-icon"> <i class="fa-solid fa-file-invoice-dollar"></i></span>
                    <span class="sideMenu-Title">Quản lý phiếu chi</span>
                </div>
                <button onclick="toggleSubMenu(this)"><i class="fa-solid fa-angle-right"></i></button>
            </div>
            <div class="sub-sideMenu ">
                <ul>
                    <li><a href="" onclick="changeActive(this)">Thống kê</a></li>
                    <li><a href="" onclick="changeActive(this)">Thêm</a></li>
                    <li><a href="" onclick="changeActive(this)">Sửa</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="../asset/js/sidebar.js"></script>