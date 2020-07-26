<?php

require __DIR__."/../app/Classes.php";

$posts = new Posts();

if (isset($_GET['type_filter']) && isset($_GET['sub_filter'])) {
  $top_posts = $posts->getFilteredPosts($_GET['sub_filter'], $_GET['type_filter']);
} else {
  if (!$posts->getCachedPosts()) {
    $top_posts = $posts->getAllPosts();
  } else {
    $top_posts = $posts->randomize();
  }
}

echo json_encode($top_posts);