<?php
session_start();

if ($_SESSION["autoriser"] !== "oui" || !isset($_GET['client'])) {
    header("location:../login.php");
    die();
}

require_once('../config/config.php');


// Fonction pour supprimer un dossier récursivement
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object)) {
                    rrmdir($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        rmdir($dir);
    }
}

// Récupérez l'ID de l'utilisateur à supprimer
$user_client = htmlspecialchars($_GET['client']);

// Supprimez les données de l'utilisateur de la base de données
$stmt = $bdd->prepare('DELETE FROM users WHERE client = :user_client');
$stmt->execute(array('user_client' => $user_client));

// Supprimez le dossier de l'utilisateur dans l'arborescence de fichiers
$dir_path = "../img/match/" . $user_client;
if (is_dir($dir_path)) {
    rrmdir($dir_path); // Utilisation de la fonction rrmdir pour supprimer récursivement
}

// Redirigez l'utilisateur vers une page de confirmation ou de déconnexion
// Par exemple, vous pouvez le rediriger vers la page de déconnexion ou vers une page de confirmation.
header("location:../index.php");