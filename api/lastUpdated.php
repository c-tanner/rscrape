<?php

$last_modified = filemtime("../app/posts.txt");
$mod_time = new DateTime("@$last_modified");
$now = new DateTime();
$interval = $now->diff($mod_time);
$result = [
    "mins" => $interval->format("%i")
];

echo json_encode($result);