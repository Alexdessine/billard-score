<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if($_SESSION["autoriser"]!="oui" || !isset($_GET['client'])){
    header("location:../login.php");
    die();
}

require_once('../config/config.php');

// Récupérer les données du formulaire
$nom_tournoi = htmlspecialchars($_POST['nom_tournoi']);
$client = htmlspecialchars($_GET['client']);
$lieu = htmlspecialchars($_POST['lieu']);
$dateDebut = htmlspecialchars($_POST['date_debut']);
$dateFin = htmlspecialchars($_POST['date_fin']);

// Insérer le tournoi dans la base de données
$stmt = $bdd->prepare("INSERT INTO tournois (tournois, user_client, lieu, date_debut, date_fin) VALUES (:tournoi, :user_client, :lieu, :date_debut, :date_fin)");
$stmt->execute(array(
    'tournoi' => $nom_tournoi,
    'user_client' => $client,
    'lieu' => $lieu,
    'date_debut' => $dateDebut,
    'date_fin' => $dateFin
));

// Rediriger l'utilisateur vers la page de profil après l'ajout
header("Location: ../user-admin.php?client=". $client ."&aj_err=success");
exit();
?>
