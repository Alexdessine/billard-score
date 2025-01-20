<?php
require_once('config/config.php');
session_start();
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Récupérer les clés reCAPTCHA à partir des variables d'environnement
$siteKey = $_ENV['CLE_RECAPTCHA'];


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
    <div id="inscription">
        <h1>Mot de passe oublié</h1>
        <?php
        if(isset($_GET['get_err'])){
            $err = htmlspecialchars($_GET['get_err']);

            switch($err){
                case 'succes':
                    ?>
                    <div class="alert alert-success">
                        <p style="margin-top: 20px; margin-bottom:0px;"><strong>Si un compte existe un mail vous est envoyé</strong></p>
                    </div>
                    <?php
                    break;
            }
        }
        ?>
        <form action="form/envoiePassword.php" method="post">
            <!-- Vos champs de connexion ici -->
            
            <input type="text" class="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><br>
            <input type="text" name="email" placeholder="Email" required><br>
            <input type="submit" value="Envoyer mon code">
        </form>
    </div>
</body>
</html>
