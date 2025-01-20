<?php 
require_once('../config/config.php');

// if ($_SERVER["REQUEST_METHOD"] === "POST"){
//     //On vérifie le jeton CSRF (protection contre les attaques CSRF)
//     session_start();
//     if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
//         die("Erreur : Jeton CSRF invalide.");
//     }
// }

//On vérifie les entrées du formulaire
if(isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirm_password'])){
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    $confirmPassword = htmlspecialchars($_POST['confirm_password']);
    
    //On sélectionne les entrées de la table user dans la bdd
    $check = $bdd->prepare('SELECT nom, prenom, username, email, password FROM users WHERE email = ?');
    $check->execute(array($email));
    $data = $check->fetch();
    $row = $check->rowCount();

    if($row == 0){
        if(strlen($nom) <= 100){
            if(strlen($prenom) <= 100){
                if(strlen($username) <= 100){
                    if(strlen($email) <= 100){
                        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
                            if(preg_match("/^(.){6,20}$/", $password)){
                                if(preg_match("/[a-z][0-9]|[A-Z][0-9]/", $password)){
                                    if($password == $confirmPassword){
                                        //On hash le password
                                        $password = hash('sha256', $password);
                                        // on créer un id unique pour le user
                                        $client_id = uniqid();
        
                                        //On créer un dossier dans le dossier img/match unique pour le client
                                        $dossier_match = "../img/match/" . $client_id . "/";
                                        if (!file_exists($dossier_match)){
                                            mkdir($dossier_match, 0777, true);
                                        }
                                        //On créer un dossier dans le dossier img/match/img unique pour le client
                                        $dossier_img_profil = "../img/match/" . $client_id . "/profil/";
                                        if (!file_exists($dossier_img_profil)){
                                            mkdir($dossier_img_profil, 0777, true);
                                        }
                                        //on insert les données dans la base de données users
                                        $insert = $bdd->prepare('INSERT INTO users(nom, prenom, username, client, email, password, validate) VALUES(:nom, :prenom, :username, :client, :email, :password, :validate)');
                                        $insert->execute(array(
                                            'nom'=> $nom,
                                            'prenom' => $prenom,
                                            'username' => $username,
                                            'client' => $client_id,
                                            'email' => $email,
                                            'password' => $password,
                                            'validate' => 0
                                        ));
                                        // Envoie de l'email de réinitialisation
                                        // On accède maintenant aux variables d'environnements
                                        if(isset($_SERVER['SERVER_NAME'])){
                                            switch($_SERVER['SERVER_NAME']){
                                                case 'matchbillard':
                                                    $siteUrl = $_ENV['SITE_URL'];
                                                    break;
                                                case 'billard-score.fr':
                                                    $siteUrl = $_ENV['SITE_URL_ENV'];
                                            }
                                        }
                                        $dest= $email;
                                        $sujet = "Validation de votre inscription";
                                        $corps = "<html>
                                            <head>
                                                <style>
                                                        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        
                                                        *{
                                                            margin: 0;
                                                            padding: 0;
                                                            box-sizing: border-box;
                                                            font-family: 'Poppins', sans-serif;
                                                        }
        
                                                        body{
                                                        background-color: #0370b7;
                                                        width: 100vw;
                                                        height: 100vh;
                                                        display: flex;
                                                        flex-direction: column;
                                                        justify-content: center;
                                                        align-items: center;
                                                    }
                                                    .content{
                                                        display: flex;
                                                        flex-direction: column;
                                                        justify-content: center;
                                                        width: 50%;
                                                        background-color: white;
                                                        margin: auto;
                                                        padding: 15px;
                                                        border-radius: 10px;
                                                        border: 2px solid #dadada;
                                                        box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.15);
                                                        margin-top: 25px;
                                                    }
                                                    .content h1{
                                                        text-align: center;
                                                    }
                                                    .content p{
                                                        text-align: center;
                                                        padding-top: 15px;
                                                    }
                                                    .content p span{
                                                        text-decoration: none;
                                                        color: #0370b7;
                                                    }
                                                    .content a{
                                                        display: flex;
                                                        justify-content: center;
                                                        background-color: #0370b7;
                                                        border: 2px solid #024572;
                                                        width: 50%;
                                                        margin: auto;
                                                        padding: 5px;
                                                        border-radius: 5px;
                                                        color: white;
                                                        text-decoration: none;
                                                    }
                                                    .alerte{
                                                        font-size: 0.6em;
                                                    }
                                                    .alerte::before{
                                                        content: '*';
                                                    }
                                                </style>
                                            </head>
                                            <body>
                                                <div class='content'>
                                                    <h1>Merci pour votre inscription !</h1>
                                                    <div id='message'>
                                                        <p>".$username.", vous venez de vous inscrire sur <br> <span>$siteUrl</span></p>
                                                        <p>Sur ce site conçu pour les joueurs de billards, compétiteurs ou loisirs, vous pourrez enregistrer vos parties, obtenir un suivi de vos résultats et de votre amélioration.</p>
                                                        <p>Pour valider votre compte, veuillez suivre ce lien</p><br>
                                                        <a href='$siteUrl/index.php?client=$client_id&valid=1'>Valider mon compte</a>
                                                    </div>
                                                    <p class='alerte'>Ce message est envoyé automatiquement, veuillez ne pas y répondre</p>
                                                </div>
                                            </body>
                                            </html>";
                                            $headers[] = 'MIME-Version : 1.0';
                                            $headers[] = 'Content-type: text/html; charset=UTF-8';
                                            $headers[] = 'From: admin@billardscore.fr';
                                            mail($dest, $sujet, $corps, implode("\r\n", $headers));
                                        $joueur = $bdd->prepare('INSERT INTO joueur(user_client, niveau, score, bronze, argent, gold, exo_1, exo_2, exo_3, exo_4, exo_5, exo_6, exo_7, exo_8, exo_9, exo_10, exo_11, exo_12, exo_13, exo_14, exo_15, exo_16, exo_17, exo_18, exo_19, exo_20, exo_21, exo_22, exo_23, exo_24, exo_25, exo_26, exo_27, exo_28, exo_29, exo_30, exo_31, exo_32, exo_33, exo_34, exo_35, exo_36, exo_37, exo_38, exo_39, exo_40, exo_41, exo_42, exo_43, exo_44, exo_45, exo_46, exo_47, exo_48, exo_49, exo_50) VALUES (:user_client, :niveau, :score, :bronze, :argent, :gold, :exo_1, :exo_2, :exo_3, :exo_4, :exo_5, :exo_6, :exo_7, :exo_8, :exo_9, :exo_10, :exo_11, :exo_12, :exo_13, :exo_14, :exo_15, :exo_16, :exo_17, :exo_18, :exo_19, :exo_20, :exo_21, :exo_22, :exo_23, :exo_24, :exo_25, :exo_26, :exo_27, :exo_28, :exo_29, :exo_30, :exo_31, :exo_32, :exo_33, :exo_34, :exo_35, :exo_36, :exo_37, :exo_38, :exo_39, :exo_40, :exo_41, :exo_42, :exo_43, :exo_44, :exo_45, :exo_46, :exo_47, :exo_48, :exo_49, :exo_50)');
                                        $joueur->execute(array(
                                            'user_client'=>$client_id,
                                            'niveau'=>1,
                                            'score'=>0,
                                            'bronze'=>0,
                                            'argent'=>0,
                                            'gold'=>0,
                                            'exo_1'=>0,
                                            'exo_2'=>0,
                                            'exo_3'=>0,
                                            'exo_4'=>0,
                                            'exo_5'=>0,
                                            'exo_6'=>0,
                                            'exo_7'=>0,
                                            'exo_8'=>0,
                                            'exo_9'=>0,
                                            'exo_10'=>0,
                                            'exo_11'=>0,
                                            'exo_12'=>0,
                                            'exo_13'=>0,
                                            'exo_14'=>0,
                                            'exo_15'=>0,
                                            'exo_16'=>0,
                                            'exo_17'=>0,
                                            'exo_18'=>0,
                                            'exo_19'=>0,
                                            'exo_20'=>0,
                                            'exo_21'=>0,
                                            'exo_22'=>0,
                                            'exo_23'=>0,
                                            'exo_24'=>0,
                                            'exo_25'=>0,
                                            'exo_26'=>0,
                                            'exo_27'=>0,
                                            'exo_28'=>0,
                                            'exo_29'=>0,
                                            'exo_30'=>0,
                                            'exo_31'=>0,
                                            'exo_32'=>0,
                                            'exo_33'=>0,
                                            'exo_34'=>0,
                                            'exo_35'=>0,
                                            'exo_36'=>0,
                                            'exo_37'=>0,
                                            'exo_38'=>0,
                                            'exo_39'=>0,
                                            'exo_40'=>0,
                                            'exo_41'=>0,
                                            'exo_42'=>0,
                                            'exo_43'=>0,
                                            'exo_44'=>0,
                                            'exo_45'=>0,
                                            'exo_46'=>0,
                                            'exo_47'=>0,
                                            'exo_48'=>0,
                                            'exo_49'=>0,
                                            'exo_50'=>0
                                        ));
                                        header('Location:../signup.php?reg_err=success');
                                        die();
                                    }else header('Location:../signup.php?reg_err=password'); die();
                                }else header('Location:../signup.php?reg_err=carPass'); die();
                            }else header('Location:../signup.php?reg_err=lenPass'); die();
                        }else header('Location:../signup.php?reg_err=email'); die();
                    }else header('Location:../signup.php?reg_err=lenEmail'); die();
                }else header('Location:../signup.php?reg_err=lenUser'); die();
            }else header('Location:../signup.php?reg_err=lenSurname'); die();
        }else header('Location:../signup.php?reg_err=lenName'); die();
    }else header('Location:../signup.php?reg_err=already'); die();
}
?>