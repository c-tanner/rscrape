<?php

require __DIR__."/../config.php";

class CurlRequest {

    var $url;
    var $response;

    public function __construct($url) {
        $this->url = $url;
    }

    public function execute() {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Referer: reddit.com"
            ),
        ));
        $this->response = curl_exec($curl);
        return $this->response;

    }

    public function toJson() {
        return json_decode($this->response, true);
    }

    public function toFile($filename) {
        $path = __DIR__."/../media/$filename";
        if (file_exists($path)) unlink($path);
        if ($fp = fopen($path,'x')) {
            fwrite($fp, $this->response);
            fclose($fp);
            return true;
        } else {
            return false;
        }
    }

}

class Posts {

    var $posts = [];

    public function __construct() {
        if ($posts = $this->getCachedPosts()) {
            $this->posts = json_decode($posts, true);
        }
    }

    public function getCachedPosts() {
        if (file_exists(__DIR__."/posts.txt")) {
            return file_get_contents(__DIR__."/posts.txt");
        } else {
            return false;
        }
    }

    public function getAllPosts() {
        foreach (SUBREDDITS as $sub) {
            $sub = new Subreddit($sub);
            $sub->setTopPosts();
            $top_posts = $sub->getTopPosts();
            if (count($top_posts) > 0) {
                foreach ($top_posts as $post) {
                    $this->posts[] = $post;
                }
            }
        }
        if (file_put_contents(__DIR__."/posts.txt", json_encode($this->posts))) {
            return $this->posts;
        } else {
            return false;
        }
    }

    public function getFilteredPosts($sub = "", $orderby = "") {
        $filtered_posts = [];
        if (strlen($sub) > 0) {
            foreach ($this->posts as $post) {
                if ($post['subreddit'] === $sub) {
                    array_push($filtered_posts, $post);
                }
            }
        } else {
            $filtered_posts = $this->posts;
        }
        if (strlen($orderby) > 0) {
            if ($orderby == "recent") {
                array_multisort(array_column($filtered_posts, "created_at"), SORT_DESC, $filtered_posts);
            } elseif ($orderby == "popular") {
                array_multisort(array_column($filtered_posts, "upvotes"), SORT_DESC, $filtered_posts);
            }
        }
        return $filtered_posts;
    }

    public function randomize($num = 100, $offset = 0) {
        shuffle($this->posts);
        return array_slice($this->posts, $offset, $num);
    }

}

class Subreddit {

    var $slug;
    var $top_posts = [];

    public function __construct($slug) {
        $this->slug = $slug;
    }

    public function setTopPosts() {
        $request = new CurlRequest("https://www.reddit.com/r/{$this->slug}/top.json");
        $request->execute();
        $response = $request->toJson();
        if (isset($response['data']['children'])) {
            foreach ($response['data']['children'] as $post) {
                if ($post['data']['preview']['images'][0]['source']['url']) {
                    $image_url = $post['data']['preview']['images'][0]['source']['url'];
                    $filename = explode("?", explode(".it/", $image_url)[1])[0];

                    $request = new CurlRequest(htmlspecialchars_decode($image_url));
                    $request->execute();

                    $epoch = $post['data']['created_utc'];
                    $created_at = new DateTime("@$epoch");
                    $created_at->format("Y-m-d H:i:s");
                    $now = new DateTime();
                    $interval = $now->diff($created_at);
                    $days = $interval->format("%a");
                    $hours = $interval->format("%h");
                    $minutes = $interval->format("%i");
                    if ($days > 0) {
                        $elapsed = $days."d";
                    } elseif ($hours > 0) {
                        $elapsed = $hours."h";
                    } else {
                        $elapsed = $minutes."m";
                    }

                    if ($request->toFile($filename)) {
                        array_push($this->top_posts, array(
                            "subreddit" => $post['data']['subreddit'],
                            "upvotes" => $post['data']['ups'],
                            "created_at" => $created_at,
                            "elapsed" => $elapsed,
                            "filename" => $filename
                        ));
                    }
                }
            }
        } else {
            return false;
        }
    }

    public function getTopPosts() {
        return $this->top_posts;
    }

}