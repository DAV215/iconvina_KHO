<?php
    require_once __DIR__ . '/../vendor/autoload.php';

    use DeviceDetector\DeviceDetector;
    function checkDevice(){

        
        // Fetch the user agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        
        // Create an instance of DeviceDetector
        $dd = new DeviceDetector($userAgent);
        
        // Extract any information you want
        $osInfo = $dd->getOs();
        $device = $dd->getDeviceName();
        $brand = $dd->getBrandName();
        $model = $dd->getModel();
        
        return $userAgent;
    }
?>