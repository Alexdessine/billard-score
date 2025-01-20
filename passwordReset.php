<?php
require_once('config/config.php');

$verif = htmlspecialchars($_GET['verif']);


?>

<!DOCTYPE html>
<html>
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
    <title>BillardScore - Mot de passe oublié</title>
</head>
<body class="bodyFond">

<img src="img/billard-score.png" alt="" class="billard_score_img">
    

    <!-- Formulaire d'inscription -->
    <?php
    // On sélectionne l'user dans la bdd correspondant au reset_token
$check = $bdd->prepare('SELECT * FROM users WHERE reset_token = ?');
$check->execute(array($verif));
$data = $check->fetch();
$row = $check->rowCount();

if($row == 1){
    ?>
    <div id="inscription">
        <h1>Modification du mot de passe</h1>
        <p style="margin-bottom: 40px; color:black;">Bonjour <?= $data['username']?></p>
    <form action="form/traitement_password.php?client=<?= $data['client']?>" method="POST" >
        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Nouveau mot de passe"><br>
        <input type="password" class="form-control" id="repeat_password"  name="repeat_password" placeholder="Retaper votre mot de passe"><br>
        <input type="submit" class="btn btn-primary" value="Envoyer">
    </form>
    </div>
<?php
}else{
    header('Location:index.php');
    die();
}
?>
</body>
</html>
