<?php
$rssurl = "https://www.numerama.com/feed/";

$rss = simplexml_load_file($rssurl);


if ($rss) {
    echo "<h1>Veilys : " . $rss->channel->title . "</h1>";
    echo "<ul>";
    $i = 0;

    foreach ($rss->channel->item as $item) {
        if ($i >= 5) break;
        $title = $item->title;
        $link = $item->link;
        $date = date('d/m/Y', strtotime($item->pubDate));

        echo "<li>";
        echo "<strong><a href='$link' target='_blank'>$title</a></strong><br>";
        echo "<em>Publié le $date</em>";
        echo "</li><br>";

        $i++;
    }
}


echo "<ul>";
