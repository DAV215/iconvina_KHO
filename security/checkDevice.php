<?php
require_once '../vendor/autoload.php';
use DeviceDetector\DeviceDetector;

// Fetch the user agent
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// Create an instance of DeviceDetector
$dd = new DeviceDetector($userAgent);

// Extract any information you want
$osInfo = $dd->getOs();
$device = $dd->getDeviceName();
$brand = $dd->getBrandName();
$model = $dd->getModel();
echo $osInfo.$brand.$model ;
echo $userAgent;
?>