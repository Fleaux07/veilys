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

    $prompt = "Tu es un expert en veille technologique. Je te donne le titre et le début d'un article.
        Titre : \"$title\".
        Contenu : \"$cleantext\".
        
        Renvoie-moi UNIQUEMENT un objet JSON valide avec deux clés :
        - \"resume\" : Rédige un résumé très clair de 2 phrases maximum sur le sujet de l'article.
        - \"pertinent\" : Mets 1 si ça parle de technologie, d'IA, de cybersécurité ou de science. Mets 0 sinon.
        Ne renvoie aucun autre texte, pas de blabla, juste le JSON brut.";
    $url_ia = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . GEMINI_API_KEY;
    $data_ia = [
            "contents" => [
                ["parts" => [["text" => $prompt]]]
            ]
        ];
        
        $ch_ia = curl_init($url_ia);
        curl_setopt($ch_ia, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_ia, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch_ia, CURLOPT_POST, true);
        curl_setopt($ch_ia, CURLOPT_POSTFIELDS, json_encode($data_ia));
        curl_setopt($ch_ia, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_ia, CURLOPT_SSL_VERIFYHOST, false);
        
        $reponse_api = curl_exec($ch_ia);
        curl_close($ch_ia);

        $responsedecode = json_decode($reponse_api, true);        
        if (isset($responsedecode['candidates'][0]['content']['parts'][0]['text'])) {    
            $texte_ia = $responsedecode['candidates'][0]['content']['parts'][0]['text'];
            echo "<br><strong>Réponse de l'IA :</strong><br>";
            echo $texte_ia;
            
        } else {
            echo "Erreur : L'IA n'a pas répondu comme prévu.";
        }





    } else {
    echo "Rien à traiter";
}
