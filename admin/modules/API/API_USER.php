<?php

use voku\helper\UTF8;

    include('..\QLNS\getdataUser.php');
    if(isset($_POST['getdataStaff'])){
        $u = new user;
        $all = ($u->getAll(' `fullname`, `id` ', " 1 "));
        echo json_encode($all);
    }
    if(isset($_POST['getdataStaff_fromID'])){
        $u = new user;
        $id_user = $_POST['id_user'];
        $all = ($u->getAll(' `fullname`, `id` ', " id = $id_user ")[0]);
        echo json_encode(array('fullname' => $all['fullname']));
    }
?>