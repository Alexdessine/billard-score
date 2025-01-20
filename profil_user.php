<?php 
session_start();
if($_SESSION["autoriser"]!="oui" || !isset($_GET['client'])){
    header("location:login.php");
    die();
}
require_once('config/config.php');

// Récupérer l'ID de l'utilisateur connecté
$client = htmlspecialchars($_GET['client']);

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
    <title>Profil <?=$client?></title>
</head>
<body>
    <div class="infos_profil_admin">
        <a href="login.php"><i class="fa-solid fa-power-off" style="color: #0370b7;"></i></a>
        <a href="profil_user?client=<?=$client?>"><i class="fa-solid fa-user" style="color: #0370b7;"></i></a>
        <a href="index.php?client=<?=$client ?>"><i class="fa-solid fa-memo-pad" style="color: #0370b7;"></i></a>
    </div>
    <section class="tableaux tableau_profil">
    <h1>Mon Profil</h1>
    
    <h2>Ajouter un Tournoi</h2>
    <form action="form/ajouter_tournoi.php?client=<?=$client?>" method="post">
        <label for="nom_tournoi">Nom du Tournoi :</label>
        <input type="text" name="nom_tournoi" id="nom_tournoi" required>
        <label for="lieu">Lieu :</label>
        <input type="text" name="lieu" id="lieu" required>
        <button type="submit">Ajouter</button>
    </form>

    <h2>Ajouter un Tour</h2>
    <form action="form/ajouter_tour.php?client=<?=$client?>" method="post">
        <label for="nom_tour">Nom du Tour :</label>
        <input type="text" name="nom_tour" id="nom_tour" required>
        </select>
        <button type="submit">Ajouter</button>
    </form>
    </section>
</body>
</html>




