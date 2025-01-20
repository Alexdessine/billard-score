<?php 
session_start();
if($_SESSION["autoriser"]!="oui" || !isset($_GET['client'])){
    header("location:login.php");
    die();
}
require_once('config/config.php');

// Récupérer l'ID de l'utilisateur connecté
$user_client = htmlspecialchars($_GET['client']);
if(isset($_GET['valid'])){
    $validation= htmlspecialchars($_GET['valid']);
    if($validation == 1) {
        // Mettre à jour le champ "validate" de la table user
        $updateClient = $bdd->prepare('UPDATE users SET validate = 1 WHERE client = :user_client');
        $updateClient->execute(array('user_client' => $user_client));
    }
}


// Récupérer le username correspondant à l'ID de l'utilisateur
$stmt = $bdd->prepare('SELECT * FROM users WHERE client = :user_client');
$stmt->execute(array('user_client' => $user_client));
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer le dernier match
$stmt = $bdd->prepare('SELECT * FROM matchs WHERE users_client = :user_client ORDER BY id DESC LIMIT 1');
$stmt->execute(array('user_client' => $user_client));
$dernierMatchs = $stmt->fetch(PDO::FETCH_ASSOC);


// Le username de l'utilisateur connecté
$username = $user['username'];
$client = $user['client'];

    // Récupérer les infos du tournoi du user
$sql = "SELECT DISTINCT c.tournois, tt.tour AS nom_tour, t.etape AS plus_haute_etape
        FROM matchs m 
        INNER JOIN tournois c ON m.tournoi = c.id
        INNER JOIN (
            SELECT id, tournoi, MAX(tour) as etape
            FROM matchs
            GROUP BY tournoi
        ) t ON m.tournoi = t.tournoi AND m.tour = t.etape
        INNER JOIN tournoi_tour tt ON m.tour = tt.id
        WHERE c.user_client = :user_client ORDER BY m.id DESC LIMIT 1";
$stmt = $bdd->prepare($sql);
$stmt->execute(array('user_client' => $client));
$tournois = $stmt->fetch(PDO::FETCH_ASSOC);


// Récupérer les infos du prochain tournoi à venir
$sql = "SELECT tournois, lieu, date_debut
        FROM tournois
        WHERE date_debut > NOW() AND  user_client = :user_client
        ORDER BY date_debut ASC
        LIMIT 1";
$stmt = $bdd->prepare($sql);
$stmt->execute(array('user_client' => $client));
$prochainTournoi = $stmt->fetch(PDO::FETCH_ASSOC);

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <title>BillardScore</title>
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
    <div class="infos-profil col-sm-12 col-md-12">
        <p><span><?= $user['validate'] == 1 ? '<i class="fa-solid fa-badge-check" style="color: #59bd4c;"></i>' : '' ?></span> <?php echo $user['prenom'] . ' ' .$user['nom']?></p>
    </div>

    <!-- On vérifie si le user à valider son compte -->
    <?php
        if ($user['validate'] == 0){
            ?><p class="alert alert-warning validation">N'oubliez pas de valider votre compte</p><?php
        } 
        if(isset($_GET['valid'])){
           if($validation == 1){
               ?><p class="alert alert-success validation">Vous venez de valider votre compte</p><?php
           }
        }
    ?>
    <section class="vue-ensemble">
        <!-- Ici on affiche le dernier match joué -->
        <div class="fond">
            <div class="petit-rect"></div>
            <div class="school">
                <div class="school-titre">
                    <a href="exercices/exercices.php?client=<?php echo $client?>">
                        <h4><i class="fa-sharp fa-solid fa-graduation-cap" style="color: #ffffff;"></i> Exercices</h4>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <section class="vue-ensemble">
        <!-- Ici on affiche le dernier match joué -->
        <div class="fond match_fond">
            <div class="petit-rect"></div>
            <div class="match">
                <div class="match-titre">
                    <h4><i class="fa-solid fa-pool-8-ball" style="color: #ffffff;"></i> Dernier match joué</h4>
                </div>
                <div class="match-contenu">
                    <!-- On vient récupérer dans la base de données le dernier match joué 
                    Si l'utilisateur n'a joué aucun match on affiche un message "Pas encore de matchs joués -->
                    <?php 
                        if(isset($dernierMatchs['joueur'])){
                            // On formate la date pour qu'elle s'affiche "01-01-2000"
                            $dateMatchFormatee = date("d-m-Y", strtotime($dernierMatchs['date']));
                            ?><p><?php echo $dateMatchFormatee . ' | ' .$dernierMatchs['joueur'] . ' | ' . $dernierMatchs['score_joueur1'] .' - ' .$dernierMatchs['score_joueur2']?></p><?php
                        }else{
                            echo '<p>Pas encore de matchs joués</p>';
                        }
                    ?>
                </div>
            </div>
        </div>
        <div class=bouton>
            <!-- Ici on affiche le bouton pour accéder aux statistiques de l'utilisateur -->
            <div class="button">
                <a href="statistiques.php?client=<?=$client?>&page=1">Mes stats</a>
            </div>
            <!-- Ici on affiche le bouton pour ajouter un match à l'utilisateur
            Ce bouton est relié à la modal "ModalMatch" en fin de page -->
            <div class="button">
                <a id="openModalMatch">Ajouter un match</a>
            </div>
        </div>
    </section>
    <section class="vue-ensemble"style="margin-bottom: -55px;" >
        <!-- Ici on affiche le dernier tournoi joué -->
        <div class="fond">
            <div class="petit-rect"></div>
            <div class="tournoi">
                <div class="tournoi-titre-resultat">
                    <h4><i class="fa-solid fa-trophy" style="color: #f5f5f5;"></i> Résultat dernier tournoi</h4>
                </div>
                <div class="tournoi-contenu">
                    <!-- On vient récupérer dans la base de données le dernier tournoi joué 
                    Si l'utilisateur n'a joué aucun tournoi on affiche un message "Pas encore de tournois joués -->
                    <?php 
                        if (isset($tournois['tournois'])) {
                            echo '<p>' . $tournois['tournois'] . ' | ' . $tournois['nom_tour'] . '</p>';
                        } else {
                            echo '<p>Pas encore de tournoi joué</p>';
                        } 
                    ?>
                </div>
            </div>
        </div>
    </section>
    <section class="vue-ensemble">
        <!-- Ici on affiche le prochain tournoi -->
        <div class="fond">
            <div class="petit-rect"></div>
            <div class="tournoi">
                <div class="tournoi-titre">
                    <h4><i class="fa-solid fa-trophy" style="color: rgb(49, 49, 49);"></i> Prochain tournoi</h4>
                </div>
                <div class="tournoi-contenu">
                    <?php 
                        if (isset($prochainTournoi['tournois'])) {
                            // On formate la date pour qu'elle s'affiche "01-01-2000"
                            $dateDebutFormatee = date("d-m-Y", strtotime($prochainTournoi['date_debut']));
                            echo '<p>'. $prochainTournoi['tournois'] . ' | ' .$dateDebutFormatee.'</p>';
                        } else {
                            echo '<p>Pas encore de tournoi enregistrer</p>';
                        } 
                    ?>
                </div>
            </div>
        </div>
        <div class=bouton>
            <?php 
                if (isset($prochainTournoi['tournois'])) {?>
                    <div class="button-tournoi">
                        <a href="tournois_details.php?client=<?=$client?>" >Voir plus</a>
                    </div><span><div class="button-tournoi"  style='width:200px;'>
                        <a id="openModalTournoi">Ajouter un tournoi</a>
                    </div></span><?php
                }else {?>
                    <div class="button-tournoi"   style='width:200px;'>
                        <a id="openModalTournoi">Ajouter un tournoi</a>
                    </div><?php
                }
            ?>
        </div>
    </section>

    <?php 
    // Récupérer le nombre total de matchs jouées
    $stmt = $bdd->prepare('SELECT COUNT(*) AS total_matchs FROM matchs WHERE users_client = :user_client');
    $stmt->execute(array('user_client' => $client));
    $total_matchs = $stmt->fetch(PDO::FETCH_ASSOC)['total_matchs'];
    
    // Récupérer le nombre de parties gagnées et perdues
    $stmt = $bdd->prepare('SELECT COUNT(*) AS matchs_gagnes FROM matchs WHERE users_client = :user_client AND score_joueur1 > score_joueur2');
    $stmt->execute(array('user_client' => $client));
    $matchs_gagnes = $stmt->fetch(PDO::FETCH_ASSOC)['matchs_gagnes'];
    if($total_matchs != 0) {
        // Calculer le pourcentage de matchs gagnés dans cette catégorie
    $pourcentage_victoire = ($matchs_gagnes / $total_matchs) * 100;
    }

    // Récupérer le nombre total de parties jouées
    $stmt = $bdd->prepare('SELECT COUNT(*) AS total_parties_jouees FROM partie WHERE user_client = :user_client');
    $stmt->execute(array('user_client' => $client));
    $total_parties_jouees = $stmt->fetch(PDO::FETCH_ASSOC)['total_parties_jouees'];


    // Récupérer le nombre total de matchs joué en tournoi
    $stmt = $bdd->prepare('SELECT COUNT(*) AS total_matchs_tournoi FROM matchs WHERE users_client = :user_client AND categorie < 3');
    $stmt->execute(array('user_client' => $client));
    $total_matchs_tournoi = $stmt->fetch(PDO::FETCH_ASSOC)['total_matchs_tournoi'];

    // Récupérer le nombres de parties gagnées et perdues en tournois
    $stmt = $bdd->prepare('SELECT COUNT(*) AS matchs_tournoi_gagnes FROM matchs WHERE users_client = :user_client AND categorie < 3 AND score_joueur1 > score_joueur2');
    $stmt->execute(array('user_client' => $client));
    $matchs_tournoi_gagnes = $stmt->fetch(PDO::FETCH_ASSOC)['matchs_tournoi_gagnes'];
    if($total_matchs_tournoi != 0){
        // On calcule le pourcentage de matchs gagnés en victoire
        $pourcentage_victoire_tournoi = ($matchs_tournoi_gagnes / $total_matchs_tournoi) * 100;
    }


    $stmt = $bdd->prepare('SELECT COUNT(DISTINCT tournoi) AS total_tournois_joues FROM matchs WHERE users_client = :user_client');
$stmt->execute(array('user_client' => $client));
$total_tournois_joues = $stmt->fetch(PDO::FETCH_ASSOC)['total_tournois_joues'];


    // Récupérer le nombre de casse ferme effectuées avec la date de la partie correspondante
    $stmt = $bdd->prepare('SELECT COUNT(*) AS casse FROM partie WHERE casse_ferme = 1 AND user_client = :user_client');
    $stmt->execute(array('user_client' => $client));
    $cas_ferme = $stmt->fetch(PDO::FETCH_ASSOC)['casse'];

    // Récupérer le nombre de reprise ferme effectuées avec la date de la partie correspondante
    $stmt = $bdd->prepare('SELECT COUNT(*) AS reprise FROM partie WHERE reprise_ferme = 1 AND user_client = :user_client');
    $stmt->execute(array('user_client' => $client));
    $reprise_ferme = $stmt->fetch(PDO::FETCH_ASSOC)['reprise'];
    ?>

    <!-- SECTION BARRE DE PROGRESSION -->
<section class="stat">
    <div class="pourcentage">
        <div class="pourcentage-match">
            <?php 
            if($total_matchs != 0){
                ?>
            <h4>% de victoire en matchs</h4>
            <div class="progress" role="progressbar" aria-label="Success" aria-valuenow="<?=round($pourcentage_victoire,2)?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-success" style="width: <?=round($pourcentage_victoire,2)?>%"><?php echo round($pourcentage_victoire,2)?> %</div>
            </div><?php }else{
                ?>
                <h4>% de victoire en matchs</h4>
            <div class="progress" role="progressbar" aria-label="Success" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-success" style="width: 0%">0 %</div>
            </div><?php
            }?>
    </div>
    <div class="pourcentage-partie">
        <?php
        if($total_tournois_joues != 0){
            ?>
        <h4>% de victoire en tournois</h4>
            <div class="progress" role="progressbar" aria-label="Success" aria-valuenow="<?=round($pourcentage_victoire_tournoi,2)?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-success" style="width: <?=round($pourcentage_victoire_tournoi,2)?>%"><?php echo round($pourcentage_victoire_tournoi,2)?> %</div>
            </div><?php
        } else {
            ?>
            <h4>% de victoire en tournois</h4>
            <div class="progress" role="progressbar" aria-label="Success" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-success" style="width: 0%">0 %</div>
            </div><?php
        }?>
    </div>
</div>
    <div class="chiffres">
        <div class="tournois">
                        <?php if($total_tournois_joues != 0){
                ?><h2><?= $total_tournois_joues?></h2><?php
            } else {
                ?>
                <h2>0</h2><?php
            }?>
            <h4>Tournois joués</h4>
        </div>
        <div class="matchs">
            <?php if($total_matchs != 0){
                ?><h2><?= $total_matchs?></h2><?php
            } else {
                ?>
                <h2>0</h2><?php
            }?>
            <h4>Matchs joués</h4>
        </div>
        <div class="parties">
            <?php if($total_parties_jouees !=0){
                ?><h2><?= $total_parties_jouees?></h2><?php
            }else{
                ?>
                <h2>0</h2><?php
            }?>
            <h4>Parties jouées</h4>
        </div>
        <div class="casses">
            <?php if($total_matchs !=0 && $cas_ferme !=0){
                ?><h2><?= $cas_ferme ?></h2><?php 
            }else{
                ?>
                <h2>0</h2><?php
            }?>
            <h4>Casses fermes</h4>
        </div>
        <div class="reprises">
            <?php if($total_matchs !=0 && $reprise_ferme !=0){
                ?><h2><?= $reprise_ferme ?></h2><?php 
            }else{
                ?>
                <h2>0</h2><?php
            }?>
            <h4>Reprises fermes</h4>
        </div>
    </div>
</section>
<?php
// Récupérer le nombre de parties gagnées et calculer le pourcentage
    // $stmt = $bdd->prepare('SELECT COUNT(*) AS parties_gagnees FROM partie WHERE gagne = 1 AND user_client = :user_client');
    // $stmt->execute(array('user_client' => $client));
    // $parties_gagnees = $stmt->fetch(PDO::FETCH_ASSOC)['parties_gagnees'];
    // $pourcentage_gagnees = ($parties_gagnees / $total_parties) * 100;

    // Récupérer le nombre de parties jouées avec les jaunes et les rouges
    // $stmt = $bdd->prepare('SELECT COUNT(*) AS parties_jaunes FROM partie WHERE jaune = 1 AND user_client = :user_client');
    // $stmt->execute(array('user_client' => $client));
    // $parties_jaunes = $stmt->fetch(PDO::FETCH_ASSOC)['parties_jaunes'];

    // $parties_rouges = $total_parties - $parties_jaunes;

    // Récupérer le nombre de parties gagnées avec les jaunes et les rouges
    // $stmt = $bdd->prepare('SELECT COUNT(*) AS parties_gagnees_jaunes FROM partie WHERE jaune = 1 AND gagne = 1 AND user_client = :user_client');
    // $stmt->execute(array('user_client' => $client));
    // $parties_gagnees_jaunes = $stmt->fetch(PDO::FETCH_ASSOC)['parties_gagnees_jaunes'];

    // $parties_gagnees_rouges = $parties_gagnees - $parties_gagnees_jaunes;


    // Récupérer les infos du tournoi du user
// $sql = "SELECT DISTINCT c.tournois, tt.tour AS nom_tour, t.etape AS plus_haute_etape
//         FROM matchs m 
//         INNER JOIN tournois c ON m.tournoi = c.id
//         INNER JOIN (
//             SELECT tournoi, MAX(tour) as etape
//             FROM matchs
//             GROUP BY tournoi
//         ) t ON m.tournoi = t.tournoi AND m.tour = t.etape
//         INNER JOIN tournoi_tour tt ON m.tour = tt.id
//         WHERE c.user_client = :user_client";
// $stmt = $bdd->prepare($sql);
// $stmt->execute(array('user_client' => $client));
// $tournois = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ?>

<div id="myModalMatch" class="modalMatchAjout">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="ajout_match">
        <h2>Ajouter un match</h2>
        <form action="form/ajout_matchs.php" method="POST">
            <label for="joueur" hidden>Client</label>
            <input type="text" name="client" value="<?=$client?>" hidden>
            <label for="joueur">Nom du joueur</label>
            <input type="text" name="joueur" placeholder="Joueur">
            <label for="categorie">categorie</label>
            <select name="categorie" id="categorie">
                <option value="">--Sélectionner une catégorie--</option>
                <?php
                $categorieVisible = false; //Variable pour indiquer si les catégories sont affichées
                    while ($row = $categorie->fetch()) {
                        if (!empty($prochainTournoi['tournois']) &&
                            ($row['categorie'] == "Tournoi individuel" || $row['categorie'] == "Tournoi équipe")) {
                            echo "<option value='{$row['categorie']}'>{$row['categorie']}</option>";
                            $categorieVisible = true;
                        } elseif ($row['categorie'] == "Entraînement" || $row['categorie'] == "Amical") {
                            echo "<option value='{$row['categorie']}'>{$row['categorie']}</option>";
                        }
                    }
                    ?>
            </select>
            <label for="manches">Nombre de manches (1-10)</label>
            <input type="number" id="manches" name="manches" min="1" max="10">
            <label for="premiereCasse">Première casse </label>
            <input type="checkbox" name="premiereCasse">
            <button type="submit">Enregistrer</button>

        </form>
                    
    </div>
    </div>
</div>
<div id="myModalTournoi" class="modalMatch">
    <div class="modal-content">
        <span class="close">&times;</span>
                <!-- Ajout tournoi / tour -->
        <div class="ajout_tournoi">
    
    <h2>Ajouter un Tournoi</h2>
    <form action="form/ajouter_tournoi.php?client=<?=$client?>" method="post">
        <label for="nom_tournoi">Nom du Tournoi :</label>
        <input type="text" name="nom_tournoi" id="nom_tournoi" required>
        <label for="lieu">Lieu :</label>
        <input type="text" name="lieu" id="lieu" required>
        <label for="date_debut">Du</label>
        <input type="date" id="date_debut" name="date_debut" min="2023-09-01" max="2030-09-01">
        <label for="date_fin">Au</label>
        <input type="date" id="date_fin" name="date_fin" min="2023-09-01" max="2030-09-01">
        <button type="submit">Ajouter</button>
    </form>

    <h2 class="tour">Ajouter un Tour</h2>
    <form action="form/ajouter_tour.php?client=<?=$client?>" method="post">
        <label for="nom_tour">Nom du Tour :</label>
        <input type="text" name="nom_tour" id="nom_tour" required>
        </select>
        <button type="submit">Ajouter</button>
    </form>
        </div>
    </div>
</div>
<script>
// Fonction pour ouvrir la fenêtre modale
function openModalTournoi() {
    var modal = document.getElementById("myModalTournoi");
    modal.style.display = "block";
}

// Fonction pour fermer la fenêtre modale
function closeModalTournoi() {
    var modal = document.getElementById("myModalTournoi");
    modal.style.display = "none";
}

// Gérer le clic sur le bouton pour ouvrir la fenêtre modale
var openBtnTournoi = document.getElementById("openModalTournoi");
openBtnTournoi.addEventListener("click", openModalTournoi);

// Gérer le clic sur le bouton de fermeture de la fenêtre modale (span avec la classe "close")
var closeBtnTournoi = document.querySelector("#myModalTournoi .close"); // Sélectionner le span dans la fenêtre modale
closeBtnTournoi.addEventListener("click", closeModalTournoi);

// Gérer la fermeture de la fenêtre modale lorsqu'on clique en dehors de celle-ci
window.addEventListener("click", function(event) {
    var modal = document.getElementById("myModalTournoi");
    if (event.target === modal) {
        closeModalTournoi();
    }
});

</script>
<script>
// Fonction pour ouvrir la fenêtre modale
function openModalMatch() {
    var modalMatch = document.getElementById("myModalMatch"); // Utilisez "myModalMatch" ici
    modalMatch.style.display = "block"; // Utilisez "modalMatch" ici
}

// Fonction pour fermer la fenêtre modale
function closeModalMatch() {
    var modalMatch = document.getElementById("myModalMatch"); // Utilisez "myModalMatch" ici
    modalMatch.style.display = "none"; // Utilisez "modalMatch" ici
}

// Gérer le clic sur le bouton pour ouvrir la fenêtre modale
var openBtnMatch = document.getElementById("openModalMatch"); // Utilisez "openModalBtn" ici
openBtnMatch.addEventListener("click", openModalMatch);

// Gérer le clic sur le bouton de fermeture de la fenêtre modale
var closeBtnMatch = document.querySelector("#myModalMatch .close");
closeBtnMatch.addEventListener("click", closeModalMatch);

// Gérer la fermeture de la fenêtre modale lorsqu'on clique en dehors de celle-ci
window.addEventListener("click", function(event) {
    var modalMatch = document.getElementById("myModalMatch"); // Utilisez "myModalMatch" ici
    if (event.target === modalMatch) {
        closeModalMatch();
    }
});
</script>