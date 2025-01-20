<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if($_SESSION["autoriser"]!="oui" || !isset($_GET['client'])){
    header("location:../login.php");
    die();
}

require_once('../config/config.php');

// Récupérer les données du formulaire
$nom_tour = htmlspecialchars($_POST['nom_tour']);
$client = htmlspecialchars($_GET['client']);

// Insérer le tournoi dans la base de données
$stmt = $bdd->prepare("INSERT INTO tournoi_tour (tour, user_client) VALUES (:tour, :user_client)");
$stmt->execute(array(
    'tour' => $nom_tour,
    'user_client' => $client
));

// Rediriger l'utilisateur vers la page de profil après l'ajout
header("Location: ../user-admin.php?client=". $client ."&aj_err=success");
exit();
?>
