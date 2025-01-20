<?php
session_start();
// Générer le jeton CSRF et le stocker dans la session
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Etape 1 : Récupérer l'id du match depuis l'url
if(isset($_GET['id']) && isset($_GET['client'])){
    $match_id = htmlspecialchars($_GET['id']);
    $client = htmlspecialchars($_GET['client']);
} else{
    // Rediriger vers une page d'erreur si l'id du match n'est pas fourni dans l'url
    header('Location:index.php?reg_err=1');
    exit();
}

require_once('config/config.php');

$stmt = $bdd->prepare('SELECT * FROM partie WHERE matchs = :match_id');
$stmt->execute(array('match_id' => $match_id));
$parties = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmp = $bdd->prepare('SELECT * FROM matchs WHERE id = :match_id AND users_client= :client');
$stmp->execute(array('match_id' => $match_id, 'client' => $client));
$match = $stmp->fetch(PDO::FETCH_ASSOC);

// Récupérer le nom du tournoi en utilisant la jointure avec la table 'tournois'
$tournoi_nom = '';
if ($match['categorie'] == 1 && isset($match['tournoi'])) {
    $stmt_tournoi = $bdd->prepare('SELECT tournois FROM tournois WHERE id = :tournoi_id');
    $stmt_tournoi->execute(array('tournoi_id' => $match['tournoi']));
    $tournoi_nom = $stmt_tournoi->fetchColumn();
}

// Récupérer le nom du tour en utilisant la jointure avec la table 'tournoi_tour'
$tour_nom = '';
if ($match['categorie'] == 1 && isset($match['tour'])) {
    $stmt_tour = $bdd->prepare('SELECT tour FROM tournoi_tour WHERE id = :tour_id');
    $stmt_tour->execute(array('tour_id' => $match['tour']));
    $tour_nom = $stmt_tour->fetchColumn();
}

$categorie = $bdd->query("SELECT categorie FROM categorie ORDER BY id ASC");
// $match = $bdd->query("SELECT * FROM matchs INNER JOIN categorie c ON categorie = c.id");
$sql = "SELECT m.*, c.categorie
        FROM matchs m
        INNER JOIN categorie c ON m.categorie = c.id";

$stmt = $bdd->query($sql);
if ($stmt !== false) {
    $matchs = $stmt->fetch(PDO::FETCH_ASSOC);
}else{
    echo "Aucun résultat trouvé";
}

if ($match['created_at'] != NULL || $match['updated_at'] != NULL){
    $createdTimestamp = strtotime($match['created_at']);
    $updatedTimestamp = strtotime($match['updated_at']);
    $timeDifferenceInSeconds = $updatedTimestamp - $createdTimestamp;

    // Convertir la différence en heure et minute
    $hours = floor($timeDifferenceInSeconds / 3600);
    $minutes = floor(($timeDifferenceInSeconds % 3600) / 60);

    // Formatage de l'heure et des minutes
    $timeDifferenceFormatted = sprintf("%02d:%02d", $hours, $minutes);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/a6212ffa8d.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/avatar.ico">
    <title>BIllardScore - Détails du match</title>
</head>
<body>
    <div class="tableau_detail">
        <h2>Détails du match</h2>
        <table class="tableau">
            <tr>
                <th>Couleur jouée</th>
                <th>Partie gagnée</th>
                <th>Casse ferme</th>
                <th>Reprise ferme</th>
                <th>Noire entrée en partie</th>
                <th>Perte par grise</th>
            </tr>
            <?php foreach ($parties as $partie): ?>
                <tr>
                    <td><?= $partie['jaune'] == 1 ? '<i class="fa-solid fa-circle" style="color: #dbd400;"></i>' : '<i class="fa-solid fa-circle" style="color: #db0000;"></i>' ?></td>
                    <td><?= $partie['gagne'] == 1 ? '<i class="fa-solid fa-check" style="color: #63bb1b;"></i>' : '<i class="fa-regular fa-xmark" style="color: #c81e1e;"></i>' ?></td>
                    <td><?= $partie['casse_ferme'] == 1 ? '<i class="fa-solid fa-check" style="color: #63bb1b;"></i>' : '' ?></td>
                    <td><?= $partie['reprise_ferme'] == 1 ? '<i class="fa-solid fa-check" style="color: #63bb1b;"></i>' : '' ?></td>
                    <td><?= $partie['noire'] == 1 ? '<i class="fa-regular fa-xmark" style="color: #c81e1e;"></i>' : '' ?></td>
                    <td><?= $partie['grise'] == 1 ? '<i class="fa-regular fa-xmark" style="color: #c81e1e;"></i>' : '' ?></td>
                </tr>
                <?php endforeach; ?>
        </table>
        
        
        <h4>Date du match : <?= date('d-m-Y', strtotime($match['date']))?></h4>
        <?=($match["created_at"] === NULL || $match["updated_at"]=== NULL) ? "" :"<h4> Durée du match : " . $timeDifferenceFormatted  ?></h4>
        <?php 
                            if($match['categorie'] == 1){
                                ?><h4>Type de match : Tournoi individuel</h4>
                                    <h4>Tournoi : <?= $tournoi_nom ?></h4>
                                    <h4>Manche : <?= $tour_nom ?></h4></h4><?php
                            } else if($match['categorie'] == 2){
                                ?><h4>Type de match : Tournoi équipe</h4><?php
                            } else if($match['categorie'] == 3){
                                ?><h4>Type de match : Amical</h4><?php
                            }else if($match['categorie'] == 4){
                                ?><h4>Type de match : Entraînement</h4><?php
                            }?>
                            <h4>Score final : <?=$match['score_joueur1']?> - <?=$match['score_joueur2']?></h4>
                            <h4>Joueur affronté : <?=$partie['joueur']?></h4>
        <h4>Première casse : <?= $match['casse'] == 1 ? 'Moi' : $partie['joueur'] ?></h4>
        <h4><a href="user-admin.php?client=<?php echo $client ?>"><i class="fa-solid fa-house-person-return"></i><br>Retour accueil</a></h4>
        <div class="feuilleMatch">
            <h4>Feuille de match</h4>
            <img src="<?=$match['feuille_match']?>" alt="">
        </div>
    
    </div>

</body>
</html>