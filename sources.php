<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "veilys";

$liste_flux = [
    "https://portswigger.net/research/rss",
    "https://snyk.io/fr/blog/feed/",
    "https://github.blog/category/security/feed/",
    "https://checkmarx.com/blog/feed/",
    "https://www.sonarsource.com/blog/rss.xml",
    "https://www.wiz.io/blog/rss.xml"
];


$sqlconnexion = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($sqlconnexion->connect_error) {
    die("Erreur de connexion SQL : " . $sqlconnexion->connect_error);
}
$sqlconnexion->set_charset("utf8mb4");




foreach ($liste_flux as $flux) {
    echo "Lecture de " . $flux;
    $rss = @simplexml_load_file($flux);

    if ($rss) {
        echo "<h1>Veilys : " . $rss->channel->title . "</h1>";
        echo "<ul>";
        $i = 0;

        foreach ($rss->channel->item as $item) {
            if ($i >= 50) break;
            $title = $item->title;
            $link = $item->link;
            $date = date('d/m/Y', strtotime($item->pubDate));

            $check = $sqlconnexion->prepare("SELECT articleID FROM articles WHERE lien = ?");
            $check->bind_param("s", $link);
            $check->execute();
            $check->store_result();

            echo "<li>";
            if ($check->num_rows == 0) {
                $insert = $sqlconnexion->prepare("INSERT INTO articles (titre, lien, date) VALUES (?, ?, ?)");
                $insert->bind_param("sss", $title, $link, $date);
                $insert->execute();
                echo "Article enregistré en base de données : ";
            } else {
                echo "Article déjà enregistré en base de données : ";
            }


            echo "<strong><a href='$link' target='_blank'>$title</a></strong><br>";
            echo "<em>Publié le $date</em>";
            echo "</li><br>";

            $check->close();
            if (isset($insert)) {
                $insert->close();
            }

            $i++;
        }
        echo "</ul>";
    }
}



$sqlconnexion->close();
