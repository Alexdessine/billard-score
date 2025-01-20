<?php 
session_start();
if($_SESSION["autoriser"]!="oui" || !isset($_GET['client'])){
    header("location:login.php");
    die();
}
require_once('../../../config/config.php');

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

$stmt = $bdd->prepare('SELECT sum(exo_1 + exo_2 + exo_3 + exo_5 + exo_6 + exo_7 + exo_8 + exo_9 + exo_10 + exo_11 + exo_12 + exo_13 + exo_14 + exo_15 + exo_16  + exo_17 + exo_18 + exo_19 + exo_20 + exo_21 + exo_22 + exo_23 + exo_24 + exo_25 + exo_26 + exo_27 + exo_28 + exo_29 + exo_30 + exo_31 + exo_32 + exo_33 + exo_34 + exo_35 + exo_36 + exo_37 + exo_38 + exo_39 + exo_40 + exo_41 + exo_42 + exo_43 + exo_44 + exo_45 + exo_46 + exo_47 + exo_48 + exo_49 + exo_50) AS totScore FROM joueur WHERE user_client = ?');
$stmt->execute(array($client));
$result = $stmt->fetch(PDO::FETCH_ASSOC);




if (isset($_POST['essai1']) && isset($_POST['essai2']) && isset($_POST['essai3']) && isset($_POST['essai4']) && isset($_POST['essai5'])) {
    $essai1 = intval($_POST['essai1']);
    $essai2 = intval($_POST['essai2']);
    $essai3 = intval($_POST['essai3']);
    $essai4 = intval($_POST['essai4']);
    $essai5 = intval($_POST['essai5']);

    // Vérification si les valeurs sont bien des entiers
    if (is_int($essai1) && is_int($essai2) && is_int($essai3) && is_int($essai4) && is_int($essai5)) {
        if($essai1 <= 15 && $essai2 <= 15 && $essai3 <= 15 && $essai4 <= 15 && $essai5 <= 15){
            $somme = $essai1 + $essai2 + $essai3 + $essai4 + $essai5;
            // echo "Somme : " . $somme;
            $verif = $somme + $joueur['exo_4'];
            if($verif > 75){
                $somme = 75;
            }
            if ($somme > $joueur['exo_4']) {
                $score = $somme + $result['totScore'];
                // Assurez-vous que $client est sécurisé avant de l'utiliser dans la requête SQL
                $stmt = $bdd->prepare('UPDATE joueur SET exo_4 = ?, score = ? WHERE user_client = ?');
                $stmt->execute(array($somme, $score, $client));
                header('Location:../exo4.php?client='. $client);
                exit();
            }
        }
    }
}
?>