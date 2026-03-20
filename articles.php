<?php

require_once 'config.php';

$sqlconnexion = new mysqli(dbhost, dbusername, dbpass, dbname);

$sqlquery = $sqlconnexion->prepare('SELECT articleID, titre, lien, resume_ia, pertinent_ia FROM articles WHERE pertinent_ia = 1');
$sqlquery->execute();
$sqlresult = $sqlquery->get_result();

foreach ($sqlresult as $row) {
    $id = $row['articleID'];
    $title = $row['titre'];
    $resume = $row['resume_ia'];
    $link = $row['lien'];

    echo $title . "<br>" . "<a href=". $link . ">Cliquer sur le lien</a>".  $resume . "<br>";

}
