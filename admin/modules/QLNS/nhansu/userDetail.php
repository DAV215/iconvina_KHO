<?php 
    if(isset($_POST['modifyUser']) && isset($_POST['userId'])){
        $userId = $_POST['userId'];
        $mail = $_POST['mail'];
        $username = $_POST['username'];
        $password = 'iconvina123';
        $department = $_POST['department'];
        $chucvu = $_POST['chucvu'];
        $fullname = $_POST['fullname'];
        $sdt = $_POST['sdt'];
        
        $otp = checkValue('otp');
        $birthDay = checkValue('birthDay');
        $startDay = checkValue('startDay');
        $queQuan = checkValue('queQuan');
        $tamTru = checkValue('tamTru');
        $sdtEmg = checkValue('sdtEmg');
        $nameEmg = checkValue('nameEmg');
        $oldAvtName = '../asset/media/private/avt/'.$_POST['nameAvtStock'];
        $newAvtName = $_FILES['avt']['tmp_name'];

        if (isset($_POST["Permission"])) {
            // Retrieve the values of all 'Permission' inputs
            $permissionValues = $_POST["Permission"];
    
            // Initialize an array to store the values
            $permissionArray = [];
            //clear all Permission of Uer
            $sql = "DELETE FROM `tbl_user_role` WHERE `id_user` =  '$userId'";
            $queryJob = mysqli_query($mysqli, $sql);
            // Loop through the values and push them into the array
            foreach ($permissionValues as $value) {
                $permissionArray[] = $value;
                $sql = "INSERT INTO `tbl_user_role`( `id_role`, `id_user`) VALUES ($value, $userId)";
                $queryJob = mysqli_query($mysqli, $sql);
            }    
        } 

        if (isset($_FILES['avt']) && $_FILES['avt']['error'] == UPLOAD_ERR_OK && $_FILES['avt']['name'] != $_POST['nameAvtStock']) {
            // Calculate MD5 hashes of the images
            $folderImageHash = md5_file($oldAvtName);
            $uploadedImageHash = md5_file($newAvtName);
            if ($folderImageHash != $uploadedImageHash) {
            $avt = $_FILES['avt']['name'];
            $avt_tmp = $_FILES['avt']['tmp_name'];
            $avt = time().'_'.$username;

            $sqlUpdateUser = "UPDATE `tbl_user` SET 
            `mail` = '$mail',
            `password` = '$password',
            `username` = '$username',
            `department` = '$department',
            `chucvu` = '$chucvu',
            `fullname` = '$fullname',
            `sdt` = '$sdt',
            `otp` = '$otp',
            `avt` = '$avt',
            `birthDay` = '$birthDay',
            `startDay` = '$startDay',
            `queQuan` = '$queQuan',
            `tamTru` = '$tamTru',
            `sdtEmg` = '$sdtEmg',
            `nameEmg` = '$nameEmg'
            WHERE `id` = $userId";

            $queryUpdateUser = mysqli_query($mysqli, $sqlUpdateUser);
            unlink('../asset/media/private/avt/'.$nameAvtStock);
            move_uploaded_file($avt_tmp,'../asset/media/private/avt/'.$avt);
            }
        }else{
            $sqlUpdateUser = "UPDATE `tbl_user` SET 
            `mail` = '$mail',
            `password` = '$password',
            `username` = '$username',
            `department` = '$department',
            `chucvu` = '$chucvu',
            `fullname` = '$fullname',
            `sdt` = '$sdt',
            `otp` = '$otp',
            `birthDay` = '$birthDay',
            `startDay` = '$startDay',
            `queQuan` = '$queQuan',
            `tamTru` = '$tamTru',
            `sdtEmg` = '$sdtEmg',
            `nameEmg` = '$nameEmg'
            WHERE `id` = $userId";
            $queryUpdateUser = mysqli_query($mysqli, $sqlUpdateUser);
        }
        echo "<script> window.open('admin.php?job=QLNS&action=personnel','_self');</script>";
        
    }
    if(isset($_POST['deleteUser'])){
        $userId = $_POST['userId'];
        $sqlDelUser = "DELETE FROM `tbl_user` WHERE `id` = $userId";
        $queryDelUse = mysqli_query($mysqli, $sqlDelUser);
        echo "<script> window.open('admin.php?job=QLNS&action=personnel','_self');</script>";
    }
    if(isset($_POST['hideUser'])){
        $userId = $_POST['userId'];
        $sqlHideUser = "UPDATE `tbl_user` SET `lock` = 1 WHERE `id` = '$userId'";
        $query = mysqli_query($mysqli, $sqlHideUser);
        echo "<script> window.open('admin.php?job=QLNS&action=personnel','_self');</script>";
    }
    function checkValue($x){
        if(isset($_POST[$x])){
            return $x = $_POST[$x];
        } else {
            $x = '';
        }
        return $x;
    }
    // include('../modules/QLNS/getdataUser.php');
    $userDetail = getUserdetail($_GET['id']);

?>

<h1>Sửa thông tin nhân sự</h1>
<form action="" method="post" enctype="multipart/form-data">
    <div class="userForm">
        <div class="avtGrid">
            <div class="avatar-upload">
                <div class="avatar-edit">
                    <input type='file' name="avt"  id="avt" accept=".png, .jpg, .jpeg" />
                    <label for="avt"></label>
                </div>
                <div class="avatar-preview">
                    <div id="imagePreview" style="background-image: url(../asset/media/private/avt/<?php echo $userDetail['avt'] ?>);">
                    </div>
                </div>
            </div>
            <?php 
                if ($_SESSION['admin'] || checkPerOfUser(26, $_SESSION['userINFO']['id'])){
                    ?>
                        <button type="submit" name="modifyUser" style="    background-image: linear-gradient(147deg, #fe8a39 0%, #fd3838 74%);">Sửa thông tin</button>
                    <?php
                }

                if ($_SESSION['admin'] || checkPerOfUser(27, $_SESSION['userINFO']['id'])){
                    ?>
                        <button type="submit" name="deleteUser" style="    background-image: linear-gradient(147deg, #fe8a39 0%, #fd3838 74%);" onclick="return confirm('Bạn có chắc xóa người dùng này?')">Xóa người dùng</button>
                        <button type="submit" name="hideUser" style="    background-image: linear-gradient(147deg, #fe8a39 0%, #fd3838 74%);" onclick="return confirm('Bạn có chắc tạm khóa người dùng này?')">Tạm ngưng</button>
                    <?php
                }
            ?>

        </div>
        <div class="mainForm">
            <div class="inforForm">
                <h2 class="nameForm">Thông tin cơ bản</h2>
                <div class="bodyofForm">
                    <input type="hidden" name="userId" value="<?php echo $userDetail['id']; ?>">
                    <input type="hidden" name="nameAvtStock" value="<?php echo $userDetail['avt']; ?>">
                    <input type="text" name="username" placeholder="Username" value="<?php echo $userDetail['username']; ?>" required>
                    <input type="text" name="fullname" placeholder="Tên dầy đủ" required value="<?php echo $userDetail['fullname']; ?>">
                    <input type="phonenumber" name="sdt" placeholder="SDT" required value="<?php echo $userDetail['sdt']; ?>">
                    <input type="email" name="mail" placeholder="Mail" required value="<?php echo $userDetail['mail']; ?>">
                    <select name="department" id="department" required>
                        <?php
                        foreach (getDepartment() as $row) {
                            $department = $row['department'];
                            $selected = ($userDetail['department'] == $department) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $department; ?>" <?php echo $selected; ?>><?php echo $department; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <select name="chucvu" id="chucvu" required>
                        <option value="<?php echo $userDetail['chucvu']; ?>" selected> <?php echo $userDetail['chucvu']; ?> </option>
                    </select>
                </div>
            </div>
            <div class="inforForm">
                <h2 class="nameForm">Thông tin bổ sung</h2>
                <div class="bodyofForm">
                    <div class="inputHaveLable">
                        <label for="birthDay"> Ngày sinh</label>
                        <input type="date" name="birthDay" id="birthDay" value="<?php echo $userDetail['birthDay']; ?>" >
                    </div>
                    <div class="inputHaveLable">
                        <label for="startDay"> Ngày bắt đầu</label>
                        <input type="date" name="startDay" value="<?php echo $userDetail['startDay']; ?>" >
                    </div>

                    <input type="text" name="queQuan" placeholder="Quê quán" value="<?php echo $userDetail['queQuan']; ?>">
                    <input type="text" name="tamTru" placeholder="Tạm Trú" value="<?php echo $userDetail['tamTru']; ?>">
                    <input type="text" name="nameEmg" placeholder="Người liên hệ khẩn cấp" value="<?php echo $userDetail['nameEmg']; ?>">
                    <input type="tel" name="sdtEmg" placeholder="Số liên hệ khẩn cấp" value="<?php echo $userDetail['sdtEmg']; ?>">
                </div>

            </div>
            <?php 
                if ($_SESSION['admin'] || checkPerOfUser(30, $_SESSION['userINFO']['id'])){
                    ?>
                                    <div class="inforForm big">
                <h2 class="nameForm">Phân quyền</h2>
                <div class="bodyofForm">
                    <table class="data_table permissionTable">
                        <thead>
                            <tr class="headerTable ">
                                <th>Công việc</th>
                                <th> Thêm</th>
                                <th> Duyệt</th>
                                <th> Xóa</th>
                                <th> Sửa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                             $allPermission = getPermission($_GET['id']);
                             $arrPer = array_column($allPermission, 'id_role');
                            ?>
                            <?php
                                foreach (getAllJob() as $row) {
                                    $job = $row['job'];
                                    $actions = getActionofJob($job);
                                    // Ensure there are at least 4 actions
                                    $existingActions = array_column($actions, 'action');
                                    // Define the desired order
                                    $desiredOrder = ["Add", "Aporval", "Del", "Modify"];
                                    ?>
                                    <tr>
                                        <td><?php echo $job; ?></td>
                                        <?php
                                        foreach ($desiredOrder as $action) {
                                            $disabled = !in_array($action, $existingActions);
                                            $id;
                                            $selected_Per = false;
                                            //tìm id trong mảng nếu có hành động
                                            //action_ là giá trị có của mảng getActionofJob
                                            //action là 4 giá trị giả của mảng(desiredOrder)để xét 
                                            if(!$disabled){
                                                foreach ($actions as $action_) {
                                                    if ($action_['action'] === $action) {
                                                        $id = $action_['id'];
                                                        $selected_Per = in_array($id, $arrPer);
                                                    }
                                                }
                                            }
                                            ?>
                                            <td>
                                                <input type="checkbox" name="Permission[]" value="<?php echo $id; ?>" id="" <?php echo $disabled ? 'disabled' : ''; ?> <?php echo $selected_Per ? 'checked' : ''; ?>>
                                            </td>
                                        <?php
                                        }
                                        ?>
                                    </tr>
                                    <?php
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>   
                    <?php
                }
            
            ?>
         
        </div>
    </div>
</form>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
function updateSelectOption(parentSelectId, childSelectId, selectedValue) {
    var parentSelect = $('#' + parentSelectId);
    var childSelect = $('#' + childSelectId);

    parentSelect.change(function() {
        var selectedParent = parentSelect.val();
        updateChildOptions(selectedParent, childSelect, selectedValue);
    });

    function updateChildOptions(selectedParent, childSelect, selectedValue) {
        $.ajax({
            type: 'POST',
            url: 'QLNS/getdataUser.php',
            data: {
                selectedDepartment: selectedParent
            },
            success: function(response) {
                var childOptions = JSON.parse(response);
                childSelect.empty();

                $.each(childOptions, function(index, option) {
                    var isSelected = (option.chucvu == selectedValue) ? 'selected' : '';
                    childSelect.append($('<option>', {
                        value: option.chucvu,
                        text: option.chucvu,
                        selected: isSelected // Corrected syntax here
                    }));
                });
            }
        });
    }

    // Initial update of child options based on the initial selected parent
    updateChildOptions(parentSelect.val(), childSelect, selectedValue);
}

// Assuming $userDetail['chucvu'] is the selected value
updateSelectOption('department', 'chucvu', '<?php echo $userDetail['chucvu']; ?>');


function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#imagePreview').css('background-image', 'url('+e.target.result +')');
            $('#imagePreview').hide();
            $('#imagePreview').fadeIn(650);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
$("#avt").change(function() {
    readURL(this);
});
</script>
<?php 
exit();
?>