<?php 
ob_start(); 
        include_once('QLNS/getdataUser.php');
        include('../config/configDb.php');
        if($_SESSION['boolUser']){
            $allPerofUser = [];
            foreach(getPermission(getIDbyUNAME($_SESSION['username_Login'])['id']) as $row){
                $allPerofUser[] = $row['id_role'];
            }
        }
        $perQLPB = [6,7];
        $perQLPer = [4,5,8,9];
        $perQLNhansu = [25, 26, 27];
        $perOfNS= $perQLNhansu+$perQLPB+$perQLPer;

        $perQLPC = [17,18,19,20];
        $perQLPT = [21,22,23,24];
        $perQLDXM = [12,13,14,16];
        $perQLQTXD = [28];

        $perofQLTC = $perQLPC + $perQLPT + $perQLDXM +$perQLQTXD;
        function checkPer($id_role, $allPerofUser){
            $missingPERNS = array_diff($id_role, $allPerofUser);
            if(count($missingPERNS) < count($id_role)){
                return 1;
            }else return 0;
        }
?>

<link rel="stylesheet" href="../asset/css/admin/sidebar.css">
<link rel="stylesheet" href="../asset/css/moblie/sidebar_MB.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<?php 
    if($_SESSION['admin']){
        ?>
<div class="sidebar pc">
    <div class="sideMenu-Top">
        <ul>
            <?php 
                if($_SESSION['boolUser']){
                    echo getUserdetail($_SESSION['userINFO'])['department'].' '.getUserdetail($_SESSION['userINFO'])['chucvu'];
                }else{
                    ?>
                    <li onclick="changeActive(this)">Admin IconVina</li>
                    <?php
                }
            ?>
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
                    <li><a href="admin.php?job=QLTC&action=dexuatmua" onclick="changeActive(this)">Đề xuất mua</a></li>
                    <li><a href="admin.php?job=QLTC&action=phieuchi" onclick="changeActive(this)">Phiếu chi</a></li>
                    <li><a href="admin.php?job=QLTC&action=phieuthu" onclick="changeActive(this)">Phiếu thu</a></li>
                    <li><a href="admin.php?job=QLTC&action=tonghop" onclick="changeActive(this)">Tổng hợp</a></li>
                    <li><a href="admin.php?job=QLTC&action=quytrinhxetduyet" onclick="changeActive(this)">Quản lý quy trình xét duyệt</a></li>
                </ul>
            </div>
        </div>
        <div href="" class="sideMenu-Item">
            <div class="Main-sideMenu">
                <div class="sideMenu-icon-title">
                    <span class="sideMenu-icon"> <i class="fa-solid fa-diagram-project"></i></i></span>
                    <span class="sideMenu-Title">Quản lý dự án</span>
                </div>
                <button onclick="toggleSubMenu(this)"><i class="fa-solid fa-angle-right"></i></button>
            </div>
            <div class="sub-sideMenu ">
                <ul>
                    <li><a href="admin.php?job=QLDA&action=thongke" onclick="changeActive(this)">Thống kê</a></li>
                    <li><a href="admin.php?job=QLTC&action=phieuchi" onclick="changeActive(this)">Thêm</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
        <?php
    }
    else{
        ?>
        <div class="sidebar pc">
            <div class="sideMenu-Top">
                <ul>
                    <?php 
                        if($_SESSION['boolUser']){
                            ?>
                                <li onclick="changeActive(this)">Employee IconVina</li>
                            <?php
                        }else{
                            ?>
                            <li onclick="changeActive(this)">Admin IconVina</li>
                            <?php
                        }
                    ?>
                    <li>
                        <button><i class="fa-solid fa-bars"></i></button>
                    </li>
                </ul>

            </div>
            <div class="sideMenu">
            <?php 
                $missingPERNS = array_diff($perOfNS, $allPerofUser);
                if(count($missingPERNS) < count($perOfNS)){
                    ?>
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
                                    
                                    <?php 
                                        if(checkPer($perQLNhansu, $allPerofUser)){
                                            ?>
                                                <li><a href="admin.php?job=QLNS&action=personnel" onclick="changeActive(this)">Nhân sự</a></li>
                                            <?php
                                        }
                                    ?>
                                    <?php 
                                        if(checkPer($perQLPer, $allPerofUser)){
                                            ?>
                                                <li><a href="admin.php?job=QLNS&action=permission" onclick="changeActive(this)">Quản lý các quyền</a></li>
                                            <?php
                                        }
                                    ?>
                                    <?php 
                                        if(checkPer($perQLPB, $allPerofUser)){
                                            ?>
                                                <li><a href="admin.php?job=QLNS&action=department" onclick="changeActive(this)">Quản lý phòng ban</a></li>
                                            <?php
                                        }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    <?php
                }
            ?>
        <?php 
            if(checkPer($perofQLTC, $allPerofUser)){
                
            }
        ?>
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
                    <li><a href="admin.php?job=QLTC&action=dexuatmua" onclick="changeActive(this)">Đề xuất mua</a></li>
                    <?php 
                        if(checkPer($perQLPC, $allPerofUser)){
                            ?>
                                <li><a href="admin.php?job=QLTC&action=phieuchi" onclick="changeActive(this)">Phiếu chi</a></li>
                            <?php
                        }
                    ?>
                    <?php 
                        if(checkPer($perQLPT, $allPerofUser)){
                            ?>
                                <li><a href="admin.php?job=QLTC&action=phieuthu" onclick="changeActive(this)">Phiếu thu</a></li>
                            <?php
                        }
                    ?>
                    <li><a href="admin.php?job=QLTC&action=tonghop" onclick="changeActive(this)">Tổng hợp</a></li>
                    <?php 
                        if(checkPer($perQLPC, $allPerofUser)){
                            ?>
                                <li><a href="admin.php?job=QLTC&action=quytrinhxetduyet" onclick="changeActive(this)">Quản lý quy trình xét duyệt</a></li>
                            <?php
                        }
                    ?>
                </ul>
            </div>
        </div>
        <div href="" class="sideMenu-Item">
            <div class="Main-sideMenu">
                <div class="sideMenu-icon-title">
                    <span class="sideMenu-icon"> <i class="fa-solid fa-diagram-project"></i></i></span>
                    <span class="sideMenu-Title">Quản lý dự án</span>
                </div>
                <button onclick="toggleSubMenu(this)"><i class="fa-solid fa-angle-right"></i></button>
            </div>
            <div class="sub-sideMenu ">
                <ul>
                    <li><a href="admin.php?job=QLDA&action=thongke" onclick="changeActive(this)">Thống kê</a></li>
                    <li><a href="admin.php?job=QLTC&action=phieuchi" onclick="changeActive(this)">Thêm</a></li>
                </ul>
            </div>
        </div>
        <div href="" class="sideMenu-Item">
            <div class="Main-sideMenu">
                <div class="sideMenu-icon-title">
                    <span class="sideMenu-icon"> <i class="fa-solid fa-list"></i></i></i></span>
                    <span class="sideMenu-Title">DashBoard</span>
                </div>
                <button onclick="toggleSubMenu(this)"><i class="fa-solid fa-angle-right"></i></button>
            </div>
            <div class="sub-sideMenu ">
                <ul>
                    <li><a href="admin.php?job=QLDA&action=totalIncome" onclick="changeActive(this)">Thống kê doanh thu</a></li>
                    <li><a href="admin.php?job=DashBoard&action=listJob" onclick="changeActive(this)">Công việc cần làm</a></li>
                </ul>
            </div>
        </div>
        <div href="" class="sideMenu-Item">
            <div class="Main-sideMenu">
                <div class="sideMenu-icon-title">
                    <span class="sideMenu-icon"> <i class="fa-solid fa-list"></i></i></i></span>
                    <span class="sideMenu-Title">Kho vận</span>
                </div>
                <button onclick="toggleSubMenu(this)"><i class="fa-solid fa-angle-right"></i></button>
            </div>
            <div class="sub-sideMenu ">
                <ul>
                    <li><a href="admin.php?job=QLKHO&action=thongke" onclick="changeActive(this)">Thống kê </a></li>
                    <li><a href="admin.php?job=QLKHO&action=thongke&actionChild=addFILE_ADD" onclick="changeActive(this)">Thêm sản phẩm</a></li>
                    <li><a href="admin.php?job=QLKHO&action=thongke&actionChild=import" onclick="changeActive(this)">Nhập kho</a></li>
                    <li><a href="admin.php?job=QLKHO&action=thongke&actionChild=export" onclick="changeActive(this)">Xuất kho</a></li>
                    <li><a href="admin.php?job=QLKHO&action=thongke&actionChild=setting" onclick="changeActive(this)">Thông tin kho</a></li>
                </ul>
            </div>
        </div>
    </div>
        </div>
        

        <?php
    }
?>
<div class="sidebar-mb">
        <div href="" class="sideMenu-Item-mb">
            <form action="" method="post">
                <div class="Main-sideMenu-mb">
                    <div class="sideMenu-icon-title-mb">
                        <button type="submit"><i class="fa-solid fa-plus"></i></button>
                        
                        <input type="hidden" name="job" value="QLTC">
                        <input type="hidden" name="action" value="dexuatmua">   
                        <input type="hidden" name="addBuysuggest" value = "true">
                        <span class="sideMenu-Title-mb">Tạo đề xuất</span>
                    </div>
                </div>
            </form>
 
        </div>
        <div href="" class="sideMenu-Item-mb">
            <div class="Main-sideMenu-mb">
                <div class="sideMenu-icon-title-mb">
                    <a href=""><i class="fa-solid fa-bell"></i></a>
                    <span class="sideMenu-Title-mb">Thông báo</span>
                </div>
            </div>
        </div>
        <div href="" class="sideMenu-Item-mb">
            <div class="Main-sideMenu-mb">
                <div class="sideMenu-icon-title-mb">
                    <a href=""><i class="fa-solid fa-right-from-bracket"></i></a>
                    <span class="sideMenu-Title-mb">Đăng xuất</span>
                </div>
            </div>
        </div>
        <div href="" class="sideMenu-Item-mb">
            <div class="Main-sideMenu-mb">
                <div class="sideMenu-icon-title-mb">
                    <a href=""><i class="fa-solid fa-bars"></i></a>
                    <span class="sideMenu-Title-mb">Menu</span>
                </div>
            </div>
        </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="../asset/js/sidebar.js"></script>

<?php 