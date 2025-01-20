<?php 
session_start();
if($_SESSION["autoriser"]!="oui" || !isset($_GET['client'])){
    header("location:login.php");
    die();
}
require_once('config/config.php');

// Récupérer l'ID de l'utilisateur connecté
$user_client = htmlspecialchars($_GET['client']);



// Récupérer le username correspondant à l'ID de l'utilisateur
$stmt = $bdd->prepare('SELECT * FROM users WHERE client = :user_client');
$stmt->execute(array('user_client' => $user_client));
$user = $stmt->fetch(PDO::FETCH_ASSOC);


$client = $user['client'];

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" href="img/avatar.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <title>BillardScore - Profil</title>
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
        <!-- Affichage bouton modification image profil -->
        <div class="ajout-img-profil">
            <i class="fa-solid fa-person-walking-arrow-right" style="color: #ffffff;"></i>
        </div>
    </section>
    <!-- Affichage nom et prénom de l'user -->
    <div class="infos-profil">
        <p><span><?= $user['validate'] == 1 ? '<i class="fa-solid fa-badge-check" style="color: #59bd4c;"></i>' : '' ?></span> <?php echo $user['prenom'] . ' ' .$user['nom']?></p>
    </div>
    
    <section class="container accueil">
                <a href="user-admin.php?client=<?=$client?>"><i class="fa-solid fa-house"></i><br>Retour accueil</a>
                
    </section>

<section class="container profil_modif">
        <?php
if(isset($_GET['reg_err'])) {
    $err = htmlspecialchars($_GET['reg_err']);

    switch($err) {
        case 'limit':
            ?>
                    <div class="alert alert-danger" style="width: 450px; margin:auto; text-align:center;">
                        <p><strong>Erreur</strong> La taille du fichier dépasse la limite de 5 Mo.</p>
                    </div>
                    <?php
            break;
    }
}
                    ?>
                    <div class="information_user m-auto text-center mt-3 mb-5">
                        <p><strong>Compte client : </strong><?=$user['client']?></p>
                        <p><strong>Nom : </strong><?=$user['nom']?></p>
                        <p><strong>Prénom : </strong><?=$user['prenom']?></p>
                        <p><strong>Email : </strong><?=$user['email']?></p>
                        <p><strong>Compte créé le : </strong><?=$user['created_at']?></p>
                    </div>
<h5 style="text-align:center; margin-top:10px;">Mettre à jour mes informations</h5>
<form class="row g-3" action="form/traitement_profil.php?client=<?= $user['client']?>" method="POST" enctype="multipart/form-data">
  <div class="col-md-6">
    <label for="new_password" class="form-label">Nouveau mot de passe</label>
    <input type="password" class="form-control" id="new_password" name="new_password">
  </div>
  <div class="col-md-6">
    <label for="repeat_password" class="form-label">Retapez votre mot de passe</label>
    <input type="password" class="form-control" id="repeat_password"  name="repeat_password">
  </div>
  <div class="mb-3">
  <div class="fichier">
            <label for="img_profil">Modifier photo de profil (JPEG uniquement, max 5Mo) :</label>
            <input type="file" name="img_profil" id="img_profil" accept="image/jpeg" class="button_fichier" maxlength="5242880" onchange="displayFileSize()">
            <p id="file_size_limit"></p>
        </div>
</div>

  <div class="col-md-12 text-center" style="margin:auto; width:50%;">
    <button type="submit" class="btn btn-primary">Valider</button>
  </div>
</form>

<div class="col-12 text-center mt-5">
    <a href="#" class="btn btn-danger" id="supprimer-compte">Supprimer mon compte</a>
    <p class="text-danger">⚠️ Toute suppression est définitive ⚠️</p>
</div>

</section>

<script>
    function displayFileSize(){
        var input = document.getElementById("formFile");
        var fileSize = input.files[0].size; //Taille du fichier en octets
        var maxSize = 5 * 1024 * 1024; //5Mo en octets

        var fileSizeDisplay = (fileSize / (1024 * 1024)).toFixed(2) + "Mo";
        var fileSizelimitDisplay = (maxSize / (1024 * 1024)).toFixed(2) + "Mo";

        var fileLimitElement = document.getElementById("file_size_limit");
        fileLimitElement.innerHTML = "Taille du fichier : " + fileSizeDisplay + " / Limite : " + fileSizelimitDisplay;

        if (fileSize > maxSize){
            // Le fichier dépasse la  limite, vous pouvez ajouter ici des styles CSS pour indiquer le dépassement de la limite
            fileLimitElement.style.color = "red";
        } else {
            fileLimitElement.style.color = "black";
        }
    }

</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const supprimerCompteButton = document.getElementById("supprimer-compte");

    if (supprimerCompteButton) {
        supprimerCompteButton.addEventListener("click", function (e) {
            e.preventDefault();
            
            const confirmation = confirm("Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est définitive.");

            if (confirmation) {
                // Redirigez l'utilisateur vers la page de suppression du compte
                window.location.href = "form/supprimer_compte.php?client=".$client; // Remplacez par l'URL réelle de la page de suppression
            }
        });
    }
});
</script>
