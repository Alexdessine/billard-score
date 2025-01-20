<?php
session_start();
// Générer le jeton CSRF et le stocker dans la session
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once('../config/config.php');

$client = htmlspecialchars($_GET['client']);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupérer l'ID du match depuis le champ caché
    $match_id = htmlspecialchars($_POST['match_id']);
    // Récupérer le nom du joueur depuis le champ caché
    $joueur = htmlspecialchars($_POST['joueur']);
    // Récupérer le nombre de manches depuis le champ caché
    $nombreDeManches = htmlspecialchars($_POST['nombreDeManches']);
    $moi = $_POST['moi'];

    //Récupération de la table matchs
    $match = $bdd->query("SELECT categorie FROM matchs WHERE id=$match_id");
    $match_data = $match->fetch(PDO::FETCH_ASSOC);
    // Vérifier si un fichier a été téléchargé
    if (isset($_FILES['feuille_match']) && $_FILES['feuille_match']['error'] === UPLOAD_ERR_OK) {
        // Récupérer le nom et l'extension du fichier
        $file_name = $_FILES['feuille_match']['name'];
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

        // Vérifier le format de l'image (JPEG uniquement)
        if ($_FILES['feuille_match']['type'] !== 'image/jpeg') {
            echo "Erreur : Seuls les fichiers JPEG sont autorisés.";
            // Gérer le cas d'erreur et arrêter le traitement si nécessaire
            exit();
        }

        // Vérifier la taille du fichier (inférieure à 5 Mo)
        if ($_FILES['feuille_match']['size'] > 5 * 1024 * 1024) {
            echo "Erreur : La taille du fichier dépasse la limite de 5 Mo.";
            // Gérer le cas d'erreur et arrêter le traitement si nécessaire
            exit();
        }

        // Générer un nom de fichier unique pour éviter les doublons
        $unique_filename = uniqid() . "." . $file_extension;

        // Récupérer le chemin du dossier du match en utilisant l'id du match
        $dossier_match = "../img/match/" . $client . "/" . $match_id . "/";

        // Vérifier si le dossier du match existe, sinon le créer
        if (!file_exists($dossier_match)) {
            mkdir($dossier_match, 0777, true); // Utilisez la permission appropriée pour votre serveur
        }

        // Déplacer l'image dans le dossier du match
        $file_path = $dossier_match . $unique_filename;
        move_uploaded_file($_FILES['feuille_match']['tmp_name'], $file_path);
        echo "Image téléchargée avec succès : " . $file_path;

        // Enregistrer le lien de l'image dans la base de données
        $img_path = "img/match/" . $client . "/" . $match_id . "/" . $unique_filename;
        $stmt = $bdd->prepare('UPDATE matchs SET feuille_match = :feuille_match WHERE id = :match_id');
        if (!$stmt) {
            echo "\nPDO::errorInfo():\n";
            print_r($bdd->errorInfo());
        }
        $stmt->execute(array(
            'feuille_match' => $img_path,
            'match_id' => $match_id
        ));
    }
    if($match_data['categorie'] == 1) {

        // Récupérer valeur tournoi et tour
        if(isset($_POST["tournoi"]) && isset($_POST["tour"])) {
            $tournoi = htmlspecialchars($_POST["tournoi"]);
            $tour = htmlspecialchars($_POST["tour"]);
        }
        // Initialiser des variables pour compter les parties gagnées par chaque joueur
        $parties_gagnees_joueur = 0;
        $parties_gagnees_adversaire = 0;


        // Initialiser la variable $vainqueur à une valeur par défaut
        $vainqueur = ""; // Vous pouvez utiliser une valeur qui n'a pas d'importance pour le comptage

        // Boucle pour traiter les données pour chaque manche
        for ($i = 1; $i <= $nombreDeManches*2; $i++) {
            // Vérifier si la case à cocher pour la manche a été cochée (manche jouée)
            if (isset($_POST["manche_jouee_$i"])) {
                // Récupérer les données de la manche jouée
                $couleur = isset($_POST["couleur_$i"]) ? $_POST["couleur_$i"] : "";
                $casse_ferme = isset($_POST["casse_$i"]) ? 1 : 0;
                $reprise_ferme = isset($_POST["reprise_$i"]) ? 1 : 0;
                $vainqueur = $_POST["vainqueur_manche_$i"];
                $noire_entree = isset($_POST["noire_entree_manche_$i"]) ? 1 : 0;
                $grise_entree = isset($_POST["grise_manche_$i"]) ? 1 : 0;

                // Déterminer les valeurs de jaune, rouge et gagne en fonction du vainqueur et de la couleur choisie
                if ($vainqueur === 'moi') {
                    if ($couleur === 'jaune') {
                        $jaune = 1;
                        $rouge = 0;
                        $gagne = 1;
                    } else {
                        $jaune = 0;
                        $rouge = 1;
                        $gagne = 1;
                    }
                } else {
                    if ($couleur === 'jaune') {
                        $jaune = 1;
                        $rouge = 0;
                        $gagne = 0;
                    } else {
                        $jaune = 0;
                        $rouge = 1;
                        $gagne = 0;
                    }
                }

                // Insérer les données dans la table "partie" pour chaque manche
                $stmt = $bdd->prepare('INSERT INTO partie (joueur, user_client, matchs, jaune, rouge, casse_ferme, reprise_ferme, noire, grise, gagne) VALUES (:joueur, :user_client, :matchs, :jaune, :rouge, :casse_ferme, :reprise_ferme, :noire, :grise, :gagne)');
                $stmt->execute(array(
                    'joueur' => $joueur,
                    'user_client' =>$client,
                    'matchs' => $match_id,
                    'jaune' => $jaune,
                    'rouge' => $rouge,
                    'casse_ferme' => $casse_ferme,
                    'reprise_ferme' => $reprise_ferme,
                    'noire' => $noire_entree,
                    'grise' => $grise_entree,
                    'gagne' => $gagne
                ));
            }
            // Mettre à jour le comptage des parties gagnées pour chaque joueur
            if ($vainqueur === 'moi') {
                $parties_gagnees_joueur++;
            } elseif ($vainqueur === $joueur) { // Vérifier si $vainqueur est "adversaire"
                $parties_gagnees_adversaire++;
            }

        }

        if($parties_gagnees_joueur > $nombreDeManches) {
            $parties_gagnees_joueur = $nombreDeManches;
        } else {
            $parties_gagnees_joueur;
        }
        if($parties_gagnees_adversaire > $nombreDeManches) {
            $parties_gagnees_adversaire = $nombreDeManches;
        } else {
            $parties_gagnees_adversaire;
        }

        $stmt = $bdd->prepare('UPDATE matchs SET score_joueur1 = :score_joueur1, score_joueur2 = :score_joueur2, tournoi = :tournoi, tour = :tour WHERE id = :match_id');
        $stmt->execute(array(
            'score_joueur1' => $parties_gagnees_joueur,
            'score_joueur2' => $parties_gagnees_adversaire,
            'tournoi' => $tournoi,
            'tour' => $tour,
            'match_id' => $match_id
        ));
    } else {
                // Initialiser des variables pour compter les parties gagnées par chaque joueur
        $parties_gagnees_joueur = 0;
        $parties_gagnees_adversaire = 0;
        // Initialiser la variable $vainqueur à une valeur par défaut
        $vainqueur = ""; // Vous pouvez utiliser une valeur qui n'a pas d'importance pour le comptage

        // Boucle pour traiter les données pour chaque manche
        for ($i = 1; $i <= $nombreDeManches*2; $i++) {
            // Vérifier si la case à cocher pour la manche a été cochée (manche jouée)
            if (isset($_POST["manche_jouee_$i"])) {
                // Récupérer les données de la manche jouée
                $couleur = isset($_POST["couleur_$i"]) ? $_POST["couleur_$i"] : "";
                $casse_ferme = isset($_POST["casse_$i"]) ? 1 : 0;
                $reprise_ferme = isset($_POST["reprise_$i"]) ? 1 : 0;
                $vainqueur = $_POST["vainqueur_manche_$i"];
                $noire_entree = isset($_POST["noire_entree_manche_$i"]) ? 1 : 0;
                $grise_entree = isset($_POST["grise_manche_$i"]) ? 1 : 0;

                // Déterminer les valeurs de jaune, rouge et gagne en fonction du vainqueur et de la couleur choisie
                if ($vainqueur === 'moi') {
                    if ($couleur === 'jaune') {
                        $jaune = 1;
                        $rouge = 0;
                        $gagne = 1;
                    } else {
                        $jaune = 0;
                        $rouge = 1;
                        $gagne = 1;
                    }
                } else {
                    if ($couleur === 'jaune') {
                        $jaune = 1;
                        $rouge = 0;
                        $gagne = 0;
                    } else {
                        $jaune = 0;
                        $rouge = 1;
                        $gagne = 0;
                    }
                }

                // Insérer les données dans la table "partie" pour chaque manche
                $stmt = $bdd->prepare('INSERT INTO partie (joueur, user_client, matchs, jaune, rouge, casse_ferme, reprise_ferme, noire, grise, gagne) VALUES (:joueur, :user_client, :matchs, :jaune, :rouge, :casse_ferme, :reprise_ferme, :noire, :grise, :gagne)');
                $stmt->execute(array(
                    'joueur' => $joueur,
                    'user_client' =>$client,
                    'matchs' => $match_id,
                    'jaune' => $jaune,
                    'rouge' => $rouge,
                    'casse_ferme' => $casse_ferme,
                    'reprise_ferme' => $reprise_ferme,
                    'noire' => $noire_entree,
                    'grise' => $grise_entree,
                    'gagne' => $gagne
                ));
            }
            // Mettre à jour le comptage des parties gagnées pour chaque joueur
            if ($vainqueur === 'moi') {
                $parties_gagnees_joueur++;
            } elseif ($vainqueur === $joueur) { // Vérifier si $vainqueur est "adversaire"
                $parties_gagnees_adversaire++;
            }

        }

        if($parties_gagnees_joueur > $nombreDeManches) {
            $parties_gagnees_joueur = $nombreDeManches;
        } else {
            $parties_gagnees_joueur;
        }
        if($parties_gagnees_adversaire > $nombreDeManches) {
            $parties_gagnees_adversaire = $nombreDeManches;
        } else {
            $parties_gagnees_adversaire;
        }

        $stmt = $bdd->prepare('UPDATE matchs SET score_joueur1 = :score_joueur1, score_joueur2 = :score_joueur2, updated_at = now() WHERE id = :match_id');
        $stmt->execute(array(
            'score_joueur1' => $parties_gagnees_joueur,
            'score_joueur2' => $parties_gagnees_adversaire,
            'match_id' => $match_id
        ));
    }
}
    header('Location: ../user-admin.php?client='.$client);
    exit();

?>
