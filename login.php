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
    <title>BillardScore - Connexion</title>
</head>
<body class="bodyFond">

<img src="img/billard-score.png" alt="" class="billard_score_img">
    

    <!-- Formulaire d'inscription -->
    <div id="inscription">
        <h1>Se connecter</h1>
        <?php
        if(isset($_GET['reg_err'])){
            $err = htmlspecialchars($_GET['reg_err']);

            switch($err){
                case 'error':
                    ?>
                    <div class="alert alert-danger">
                        <p><strong>Erreur</strong> identifiants incorrects</p>
                    </div>
                    <?php
                    break;
                case 'locked':
                // Afficher un message si le compte est verrouillé en raison de tentatives échouées
                $remainingTime = htmlspecialchars($_GET['time']);
                ?>
                <div class="alert alert-danger">
                    <p><strong>Erreur</strong> Compte verrouillé en raison de tentatives échouées. Réessayez dans <?php echo $remainingTime; ?> secondes.</p>
                </div>
                <?php
                break;
            }
        }
        ?>
        <form action="form/traitement_connexion.php" method="post">
            <!-- Vos champs de connexion ici -->
            
            <input type="text" class="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><br>
            <input type="text" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Mot de passe" required><br>
            <input type="submit" value="Connexion">
        </form>
        <p style="margin-bottom: 10px;"><a href="forget_password.php">Mot de passe oublié ?</a></p>
        <p>Pas encore de compte? <a href="signup.php">S'inscrire</a></p>
    </div>
</body>
</html>
