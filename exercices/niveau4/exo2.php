<?php 
session_start();
if($_SESSION["autoriser"]!="oui" || !isset($_GET['client'])){
    header("location:login.php");
    die();
}
require_once('../../config/config.php');

// Récupérer l'ID de l'utilisateur connecté
$client = htmlspecialchars($_GET['client']);

// Récupérer le username correspondant à l'ID de l'utilisateur
$stmt = $bdd->prepare('SELECT * FROM users WHERE client = :user_client');
$stmt->execute(array('user_client' => $client));
$user = $stmt->fetch(PDO::FETCH_ASSOC);


// Récupérer les données des exercices de l'utilisateur
$stmt = $bdd->prepare('SELECT * FROM joueur WHERE user_client = :user_client');
$stmt->execute(array('user_client' => $client));
$joueur = $stmt->fetch(PDO::FETCH_ASSOC);

// On récupère les niveaux
$stmt = $bdd->prepare('SELECT * FROM niveau');
$stmt->execute();
$niveau = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="icon" href="../../img/avatar.ico">
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
            <a href="../../profil.php?client=<?=$user['client']?>"><i class="fa-solid fa-pen" style="color: #ffffff;"></i></a>
        </div>
                <!-- Affichage photo de profil -->
        <div class="fond-img-profil">
            <?php if(isset($user['profil_img'])){
                ?><img src="../../<?=$user['profil_img']?>" alt=""><?php
            }else{
                ?>
                <img src="../../img/profil.jpg" alt=""><?php
            }?>
        </div>
        <!-- Affichage bouton pour se déconnecter -->
        <div class="ajout-img-profil">
            <a href="../../deconnexion.php"><i class="fa-solid fa-person-walking-arrow-right" style="color: #ffffff;"></i></a>
        </div>
    </section>
    <!-- Affichage nom et prénom de l'user -->
    <div class="infos-profil col-sm-12 col-md-12">
        <p><span><i class="fa-solid fa-badge-check" style="color: #59bd4c;"></i> Alexandre Bourlier</span></p>
        <div class="container accueil">
            <a href="../../user-admin.php?client=<?=$client?>"><i class="fa-solid fa-house"></i><br>Retour accueil</a>
        </div>
        <div class="classement_exercice">
            <?php 
                $i = $joueur['niveau'];
                echo '<img src="../../img/star/star' . $i . '.svg " alt="" width="48px">';

            ?>
                    <!-- <div class="progress-bar bg-success" style="width: <?= round($niveauJoueur, 2)?>%"><?= $joueur['score']?>/<?=$niveau[0]['niveau_'.$i]?></div> -->
                    <?php
                    if($joueur['score'] != 0){
                        $niveauJoueur = ($joueur['score'] / $niveau[0]['niveau_'.$i]) * 100;
                        $progressPercentage = round($niveauJoueur, 2);
                        $textColor = $progressPercentage <= 29 ? 'text-black' : ''; // Vérifiez si le pourcentage est inférieur à 25
                        $overflowStyle = $progressPercentage <= 29 ? 'visible' : 'hidden'; // Gérez l'overflow
    
                        ?>
                          <div class="progress" role="progressbar" aria-label="Success" aria-valuenow="<?= round($niveauJoueur,2)?>" aria-valuemin="0" aria-valuemax="100"><?php
                                echo '<div class="progress-bar bg-success ' . $textColor . '" style="width: ' . $progressPercentage . '%; overflow: ' . $overflowStyle . '">';
    
                        // Ajoutez la classe font-weight-bold si le pourcentage est inférieur à 25
                        if ($progressPercentage <= 29) {
                            echo '<span class="font-weight-bold">' . $joueur['score'] . '/' . $niveau[0]['niveau_' . $i] . '</span>';
                        } else {
                            echo $joueur['score'] . '/' . $niveau[0]['niveau_' . $i];
                        }
    
                        ?></div>
                        </div><?php
                    } else {
                        ?>
                        <div class="progress" role="progressbar" aria-label="Success" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-success"  style="width:0%">0/250</div>
                    </div>
                    <?php } ?>
            </div>
        </div>
        <?php 
            if($joueur['bronze'] == 1){
                if($joueur['argent'] == 1){
                    if($joueur['gold'] == 1){
                        echo'<p style="font-size:1.5em;">🥇🥈🥉</p>';
                    }else echo '<p style="font-size:1.5em;">🥈🥉</p>';
                }else echo '<p style="font-size:1.5em;">🥉</p>';
            }else echo '<p><br></p>';
        ?>
    </div>
</section>
<section  style="margin-top:65px;">
    <div class="container text-center">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-xl-8">
                <div>
                    <img src="../../img/exo_niv_4/exo_2.jpg" alt="" class="exoImage">
                </div>
            </div>
            <div class="col-sm-12 col-md-12 col-xl-4">
                <h5>Instructions</h5>
                <p>Réaliser 3 coups sans toucher les billes rouges<br>
                La bille blanche doit s'arrêter dans les zones claires<br>
                <strong>Score :</strong> 1 point par bille stoppée dans la zone <br>
                <strong>3 Points maximums</strong><br>
                Réussissez cet exercice à 5 reprises <br>
                Score maximum de l'exercice : <strong>15 points</strong>
                <br>
                <br>
                <strong>Objectif</strong><br>
                <?= $joueur['exo_17']?>/15
                </p>
                <?php 
                if($joueur['exo_17'] == 15){
                    ?>
                    <h4><i class="fa-solid fa-face-smile-beam" style="color: #0370b7; font-size:2em;"></i></h4>
                    <p>Félicitations objectif atteint ! </p>
                    <?php
                }else{
                ?>
                <form action="/exercices/niveau4/traitement/exo2traitement.php?client=<?=$client?>" class="form-control" method="POST">
                    <label for="">Essai #1</label>
                    <input type="number" name="essai1" min="0" max="3"><br>
                    <label for="">Essai #2</label>
                    <input type="number" name="essai2" min="0" max="3"><br>
                    <label for="">Essai #3</label>
                    <input type="number" name="essai3" min="0" max="3"><br>
                    <label for="">Essai #4</label>
                    <input type="number" name="essai4" min="0" max="3"><br>
                    <label for="">Essai #5</label>
                    <input type="number" name="essai5" min="0" max="3"><br><br>
                    <button type="submit" class="btn btn-primary">Valider</button>
        </form><?php
                }?>
            </div>
        </div>
    </div>
</section>
<section class="bouton_exercice">
    <div class="precedent">
        <a href="exo1.php?client=<?=$client?>">Exercice précédent</a>
    </div>
    <div class="precedent">
        <a href="exo3.php?client=<?=$client?>">Exercice suivant</a>
    </div>
</section>
</body>

