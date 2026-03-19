<?php 
    include('getdata_Client.php');
    if(isset($_GET['action'])){
        $action = $_GET['action'];
        if($action == 'Client'){
            if(isset($_GET['actionChild'])){
                $actionChild = $_GET['actionChild'];
                if($actionChild == 'add_Client'){
                    include('thongke.php');
                    include('add.php');
                }
                if($actionChild == 'client_detail'){
                    include('client_detail.php');
                }
            }else{
                include('thongke.php');
            }
        }elseif($action == 'Order'){
            if(isset($_GET['actionChild'])){
                $actionChild = $_GET['actionChild'];
                if($actionChild == 'add_Order'){
                    include('thongke_Order.php');
                    include('add_Order.php');
                }elseif($actionChild == 'order_Detail'){
                    include('thongke_Order.php');
                }
            }else{
                include('thongke_Order.php');
            }
        }
    }else{
        include('thongke_Order.php');
    }


?>
<script src="../asset/js/KHO/Client.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.0.3/js/dataTables.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.css">