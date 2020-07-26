<?php

require __DIR__."/../config.php";
$subs = SUBREDDITS;
sort($subs);
echo json_encode($subs);