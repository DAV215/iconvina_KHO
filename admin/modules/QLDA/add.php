<?php 
    if(isset($_POST['addProject']) && isset($_POST['projectName'])){
        $projectName = $_POST['projectName'];
        $createby_admin = $_SESSION['userFullname'];
        $startDay = $_POST['startDay'];
        $finishDay = checkValue('finishDay');
        $investment = checkValue('investment');
        $income = checkValue('income');
        $spent = checkValue('spent');

        $sql = "INSERT INTO `tbl_project`(
            `name`,
            `createby_admin`,
            `startDay`,
            `finishDay`,
            `investment`,
            `income`,
            `spent`
        )
        VALUES (
            '$projectName',
            '$createby_admin',
            '$startDay',
            '$finishDay',
            '$investment',
            '$income',
            '$spent'
        )";
        $query = mysqli_query($mysqli, $sql);

        echo "<script> window.open('modules/admin.php?job=QLDA&action=thongke');</script>";
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

<h1>Thêm dự án</h1>
<form action="" method="post" enctype="multipart/form-data">
    <div class="userForm">
        <div class="mainForm">
            <div class="inforForm">
                <h2 class="nameForm">Thông tin cơ bản</h2>
                <div class="bodyofForm">
                    <input type="text" name="projectName" placeholder="Tên Dự án" required>
                    <div class="inputHaveLable">
                        <label for="birthDay"> Ngày bắt đầu</label>
                        <input type="date" name="startDay" required>
                    </div>
                </div>
                <button type="submit" name="addProject" class="btnAddPrj"
                style="    background-image: linear-gradient(147deg, #fe8a39 0%, #fd3838 74%);">Thêm dự án</button>
            </div>
            <div class="inforForm">
                <h2 class="nameForm">Thông tin bổ sung</h2>
                <div class="bodyofForm">

                    <div class="inputHaveLable">
                        <label for="startDay"> Ngày kết thúc</label>
                        <input type="date" name="finishDay" >
                    </div>

                    <input type="text" name="investment" placeholder="Vốn đầu tư">
                    <input type="text" name="income" placeholder="Thu nhập">
                    <input type="text" name="spent" placeholder="Chi tiêu">
                </div>

            </div>

        </div>
    </div>

</form>
<?php 
exit();
?>