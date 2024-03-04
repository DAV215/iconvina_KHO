<?php 
    include_once('QLNS/getdataUser.php');
    include('../config/configDb.php');
    if(isset($_POST['saveQuytrinh'])){
        $sql = "TRUNCATE TABLE tbl_phanquyenduyet";
        $query = mysqli_query($mysqli, $sql);
        $admin1 = $_POST['admin1'];
        $admin2 = $_POST['admin2'];
        $thuquy = $_POST['thuquy'];
        $ketoan = $_POST['ketoan'];
        save($admin1,'admin1', 'admin');
        save($admin2,'admin2', 'admin');
        save($thuquy,'thuquy', 'user');
        save($ketoan,'ketoan', 'user');
    }

    $sql = "SELECT * FROM `tbl_phanquyenduyet`";
    $query = mysqli_query($mysqli, $sql);
    $data = [];
    while ($row = mysqli_fetch_array($query)){
        $data[]= $row;
    }
    // print_r($data);
    foreach($data as $row){
        if($row['permission']=='admin1'){
            $UserNameA1 = $row['username'];
        }elseif($row['permission']=='admin2'){
            $UserNameA2 = $row['username'];
        }
        elseif($row['permission']=='thuquy'){
            $UserNameTQ = $row['username'];
        }elseif($row['permission']=='ketoan'){
            $UserNameKT= $row['username'];
        }
    }
    function save($username,$permission,$type){
        include('../config/configDb.php');
        $sql = "INSERT INTO `tbl_phanquyenduyet`( `username`, `permission`, `type`)
        VALUES(
            '$username',
            '$permission',
            '$type'
        )";
        $query= mysqli_query($mysqli, $sql);

    }
?>

<form action="" method="post" enctype="multipart/form-data">
    <div class="userForm" style="margin-top:20px;">
        <div class="mainForm">
            <div class="inforForm">
                <h2 class="nameForm">Phân quyền các bước duyệt</h2>
                <div class="bodyofForm">
                    <div class="inputHaveLable">
                        <label for="nameproject">Admin 1</label>
                        <select name="admin1" id="">
                            <?php 
                            foreach (getAllPersonnel() as $row) {
                                $selected = ($row['username'] == $UserNameA1) ? 'selected' : '';
                                ?>
                            <option value="<?php echo $row['username'] ?>"  <?php echo $selected ?>><?php echo $row['fullname'] ?></option>
                            <?php
                            }
                        ?>
                        </select>
                    </div>
                    <div class="inputHaveLable">
                        <label for="nameproject">Admin 2</label>
                        <select name="admin2" id="">
                            <?php 
                            foreach (getAllPersonnel() as $row) {
                                $selected = ($row['username'] == $UserNameA2) ? 'selected' : '';
                                ?>
                            <option value="<?php echo $row['username'] ?>"  <?php echo $selected ?>><?php echo $row['fullname'] ?></option>
                            <?php
                            }
                        ?>
                        </select>
                    </div>
                    <div class="inputHaveLable">
                        <label for="nameproject">Thủ quỹ</label>
                        <select name="thuquy" id="">
                            <?php 
                            foreach (getAllPersonnel() as $row) {
                                $selected = ($row['username'] == $UserNameTQ) ? 'selected' : '';
                                ?>
                            <option value="<?php echo $row['username'] ?>"  <?php echo $selected ?>><?php echo $row['fullname'] ?></option>
                            <?php
                            }
                        ?>
                        </select>
                    </div>
                    <div class="inputHaveLable">
                        <label for="nameproject">Kế toán</label>
                        <select name="ketoan" id="">
                            <?php 
                            foreach (getAllPersonnel() as $row) {
                                $selected = ($row['username'] == $UserNameKT) ? 'selected' : '';
                                ?>
                            <option value="<?php echo $row['username'] ?>"  <?php echo $selected ?>><?php echo $row['fullname'] ?></option>
                            <?php
                            }
                        ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="inforForm">
                <h2 class="nameForm">Thông tin bổ sung</h2>
                <div class="bodyofForm">
                    <textarea name="note" id="" cols="30" rows="10" placeholder="Ghi chú" autocomplete="list"
                        aria-haspopup="true"></textarea>
                    <button type="submit" name="saveQuytrinh"
                        style="background-image: linear-gradient(147deg, #fe8a39 0%, #fd3838 74%);">Lưu cài đặt</button>
                </div>
            </div>


        </div>
    </div>
</form>