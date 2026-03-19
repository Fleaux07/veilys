<?php

require_once 'config.php';

$sqlconnexion = new mysqli("localhost", "root", "", "veilys");
if ($sqlconnexion->connect_error) {
    die("Erreur SQL : " . $sqlconnexion->connect_error);
}
$sqlconnexion->set_charset("utf8mb4");


$query = "SELECT articleID, titre, lien FROM articles WHERE resume_ia IS NULL LIMIT 1";
$result = $sqlconnexion->query($query);

if ($result->num_rows > 0) {
    $article = $result->fetch_assoc();

    $id = $article['articleID'];
    $title = $article['titre'];
    $link = $article['lien'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $link);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $html = curl_exec($ch);
    curl_close($ch);

    if ($html) {

        $htmlclean = preg_replace(array('@<script[^>]*?>.*?</script>@si', '@<style[^>]*?>.*?</style>@si'), '', $html);
        $texthtml = strip_tags($htmlclean);
        $cleantext = preg_replace('/\s+/', ' ', $texthtml);

        echo "Page aspiré et nettoyer";
        echo "<em>" . substr($cleantext, 0, 300) . "...</em>";
    } else {
        echo "Page imposssible à aspiré";
    }
} else {
    echo "Rien à traiter";
}
