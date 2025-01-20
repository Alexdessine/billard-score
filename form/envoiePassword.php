<?php 
require_once('../config/config.php');
include('../password/aleatoire.php');




if ($_SERVER["REQUEST_METHOD"] === "POST"){
    //On vérifie le jeton CSRF (protection contre les attaques CSRF)
    session_start();
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
        die("Erreur : Jeton CSRF invalide.");
    }
}

//On vérifie les entrées du formulaire
if(isset($_POST['email'])){
    $email = htmlspecialchars($_POST['email']);
    $aleatoire = GenVerif(6);
    
    //On sélectionne les entrées de la table user dans la bdd
    $check = $bdd->prepare('SELECT email FROM users WHERE email = ?');
    $check->execute(array($email));
    $row = $check->rowCount();

    if($row == 1){
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            //on génère un token aléatoire et on le stocke dans la base de données
                $insert = $bdd->prepare('UPDATE users SET reset_token=? WHERE email =?');
                $insert->execute(array($aleatoire, $email));

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
                
                $from = $_ENV['MAIL_USERNAME'];
                $dest= $email;
                $sujet = "Réinitialisation de votre mot de passe";
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
                            <h1>Renouvellement de votre mot de passe</h1>
                            <div id='message'>
                                <p>Vous venez de demander un renouvellement de mot de passe <br> <span>$siteUrl</span></p>
                               <a href='$siteUrl/passwordReset.php?verif=$aleatoire'>Modifier mon mot de passe</a>
                            </div>
                            <p class='alerte'>Ce message est envoyé automatiquement, veuillez ne pas y répondre</p>
                        </div>
                    </body>
                    </html>";
                $headers[] = 'MIME-Version : 1.0';
                $headers[] = 'Content-type: text/html; charset=UTF-8';
                $headers[] = 'From:'.$from;
                mail($dest, $sujet, $corps, implode("\r\n", $headers));
                header('Location:../forget_password.php?get_err=succes');
                exit();
            } else {
                header('Location:../forget_password.php?get_err=succes');
                die();
            }
        } else {
    header('Location:../forget_password.php?get_err=succes');
    die();
}
}
?>