<?php
require "config.php";
$time = microtime();
$parts = explode(" ", $time);
$current_time_with_microseconds = date("H:i:s", $parts[1]) . "." . $parts[0];
log_writer2($_GET["point"],$current_time_with_microseconds,"lv1");

?>