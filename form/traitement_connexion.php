<?php 
require_once('../config/config.php');


if ($_SERVER["REQUEST_METHOD"] === "POST"){
    //On vérifie le jeton CSRF (protection contre les attaques CSRF)
    session_start();
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
        die("Erreur : Jeton CSRF invalide.");
    }
}

//On vérifie les entrées du formulaire
if(isset($_POST['email']) && isset($_POST['password'])){
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    
    //On sélectionne les entrées de la table user dans la bdd
    $check = $bdd->prepare('SELECT username, email, password, login_attempts, last_login_attempt FROM users WHERE email = ?');
    $check->execute(array($email));
    $data = $check->fetch();
    $row = $check->rowCount();

    if($row == 1){
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            // On vérifie si le compte est vérouillé en raison de tentatives échouées
            $maxAttempts = 5; //Nombre maximum de tentatives de connexion échouée
            $lockoutTime = 300; //Durée en secondes pendant laquelle le compte reste vérouillé (5 minutes)
            $currentTime = time();
            $lastLoginAttempt = strtotime($data['last_login_attempt']);
            $loginAttempts = (int)$data['login_attempts'];

            if ($loginAttempts >= $maxAttempts && ($currentTime - $lastLoginAttempt) < $lockoutTime){
                // Le compte est vérouillé en raison de tentatives échouées
                $remainingTime = $lockoutTime - ($currentTime - $lastLoginAttempt);
                header('Location:../login.php?reg_err=locked&time='. $remainingTime);
                die();
            }
            //On hash le password
            $password = hash('sha256', $password);
            //on insert les données dans la base de données users
            if($data['password'] === $password){
                // Réinitialiser le compteur de tentatives échouées et la date de dernière tentative de connexion
                $resetAttempts = 0;
                $resetLastLoginAttempt = null;
                $username = $data['username'];
                $_SESSION['email'] = $username;
                $_SESSION['password'] = $password;
                $insert = $bdd->prepare('UPDATE users SET updated_at=now(), login_attempts=?, last_login_attempt=?, reset_token = null WHERE email =?');
                $insert->execute(array($resetAttempts, $resetLastLoginAttempt, $email));
                $_SESSION["autoriser"]="oui";
                $stmt = $bdd->prepare('SELECT * FROM users WHERE email = :email');
                $stmt->bindValue(":email", $email, PDO::PARAM_STR);
                $executeIsOk = $stmt->execute();
                $user = $stmt->fetch();
                header('Location:../user-admin.php?client='.$user['client']);
                exit();
            } else {
                // Incrémenter le compteur de tentatives échouées et enregistrer la date de dernière tentatives de connexion
                $loginAttempts++;
                $lastLoginAttempt = date('Y-m-d H:i:s');
                $updateAttempts = $bdd->prepare('UPDATE users SET login_attempts=?, last_login_attempt=? WHERE email=?');
                $updateAttempts->execute(array($loginAttempts, $lastLoginAttempt, $email));
                header('Location:../login.php?reg_err=error');
                die();
            }
        } else {
    header('Location:../login.php?reg_err=error');
    die();
}
    }else {
    header('Location:../login.php?reg_err=error');
    die();
}
}
?>