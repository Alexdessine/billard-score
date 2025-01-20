<?php 

require_once('../config/config.php');


$today = date("Y-m-d");
echo $today;

// obtenez la date de modification du fichier tournois.txt
$fileModified = date("Y-m-d", filemtime("tournois.txt"));
echo $fileModified;

if($today === $fileModified){

    // Executer le script Python pour obtenir les nouvelles données
    $nouvelles_donnees = exec("python alerte_tournoi.py");
    echo $nouvelles_donnees;
    
    // Obtenir la dernière ligne (horodatage) du fichier tournois.txt
    $last_line = exec("python get_last_line.py");
    
    if (strpos($nouvelles_donnees, $last_line) === false){
        
    $email = "alexandre@bourlier.email";
    // echo "<pre>$contenu</pre>";
        if(isset($_SERVER['SERVER_NAME'])){
            switch($_SERVER['SERVER_NAME']){
                case 'matchbillard':
                    $siteUrl = $_ENV['SITE_URL'];
                    break;
                case 'billard-score.fr':
                    $siteUrl = $_ENV['SITE_URL_ENV'];
            }
        }
        // Obtenir le contenu du fichier tournois.txt
    $contenu_fichier = file_get_contents("tournois.txt");
        $dest= $email;
        $sujet = "Nouveau tournoi ajouté";
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
                <h1>Nouveau tournoi !</h1>
                <div id='message'>
                    <p>Un nouveau tournoi a été ajouter</p>
                    <p><pre>".$contenu_fichier."</pre></p>
                </div>
                <p class='alerte'>Ce message est envoyé automatiquement, veuillez ne pas y répondre</p>
            </div>
        </body>
        </html>";
        $headers[] = 'MIME-Version : 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = 'From: admin@billardscore.fr';
        mail($dest, $sujet, $corps, implode("\r\n", $headers));
    } else{
        echo "Aucune nouvelle information n'a été trouvée";
    }
} else {
    echo "Le fichier tournois.txt n'a pas été modifié aujourd'hui";
}







?>