<?php 

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require("Compress.php");

    $quality = 30;
    $pngQuality = 9;
    $targetDir = "/home/akshat-jain/Workspace/kompressor/dogs-subset";

    $compressor = new Compress($quality, $pngQuality, $targetDir."-min");

    $arr_done = array();
    $arr_notdone = array();

    foreach (scandir($targetDir) as $i => $f) {
        if($f !== "." && $f !== "..") {
            $compressor->set_file_url($targetDir."/".$f);
            $compressor->set_new_name_image($f);
            array_push($arr_done, $compressor->compress_image());
        } else {
            array_push($arr_notdone, $f);
        }
    }

    echo "<pre>";
    print_r($arr_done);
    echo "</pre>";

    echo "<pre>";
    print_r($arr_notdone);
    echo "</pre>";

?>