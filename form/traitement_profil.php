<?php
session_start();
// Générer le jeton CSRF et le stocker dans la session
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once('../config/config.php');

$client = htmlspecialchars($_GET['client']);

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupérer les données du formulaire
    $newPassword = htmlspecialchars($_POST['new_password']);
    $repeatPassword = htmlspecialchars($_POST['repeat_password']);

    // Valider et mettre à jour le mot de passe si nécessaire
    if (!empty($newPassword) && $newPassword === $repeatPassword) {
        // Hasher le nouveau mot de passe avant de le stocker en base de données
        $hashedPassword =  hash('sha256', $newPassword);

        // Mettre à jour le mot de passe dans la base de données
        $updatePasswordQuery = "UPDATE users SET password = :hashedPassword WHERE client = :user_client";
        $stmt = $bdd->prepare($updatePasswordQuery);
        $stmt->bindParam(':hashedPassword', $hashedPassword);
        $stmt->bindParam(':user_client', $client);
        $stmt->execute();
    }

    // Vérifier si un fichier a été téléchargé
if (isset($_FILES['img_profil']) && $_FILES['img_profil']['error'] === UPLOAD_ERR_OK) {
    // Récupérer le nom et l'extension du fichier
    $file_name = $_FILES['img_profil']['name'];
    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

    // Vérifier le format de l'image (JPEG uniquement)
    if ($_FILES['img_profil']['type'] !== 'image/jpeg') {
        echo "Erreur : Seuls les fichiers JPEG sont autorisés.";
        // Gérer le cas d'erreur et arrêter le traitement si nécessaire
        exit();
    }

    // Vérifier la taille du fichier (inférieure à 5 Mo)
    if ($_FILES['img_profil']['size'] > 5 * 1024 * 1024) {
        // echo "Erreur : La taille du fichier dépasse la limite de 5 Mo.";
        header("Location:../profil.php?client=".$client."&reg_err=limit");
        // Gérer le cas d'erreur et arrêter le traitement si nécessaire
        exit();
    }

    // Générer un nom de fichier unique pour éviter les doublons
    $unique_filename = uniqid() . "." . $file_extension;

    // Récupérer le chemin du dossier du match en utilisant l'id du match
    $dossier_match = "../img/match/" . $client . "/profil/";

    // Vérifier si le dossier du match existe, sinon le créer
    if (!file_exists($dossier_match)) {
        mkdir($dossier_match, 0777, true); // Utilisez la permission appropriée pour votre serveur
    }

    // Déplacer l'image dans le dossier du match
    $file_path = $dossier_match . $unique_filename;
    move_uploaded_file($_FILES['img_profil']['tmp_name'], $file_path);
    echo "Image téléchargée avec succès : " . $file_path;

    // Enregistrer le lien de l'image dans la base de données
    $img_path = "img/match/" . $client . "/profil/" . $unique_filename;
    $stmt = $bdd->prepare('UPDATE users SET profil_img = :img_profil WHERE client = :user_client');
    if (!$stmt) {
        echo "\nPDO::errorInfo():\n";
        print_r($bdd->errorInfo());
    }
    $stmt->execute(array(
        'img_profil' => $img_path,
        'user_client' => $client
    ));
}
        // Rediriger vers la page de profil après la mise à jour
        header("location:../profil.php?client=" . $client);
        exit();
}
?>