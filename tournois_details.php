<?php
session_start();
if($_SESSION["autoriser"]!="oui" || !isset($_GET['client'])){
    header("location:login.php");
    die();
}
require_once('config/config.php');

$client_id = $_GET['client'];

// Récupérer le username correspondant à l'ID de l'utilisateur
$stmt = $bdd->prepare('SELECT * FROM users WHERE client = :user_client');
$stmt->execute(array('user_client' => $client_id));
$user = $stmt->fetch(PDO::FETCH_ASSOC);


// Récupérer les noms des tournois de l'utilisateur
$sql = "SELECT DISTINCT t.id AS tournoi_id, t.tournois, t.date_debut, t.date_fin
        FROM tournois t
        WHERE t.user_client = :user_client
        ORDER BY t.date_debut ASC";
$stmt = $bdd->prepare($sql);
$stmt->execute(array('user_client' => $client_id));
$tournois = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/a6212ffa8d.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" href="img/avatar.ico">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <title>BillardScore - Détails tournois</title>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
<!-- Header page d'accueil -->
    <section class="profil">
        <!-- Affichage bouton modification profil -->
        <div class="modif-profil">
            <a href="profil.php?client=<?=$user['client']?>"><i class="fa-solid fa-pen" style="color: #ffffff;"></i></a>
        </div>
        <!-- Affichage photo de profil -->
        <div class="fond-img-profil">
            <?php if(isset($user['profil_img'])){
                ?><img src="<?=$user['profil_img']?>" alt=""><?php
            }else{
                ?>
            <img src="img/profil.jpg" alt=""><?php
            }?>
        </div>
        <!-- Affichage bouton pour se déconnecter -->
        <div class="ajout-img-profil">
            <a href="deconnexion.php"><i class="fa-solid fa-person-walking-arrow-right" style="color: #ffffff;"></i></a>
        </div>
    </section>
    <!-- Affichage nom et prénom de l'user -->
    <div class="infos-profil">
        <p><span><?= $user['validate'] == 1 ? '<i class="fa-solid fa-badge-check" style="color: #59bd4c;"></i>' : '' ?></span> <?php echo $user['prenom'] . ' ' .$user['nom']?></p>
    </div>
    <section class="container accueil">
                <a href="user-admin.php?client=<?=$client_id?>"><i class="fa-solid fa-house"></i><br>Retour accueil</a>
    </section>
    <div class="container details_tournoi">
    <h1>Liste des tournois enregistrés</h1>
    <!-- <h2>Tournoi : <?=$tournoi_id?></h2> -->
        <div class="accordion" id="accordionExample">
            <?php foreach ($tournois as $tournoi) { 
                $dateDebutFormatee = date("d-m-Y", strtotime($tournoi['date_debut']));
                $dateFinFormatee = date("d-m-Y", strtotime($tournoi['date_fin']));

                // Vérifier si le tournoi a des matchs associés dans la table matchs
        $sqlMatchs = "SELECT COUNT(*) FROM matchs WHERE tournoi = :tournoi_id AND users_client = :user_client";
        $stmtMatchs = $bdd->prepare($sqlMatchs);
        $stmtMatchs->execute(array('user_client' => $client_id, 'tournoi_id' => $tournoi['tournoi_id']));
        $hasMatchs = $stmtMatchs->fetchColumn();
?>                
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?=$tournoi['tournoi_id']?>" aria-expanded="true" aria-controls="collapse<?=$tournoi['tournoi_id']?>">
                  <?php echo $tournoi['tournois'] . ' (Du : ' . $dateDebutFormatee . ' au : ' . $dateFinFormatee . ')';?>
                </button>
              </h2>
              <div id="collapse<?=$tournoi['tournoi_id']?>" class="accordion-collapse collapse " data-bs-parent="#accordionExample">
                  <div class="accordion-body">
                      <?php
                        /// Récupérer les détails des matchs joués dans ce tournoi
                            $sql = "SELECT m.tournoi, m.tour, m.joueur, m.score_joueur1, m.score_joueur2, tt.tour AS nom_tour
                                    FROM matchs m
                                    INNER JOIN tournoi_tour tt ON m.tour = tt.id
                                    WHERE m.tournoi = :tournoi_id
                                    AND m.users_client = :user_client
                                    ORDER BY m.tour ASC";

                            $stmt = $bdd->prepare($sql);
                            $stmt->execute(array('user_client' => $client_id, 'tournoi_id' => $tournoi['tournoi_id']));
                            $details_tournoi = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if(!empty($details_tournoi)){
                                foreach ($details_tournoi as $detail) {
                                    // Afficher les détails des matchs ici
                                    echo "Tour : " . $detail['nom_tour'] . "<br>";
                                    echo "Joueur : " . $detail['joueur'] . "<br>";
                                    echo "Score : " . $detail['score_joueur1'] . " - " . $detail['score_joueur2'] . "<br>";
                                    echo "<hr>";
                                }
                            } else {
                                echo "<em>Pas encore de résultat pour ce tournoi</em>";
                            }
                        ?>
                </div>
              </div>
            </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>
