<?php 
session_start();
if($_SESSION["autoriser"]!="oui" || !isset($_GET['client'])){
    header("location:login.php");
    die();
}
require_once('../config/config.php');

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
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" href="../img/avatar.ico">
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
            <a href="../profil.php?client=<?=$user['client']?>"><i class="fa-solid fa-pen" style="color: #ffffff;"></i></a>
        </div>
                <!-- Affichage photo de profil -->
        <div class="fond-img-profil">
            <?php if(isset($user['profil_img'])){
                ?><img src="../<?=$user['profil_img']?>" alt=""><?php
            }else{
                ?>
                <img src="../img/profil.jpg" alt=""><?php
            }?>
        </div>
        <!-- Affichage bouton pour se déconnecter -->
        <div class="ajout-img-profil">
            <a href="../deconnexion.php"><i class="fa-solid fa-person-walking-arrow-right" style="color: #ffffff;"></i></a>
        </div>
    </section>
    <!-- Affichage nom et prénom de l'user -->
    <div class="infos-profil col-sm-12 col-md-12">
        <p><span><i class="fa-solid fa-badge-check" style="color: #59bd4c;"></i> Alexandre Bourlier</span></p>
        <div class="container accueil">
            <a href="../user-admin.php?client=<?=$client?>"><i class="fa-solid fa-house"></i><br>Retour accueil</a>
        </div>
        <div class="classement_exercice">
            <?php 
                $i = $joueur['niveau'];
                if($joueur['score']>=$niveau[0]['niveau_'.$i]){
                    $i++;
                    $stmt= $bdd->prepare('UPDATE joueur SET niveau = ? WHERE user_client = ?');
                    $stmt->execute(array($i, $client));
                    echo '<img src="../img/star/star' . $i . '.svg " alt="" width="48px">';
                }else{
                    echo '<img src="../img/star/star' . $i . '.svg " alt="" width="48px">';
                }

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
<section class="exercice_section" style="margin-top:65px;">
    <div class="container text-center">
        <div class="row">
            <div class="col-sm-12 col-md-4">
                <?php 
                if($joueur['niveau'] >= 2){
                    ?><div>
                            <a href="niveau1/exo1.php?client=<?=$client?>" class="exercice_content green-300">Niveau 1</a>
                        </div>
                        <?php
                }else{
                ?>
                    <div>
                        <a href="niveau1/exo1.php?client=<?=$client?>" class="exercice_content">Niveau 1</a>
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="col-sm-12 col-md-4">
                <?php 
                if($joueur['niveau'] >= 3){
                    ?><div>
                            <a href="niveau2/exo1.php?client=<?=$client?>" class="exercice_content green-300">Niveau 2</a>
                        </div>
                        <?php
                }elseif ($joueur['score']<190){
                ?>
                    <div>
                         <a href="niveau2/exo1.php?client=<?=$client?>" class="exercice_content disabled"><i class="fa-solid fa-lock" style="color: #212529;"></i> Niveau 2</a>
                    </div><?php
                } else {
                     ?>
                    <div>
                         <a href="niveau2/exo1.php?client=<?=$client?>" class="exercice_content">Niveau 2</a>
                    </div><?php
                }
                ?>
            </div>
            <div class="col-sm-12 col-md-4">
                <?php 
                if($joueur['niveau'] >= 4){
                    ?><div>
                            <a href="niveau3/exo1.php?client=<?=$client?>" class="exercice_content green-300">Niveau 3</a>
                        </div>
                        <?php
                }elseif($joueur['score'] < 450){
                ?>
                    <div>
                         <a href="niveau3/exo1.php?client=<?=$client?>" class="exercice_content disabled"><i class="fa-solid fa-lock" style="color: #212529;"></i> Niveau 3</a>
                    </div><?php
                } else{
                ?>
                    <div>
                        <a href="niveau3/exo1.php?client=<?=$client?>" class="exercice_content">Niveau 3</a>
                    </div>
                    <?php
                }
                ?>
            </div>
            </div>
        <div class="row">
            <div class="col-sm-12 col-md-4">
                <?php 
                if($joueur['niveau'] >= 5){
                    ?><div>
                            <a href="niveau4/exo1.php?client=<?=$client?>" class="exercice_content green-300">Niveau 4</a>
                        </div>
                        <?php
                }elseif($joueur['score'] < 715){
                ?>
                    <div>
                         <a href="niveau4/exo1.php?client=<?=$client?>" class="exercice_content disabled"><i class="fa-solid fa-lock" style="color: #212529;"></i> Niveau 4</a>
                    </div><?php
                }else{
                    ?>
                    <div>
                         <a href="niveau4/exo1.php?client=<?=$client?>" class="exercice_content">Niveau 4</a>
                    </div><?php
                }
                ?>
            </div>
            <div class="col-sm-12 col-md-4">
                <?php 
                if($joueur['niveau'] >= 6){
                    ?><div>
                            <a href="niveau5/exo1.php?client=<?=$client?>" class="exercice_content green-300">Niveau 5</a>
                        </div>
                        <?php
                }elseif($joueur['score'] < 950){
                ?>
                    <div>
                        <a href="niveau5/exo1.php?client=<?=$client?>" class="exercice_content disabled"><i class="fa-solid fa-lock" style="color: #212529;"></i> Niveau 5</a>
                    </div><?php
                }else{
                ?>
                <div>
                    <a href="niveau5/exo1.php?client=<?=$client?>" class="exercice_content">Niveau 5</a>
                </div><?php
                }
                ?>
            </div>
            <div class="col-sm-12 col-md-4">
                <?php 
                if($joueur['niveau'] >= 7){
                    ?><div>
                            <a href="niveau6/exo1.php?client=<?=$client?>" class="exercice_content green-300">Niveau 6</a>
                        </div>
                        <?php
                }elseif($joueur['score']<1250){
                ?>
                    <div>
                        <a href="niveau6/exo1.php?client=<?=$client?>" class="exercice_content disabled"><i class="fa-solid fa-lock" style="color: #212529;"></i> Niveau 6</a>
                    </div><?php
                } else{
                ?>
                <div>
                    <a href="niveau6/exo1.php?client=<?=$client?>" class="exercice_content">Niveau 6</a>
                </div>
                    <?php
                }
                ?>
            </div>
            </div>
        <div class="row">
            <div class="col-sm-12 col-md-4">
                <?php 
                if($joueur['niveau'] >= 8){
                    ?><div>
                            <a href="niveau7/exo1.php?client=<?=$client?>" class="exercice_content green-300">Niveau 7</a>
                        </div>
                        <?php
                }elseif($joueur['score']<1500){
                ?>
                    <div>
                        <a href="niveau7/exo1.php?client=<?=$client?>" class="exercice_content disabled"><i class="fa-solid fa-lock" style="color: #212529;"></i> Niveau 7</a>
                    </div><?php
                }else{
                ?>
                <div>
                    <a href="niveau7/exo1.php?client=<?=$client?>" class="exercice_content">Niveau 7</a>
                </div>
                    <?php
                }
                    ?>
            </div>
            <div class="col-sm-12 col-md-4">
                <?php 
                if($joueur['niveau'] >= 9){
                    ?><div>
                            <a href="niveau8/exo1.php?client=<?=$client?>" class="exercice_content green-300">Niveau 8</a>
                        </div>
                        <?php
                }elseif($joueur['score'] < 1750){
                ?>
                    <div>
                         <a href="niveau8/exo1.php?client=<?=$client?>" class="exercice_content disabled"><i class="fa-solid fa-lock" style="color: #212529;"></i> Niveau 8</a>
                    </div><?php
                }else{
                    ?>
                    <div>
                         <a href="niveau8/exo1.php?client=<?=$client?>" class="exercice_content">Niveau 8</a>
                </div><?php
                }
                ?>
            </div>
            <div class="col-sm-12 col-md-4">
                <?php 
                if($joueur['niveau'] >= 10){
                    ?><div>
                            <a href="niveau9/exo1.php?client=<?=$client?>" class="exercice_content green-300">Niveau 9</a>
                        </div>
                        <?php
                }elseif($joueur['score'] < 2000){
                ?>
                    <div>
                        <a href="niveau9/exo1.php?client=<?=$client?>" class="exercice_content disabled"><i class="fa-solid fa-lock" style="color: #212529;"></i> Niveau 9</a>
                    </div><?php
                }else{
                ?>
                <div>
                    <a href="niveau9/exo1.php?client=<?=$client?>" class="exercice_content">Niveau 9</a>
                </div>
                <?php
                }
                ?>
        </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-4 offset-md-4">
                <div>
                    <?php
                    if($joueur['score'] < 2250){
                        ?>
                        <div>
                            <a href="niveau10/exo1.php?client=<?=$client?>" class="exercice_content disabled"><i class="fa-solid fa-lock" style="color: #212529;"></i> Niveau 10</a>
                        </div>
                        <?php
                    }else{
                        ?>
                        <div>
                            <a href="niveau10/exo1.php?client=<?=$client?>" class="exercice_content">Niveau 10</a>
                        </div>
                         <?php
                    }
                    ?>
                </div>
            </div>
        </div>
</section>
<section class="classement_billard">
    <div class="container text-center">
        <h4>Prêt à passer les diplômes ?</h4>
        <div class="row">
            <div class="col-sm-12 col-md-4 exercice_content">
                <div class="bronze">🥉 <br> Billard de bronze</div>
            </div>
            <div class="col-sm-12 col-md-4 exercice_content">
                <div class="argent">🥈 <br> Billard d'argent</div>
            </div>
            <div class="col-sm-12 col-md-4 exercice_content">
                <div class="or">🥇<br> Billard d'or</div>
            </div>
        </div>
    </div>
</section>