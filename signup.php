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
    <link rel="icon" href="img/avatar.ico">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>BillardScore - Inscription</title>
</head>
<body class="bodyFond">
    <img src="img/billard-score.png" alt="" class="billard_score_img">

    <!-- Formulaire d'inscription -->
    <div id="inscription">
        <h1 class="inscription">Inscription</h1>
        <?php
        if(isset($_GET['reg_err'])){
            $err = htmlspecialchars($_GET['reg_err']);

            switch($err){
                case 'already':
                    ?>
                    <div class="alert alert-warning">
                        <p><strong>Erreur</strong> ce compte existe déjà</p>
                    </div>
                    <?php
                    break;
                case 'lenUser':
                    ?>
                    <div class="alert alert-dangers">
                        <p><strong>Erreur</strong> utilisateur</p>
                    </div>
                    <?php
                    break;
                case 'lenEmail':
                    ?>
                    <div class="alert alert-dangers">
                        <p><strong>Erreur</strong> email non conforme</p>
                    </div>
                    <?php
                    break;
                case 'email':
                    ?>
                    <div class="alert alert-dangers">
                        <p><strong>Erreur</strong> email non conforme</p>
                    </div>
                    <?php
                    break;
                case 'lenPass':
                    ?>
                    <div class="alert alert-dangers">
                        <p><strong>Erreur</strong> le mot de passe doit comporter entre 6 et 20 caractère</p>
                    </div>
                    <?php
                    break;
                case 'carPass':
                    ?>
                    <div class="alert alert-dangers">
                        <p><strong>Erreur</strong> le mot de passe doit comporter uniquement des caractère alphanumérique</p>
                    </div>
                    <?php
                    break;
                case 'password':
                    ?>
                    <div class="alert alert-dangers">
                        <p><strong>Erreur</strong> les mots de passe ne correspondent pas</p>
                    </div>
                    <?php
                    break;
                case 'success':
                    ?>
                    <div class="alert alert-success">
                        <p><strong>Succès</strong> votre compte est bien créer<br>
                        <a href="login.php">Vous pouvez maintenant vous connecter</a></p>
                    </div>
                    <?php
                    break;

            }
        }
        ?>
        <form action="form/traitement_inscription.php" method="post">
            <!-- Vos champs d'inscription ici -->
            <!-- <input type="text" name="csrf_token" value="<?php echo $csrf_token; ?>"><br> -->
            <input type="text" name="nom" placeholder="Nom" required><br>
            <input type="text" name="prenom" placeholder="Prénom" required><br>
            <input type="text" name="username" placeholder="Nom d'utilisateur" required><br>
            <input type="email" name="email" placeholder="Adresse e-mail" required><br>
            <input type="password" name="password" placeholder="Mot de passe" required><br>
            <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required><br>
            <input type="submit" value="S'inscrire">
        </form>
        <p>Déjà inscrit? <a href="login.php">Se connecter</a></p>
    </div>
</body>
</html>