<?php
require_once 'config.php';

$sqlconnexion = new mysqli(dbhost, dbusername, dbpass, dbname);
if ($sqlconnexion->connect_error) {
    die("Erreur SQL : " . $sqlconnexion->connect_error);
}
$sqlconnexion->set_charset("utf8mb4");

$stat_total = $sqlconnexion->query("SELECT COUNT(*) as nb FROM articles")->fetch_assoc()['nb'];
$stat_attente = $sqlconnexion->query("SELECT COUNT(*) as nb FROM articles WHERE resume_ia IS NULL")->fetch_assoc()['nb'];
$stat_pertinents = $sqlconnexion->query("SELECT COUNT(*) as nb FROM articles WHERE pertinent_ia = 1")->fetch_assoc()['nb'];

$recherche = isset($_GET['q']) ? trim($_GET['q']) : '';

if (!empty($recherche)) {
    $mot_cle = "%" . $recherche . "%";
    $sqlquery = $sqlconnexion->prepare('SELECT articleID, titre, lien, resume_ia FROM articles WHERE (titre LIKE ? OR resume_ia LIKE ?) ORDER BY articleID DESC LIMIT 50');
    $sqlquery->bind_param("ss", $mot_cle, $mot_cle);
} else {
    $sqlquery = $sqlconnexion->prepare('SELECT articleID, titre, lien, resume_ia FROM articles WHERE pertinent_ia = 1 ORDER BY articleID DESC LIMIT 50');
}

$sqlquery->execute();
$articles_pertinents = $sqlquery->get_result();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Veilys</title>
    <link href="css/style.css" rel="stylesheet">
</head>

<body>

    <div class="container">
        <h1>Dashboard Veilys</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Articles</h3>
                <div class="nombre"><?= $stat_total ?></div>
            </div>
            <div class="stat-card">
                <h3>À Traiter par l'IA</h3>
                <div class="nombre"><?= $stat_attente ?></div>
            </div>
            <div class="stat-card">
                <h3>Articles Pertinents</h3>
                <div class="nombre"><?= $stat_pertinents ?></div>
            </div>
        </div>

        <div class="actions">
            <strong>Actions rapides : </strong>
            <a href="sources.php" target="_blank" class="btn btn-source">Récupérer les sources</a>
            <a href="traitement_ia.php" target="_blank" class="btn btn-ia">Lancer un traitement IA</a>
        </div>

        <div class="search-container">
            <form method="GET" action="index.php">
                <input type="text" name="q" class="search-input" placeholder="Rechercher une faille, un logiciel, un mot-clé..." value="<?= htmlspecialchars($recherche) ?>">
                <button type="submit" class="search-btn">🔍 Chercher</button>
                <?php if (!empty($recherche)): ?>
                    <a href="index.php" class="clear-btn">❌ Annuler</a>
                <?php endif; ?>
            </form>

            <?php if (!empty($recherche)): ?>
                <p style="color: #38bdf8; margin-top: 15px; font-size: 16px;">
                    Résultats pour la recherche : <strong>"<?= htmlspecialchars($recherche) ?>"</strong>
                    (<?= $articles_pertinents->num_rows ?> trouvés)
                </p>
            <?php endif; ?>
        </div>

        <h2><?= !empty($recherche) ? 'Résultats de votre recherche' : 'Dernières actualités pertinentes' ?></h2>

        <?php if ($articles_pertinents->num_rows > 0): ?>
            <?php foreach ($articles_pertinents as $row): ?>
                <div class="article-card">
                    <a href="<?= htmlspecialchars($row['lien']) ?>" target="_blank">
                        <?= htmlspecialchars($row['titre']) ?>
                    </a>
                    <div class="resume">
                        <strong>Résumé IA :</strong>
                        <?= !empty($row['resume_ia']) ? htmlspecialchars($row['resume_ia']) : "<em>En attente de traitement par l'IA...</em>" ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun article trouvé. <?= !empty($recherche) ? 'Essayez un autre mot-clé.' : 'Lancez une récupération et un traitement IA !' ?></p>
        <?php endif; ?>

    </div>

</body>

</html>

<?php
$sqlquery->close();
$sqlconnexion->close();
?>