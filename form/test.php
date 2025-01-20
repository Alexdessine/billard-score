<?php 
require_once('../config/config.php');

if(isset($_POST['joueur']) && isset($_POST['categorie']) && isset($_POST['manches'])){
    $joueur = htmlspecialchars($_POST['joueur']);
    $categorie_nom = htmlspecialchars($_POST['categorie']);
    $casse = isset($_POST['premiereCasse'])? 1 : 0;
    $manches = htmlspecialchars($_POST['manches']);

    $stmt = $bdd->prepare('SELECT id FROM categorie WHERE categorie = :categorie');
    $stmt->execute(array('categorie' => $categorie_nom));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result){
        $categorie_id = $result['id'];

        $stmt = $bdd->prepare('INSERT INTO matchs(joueur, date, categorie, manches, casse) VALUES (:joueur, :date, :categorie, :manches, :casse)');
        $stmt->execute(array(
            'joueur' => $joueur,
            'date' => date('y-m-d'),
            'categorie' => $categorie_id,
            'manches' => $manches,
            'casse' => $casse
        ));

        // Récupération de l'id du match insérer
        $match_id = $bdd->lastInsertId();

        header('Location: ../matchs.php?m='.$manches.'&id='.$match_id);
        die();
    }else header('Location: ../index.php?reg_err=categorie_non_trouvée');
    die();
}elseif(isset($_POST['joueur']) && isset($_POST['categorie']) && isset($_POST['partie_seche'])){
    $joueur = htmlspecialchars($_POST['joueur']);
    $categorie_nom = htmlspecialchars($_POST['categorie']);
    $casse = isset($_POST['premiereCasse'])? 1 : 0;
    $manches = "1";

    $stmt = $bdd->prepare('SELECT id FROM categorie WHERE categorie = :categorie');
    $stmt->execute(array('categorie' => $categorie_nom));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result){
        $categorie_id = $result['id'];

        $stmt = $bdd->prepare('INSERT INTO matchs(joueur, date, categorie, manches, casse) VALUES (:joueur, :date, :categorie, :manches, :casse)');
        $stmt->execute(array(
            'joueur' => $joueur,
            'date' => date('y-m-d'),
            'categorie' => $categorie_id,
            'manches' => $manches,
            'casse' => $casse
        ));

        //Récuperation de l'id du match insérer
        $match_id = $bdd->lastInsertId();

        header('Location: ../matchs.php?m='.$manches.'&id='.$match_id);
        die();
}else header('Location: ../index.php?reg_err=categorie_non_trouvée');
die();
}else header('Location: ../index.php?reg_err=partie_seche_manquante');
die();

?>