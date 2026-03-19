<?php 
    include(__DIR__.'/../QL_CLIENT/code/getdata_Client.php');
    $client = new client;
    $order = new Order;
    if(isset($_POST['client_listed_table'])){
        echo json_encode($client->getAll(" 1 ")) ;
    }
    if(isset($_POST['order_listed'])){
        echo json_encode($order->getAll(" 1 ")) ;
    }
?>
