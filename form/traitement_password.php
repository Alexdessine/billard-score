<?php
require_once('../config/config.php');



if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupérer le client depuis le formulaire
    $client = htmlspecialchars($_GET['client']);
        // Récupérer les données du formulaire
    $newPassword = htmlspecialchars($_POST['new_password']);
    $repeatPassword = htmlspecialchars($_POST['repeat_password']);

    if (!empty($newPassword) && $newPassword === $repeatPassword) {
        // Hasher le nouveau mot de passe avant de le stocker en base de données
        $hashedPassword =  hash('sha256', $newPassword);

        // Mettre à jour le mot de passe dans la base de données
        $updatePasswordQuery = "UPDATE users SET password = :hashedPassword, reset_token = NULL WHERE client = :user_client";
        $stmt = $bdd->prepare($updatePasswordQuery);
        $stmt->bindParam(':hashedPassword', $hashedPassword);
        $stmt->bindParam(':user_client', $client);

        if ($stmt->execute()){
            header('Location:../login.php');
            die(); 
        } else {
            // Gestion de l'erreur  de mise à jour de la base de données
            echo "Erreur lors de la mise à jour des données";
        }
    } else {
        // Gestion des erreurs de saisie des mots de passe
        echo "Les mots de passe ne correspondent pas";
    }
} else{
    header('Location: ../index.php');
    exit();
}

?>
