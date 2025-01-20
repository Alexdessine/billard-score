<?php
session_start();
// Générer le jeton CSRF et le stocker dans la session
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once('../config/config.php');

if(isset($_POST['client']) && isset($_POST['joueur']) && isset($_POST['categorie']) && isset($_POST['manches'])){
    $joueur = htmlspecialchars($_POST['joueur']);
    $categorie_nom = htmlspecialchars($_POST['categorie']);
    $casse = isset($_POST["premiereCasse"]) ? 1 : 0;
    $client = htmlspecialchars($_POST['client']);

    $stmt = $bdd->prepare('SELECT id FROM categorie WHERE categorie = :categorie');
    $stmt->execute(array('categorie' => $categorie_nom));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result){
        $categorie_id = $result['id'];
        $manche = htmlspecialchars($_POST['manches']);

    
        $stmt = $bdd->prepare('INSERT INTO matchs(joueur, date, categorie, manches, casse, users_client, created_at) VALUES (:joueur, :date, :categorie, :manches, :casse, :users_client, NOW()) ');
        $stmt->execute(array(
            'joueur' => $joueur,
            'date' => date('y-m-d'),
            'categorie' => $categorie_id,
            'manches' => $manche,
            'casse' => $casse,
            'users_client' => $client
        ));

        // Récupérer l'id du match insérer
        $match_id = $bdd->lastInsertId();

        header('Location: ../matchs.php?client='.$client.'&m='.$manche.'&id='.$match_id);
        die();
    }else header('Location: ../index.php?reg_err=1');
    die();
    }else header('Location: ../index.php?reg_err=1');
    die();

?>