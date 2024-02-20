<?php 
    if(isset($_POST['addUser']) && isset($_POST['username']) && !isset($_POST['userId'])){
        $addby = $_SESSION['username_Login'];
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

        $avt = $_FILES['avt']['name'];
        $avt_tmp = $_FILES['avt']['tmp_name'];
        $avt = time().'_'.$username;
         

 

        $sqlAddUser = "INSERT INTO `tbl_user` 
        (`addby`, `mail`, `password`, `username`, `department`, `chucvu`, `fullname`, `sdt`, `otp`, `avt`, `birthDay`, `startDay`, `queQuan`, `tamTru`, `sdtEmg`,`nameEmg`) 
        VALUES 
        ('$addby', '$mail', '$password', '$username', '$department', '$chucvu', '$fullname', '$sdt', '$otp', '$avt', '$birthDay', '$startDay', '$queQuan', '$tamTru','$sdtEmg', '$nameEmg')";
        $queryAddUse = mysqli_query($mysqli, $sqlAddUser);
        move_uploaded_file($avt_tmp,'../asset/media/private/avt/'.$avt);
        if (isset($_POST["Permission"])) {
            // Retrieve the values of all 'Permission' inputs
            $permissionValues = $_POST["Permission"];
    
            // Initialize an array to store the values
            $permissionArray = [];
            require('QLNS/getdataUser.php');

            $id_User = getIDbyUNAME($username)['id'];
            // Loop through the values and push them into the array
            foreach ($permissionValues as $value) {
                $permissionArray[] = $value;
                $sql = "INSERT INTO `tbl_user_role`( `id_role`, `id_user`) VALUES ($value, $id_User)";
                $queryJob = mysqli_query($mysqli, $sql);
            }    
        } 
        exit();
        echo "<script> window.open('modules/admin.php?job=QLNS&action=personnel');</script>";
    }
    function checkValue($x){
        if(isset($_POST[$x])){
            return $x = $_POST[$x];
        } else {
            $x = '';
        }
        return $x;
    }
    
    
?>

<h1>Thêm nhân sự</h1>
<form action="" method="post" enctype="multipart/form-data">
    <div class="userForm">
        <div class="avtGrid">
            <div class="avatar-upload">
                <div class="avatar-edit">
                    <input type='file' name="avt" id="avt" accept=".png, .jpg, .jpeg" />
                    <label for="avt"></label>
                </div>
                <div class="avatar-preview">
                    <div id="imagePreview" style="background-image: url(../asset/media/base/user/user.png);">
                    </div>
                </div>
            </div>
            <button type="submit" name="addUser"
                style="    background-image: linear-gradient(147deg, #fe8a39 0%, #fd3838 74%);">Thêm
                nhân sự</button>
        </div>
        <div class="mainForm">
            <div class="inforForm">
                <h2 class="nameForm">Thông tin cơ bản</h2>
                <div class="bodyofForm">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="text" name="fullname" placeholder="Tên dầy đủ" required>
                    <input type="phonenumber" name="sdt" placeholder="SDT" required>
                    <input type="email" name="mail" placeholder="Mail" required>
                    <select name="department" id="department" required>
                        <?php
                    require('QLNS/getdataUser.php');
                    foreach (getDepartment() as $row) {
                        $department = $row['department'];
                    ?>
                        <option value="<?php echo $department; ?>"><?php echo $department; ?></option>
                        <?php
                    }
                    ?>
                    </select>

                    <select name="chucvu" id="chucvu" required>
                        <option value="chucvu">Quản Lý</option>
                        <option value="chucvu">Nhân viên</option>
                    </select>
                </div>
            </div>
            <div class="inforForm">
                <h2 class="nameForm">Thông tin bổ sung</h2>
                <div class="bodyofForm">
                    <div class="inputHaveLable">
                        <label for="birthDay"> Ngày sinh</label>
                        <input type="date" name="birthDay" placeholder="Ngày sinh">
                    </div>
                    <div class="inputHaveLable">
                        <label for="startDay"> Ngày bắt đầu</label>
                        <input type="date" name="startDay" placeholder="Ngày bắt đầu">
                    </div>

                    <input type="text" name="queQuan" placeholder="Quê quán">
                    <input type="text" name="tamTru" placeholder="Tạm Trú">
                    <input type="text" name="nameEmg" placeholder="Người liên hệ khẩn cấp">
                    <input type="tel" name="sdtEmg" placeholder="Số liên hệ khẩn cấp">
                </div>

            </div>
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
                                        //tìm id trong mảng nếu có hành động
                                        if(!$disabled){
                                            foreach ($actions as $action_) {
                                                if ($action_['action'] === $action) {
                                                    $id = $action_['id'];
                                                }
                                            }
                                        }
                                        ?>
                                        <td>
                                            <input type="checkbox" name="Permission[]" value="<?php echo $id; ?>" id="" <?php echo $disabled ? 'disabled' : ''; ?>>
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
        </div>
    </div>
</form>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
function updateSelectOption(parentSelectId, childSelectId) {
    // No need to use vanilla JavaScript for getting elements by ID
    // Use jQuery selectors instead
    var parentSelect = $('#' + parentSelectId);
    var childSelect = $('#' + childSelectId);

    parentSelect.change(function() {
        // Get the selected value
        var selectedParent = parentSelect.val();
        updateChildOptions(selectedParent, childSelect);
    });

    function updateChildOptions(selectedParent, childSelect) {
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
                    childSelect.append($('<option>', {
                        value: option.chucvu,
                        text: option.chucvu
                    }));
                });
            }
        });
    }

    // Initial update of child options based on the initial selected parent
    updateChildOptions(parentSelect.val(), childSelect);
}
updateSelectOption('department', 'chucvu');

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#imagePreview').css('background-image', 'url(' + e.target.result + ')');
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