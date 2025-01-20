<?php 
session_start();
if($_SESSION["autoriser"]!="oui" || !isset($_GET['client']) || !isset($_GET['page'])){
    header("location:login.php");
    die();
}

// Récupérer le numéro de page actuelle
if (isset($_GET['page'])) {
    $pageActuelle = $_GET['page'];
} else {
    $pageActuelle = 1;
}
require_once('config/config.php');

// Récupérer l'ID de l'utilisateur connecté
$user_client = $_GET['client'];

// Récupérer le username correspondant à l'ID de l'utilisateur
$stmt = $bdd->prepare('SELECT * FROM users WHERE client = :user_client');
$stmt->execute(array('user_client' => $user_client));
$user = $stmt->fetch(PDO::FETCH_ASSOC);


// Le username de l'utilisateur connecté
$username = $user['username'];
$client = $user['client'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/a6212ffa8d.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" href="img/avatar.ico">
    <title>BillardScore - Statistiques</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    
  </head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <!-- Header page d'accueil -->
    <section class="profil">
        <!-- Affichage bouton modification profil -->
        <div class="modif-profil">
            <a href="profil.php?client=<?=$user['client']?>"><i class="fa-solid fa-pen" style="color: #ffffff;"></i></a>
        </div>
        <!-- Affichage photo de profil -->
        <div class="fond-img-profil">
            <?php if(isset($user['profil_img'])){
                ?><img src="<?=$user['profil_img']?>" alt=""><?php
            }else{
                ?>
            <img src="img/profil.jpg" alt=""><?php
            }?>
        </div>
        <!-- Affichage bouton pour se déconnecter -->
        <div class="ajout-img-profil">
            <a href="deconnexion.php"><i class="fa-solid fa-person-walking-arrow-right" style="color: #ffffff;"></i></a>
        </div>
    </section>
    <!-- Affichage nom et prénom de l'user -->
    <div class="infos-profil">
        <p><span><?= $user['validate'] == 1 ? '<i class="fa-solid fa-badge-check" style="color: #59bd4c;"></i>' : '' ?></span> <?php echo $user['prenom'] . ' ' .$user['nom']?></p>
    </div>
    <!-- Affichage du bouton de retour à l'accueil -->
    <section class="container accueil">
                <a href="user-admin.php?client=<?=$client?>"><i class="fa-solid fa-house"></i><br>Retour accueil</a>
    </section>
    <section class="container-lg container-fluid-xs details_match">
        <?php
        // Nombre de matchs par page
            $matchsParPage = 10;

            $stmt = $bdd->prepare('SELECT COUNT(*) AS total FROM matchs WHERE users_client = :user_client');
            $stmt->bindParam(':user_client', $client, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalMatchs = $result['total'];


            // Calculer le début et la fin de la plage de matchs à afficher
            $debut = ($pageActuelle - 1) * $matchsParPage;
            $fin = $debut + $matchsParPage;
            $fin = min($fin, $totalMatchs);
            
            // Sélectionner les matchs de l'user_client uniquement
            $stmt = $bdd->prepare('SELECT * FROM matchs WHERE users_client = :user_client ORDER BY id DESC LIMIT :debut, :fin');
            $stmt->bindParam(':user_client', $client, PDO::PARAM_STR);
            $stmt->bindParam(':debut', $debut, PDO::PARAM_INT);
            $stmt->bindParam(':fin', $matchsParPage, PDO::PARAM_INT);
            $stmt->execute();
            $matchs = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // Fonction de comparaison pour trier les matchs par ID (ordre croissant)
            function compareByDate($match1, $match2) {
                return strtotime($match2['date']) - strtotime($match1['date']);
            }

            // Trier le tableau $matchs en utilisant la fonction de comparaison
            usort($matchs, 'compareByDate');
        ?>
        <form class="navbar-collapse d-flex recherche" role="search">
            <input class="form-control me-2 bouton-recherche" id="searchInput" type="search" placeholder="Recherche" aria-label="Search">
            <button class="btn btn-outline-primary" type="button" id="searchButton"><i class="fa-regular fa-magnifying-glass"></i></button>
            <a href="traitement_excel/telecharger_excel.php?client=<?=$client?>" download><i class="fa-solid fa-download" style="color: #0370b7;"></i></a>
        </form>
        <!-- <p><?php echo var_dump($pageActuelle) ?></p>
        <p><?php echo $matchsParPage ?></p> -->
        <table class="table">
            <thead>
                <tr class="table-primary align-middle">
                    <th scope="col">Date</th>
                    <th scope="col">Catégorie</th>
                    <th scope="col">Adversaire</th>
                    <th scope="col">Résultat</th>
                    <th scope="col">Temps de jeu</th>
                    <th scope="col">Détail</th>
                </tr>
            </thead>
            <tbody class="table-striped align-middle" id="tableBody">
                <?php
                    foreach ($matchs as $match) {
                        // Vérifier si le match a été perdu
                        $perdu = $match["score_joueur1"] < $match["score_joueur2"];
                        // Ajouter une classe CSS spéciale pour les matchs perdus
                        $classe_css = $perdu ? 'table-danger' : '';
                        $alerte_css = $perdu ? 'text-danger' : '';
                        $dateMatchFormatee = date("d-m-Y", strtotime($match['date']));
                        if ($match['created_at'] != NULL || $match['updated_at'] != NULL){

                            $createdTimestamp = strtotime($match['created_at']);
                            $updatedTimestamp = strtotime($match['updated_at']);
                            $timeDifferenceInSeconds = $updatedTimestamp - $createdTimestamp;
    
                            // Convertir la différence en heure et minute
                            $hours = floor($timeDifferenceInSeconds / 3600);
                            $minutes = floor(($timeDifferenceInSeconds % 3600) / 60);
    
                            // Formatage de l'heure et des minutes
                            $timeDifferenceFormatted = sprintf("%02d:%02d", $hours, $minutes);
                        }
                ?>
                <!-- Contenu tableau résultat des matches -->
                <tr class="<?= $classe_css ?> <?= $match["categorie"]?>">
                    <td class="date <?= $alerte_css ?>"><?=$dateMatchFormatee?></td>
                        <!-- Affichage de la catégorie des matchs et non de l'id des catégories -->
                        <?php 
                        if($match['categorie'] == 1){
                            ?><td class="categorie <?= $alerte_css ?>">Tournoi Individuel</td><?php
                        } else if($match['categorie'] == 2){
                            ?><td class="categorie <?= $alerte_css ?>">Tournoi équipe</td><?php
                        } else if($match['categorie'] == 3){
                            ?><td class="categorie <?= $alerte_css ?>">Amical</td><?php
                        }else if($match['categorie'] == 4){
                            ?><td class="categorie <?= $alerte_css ?>">Entraînement</td><?php
                        }?>
                    <td class="adversaire <?= $alerte_css ?>"><?=$match["joueur"]?></td>
                    <td class="<?= $alerte_css?>"><?=$match["score_joueur1"]?> - <?=$match["score_joueur2"]?></td>
                    <td class="<?= $alerte_css?>"><?=($match["created_at"] === NULL || $match["updated_at"]=== NULL) ? "" : $timeDifferenceFormatted ?></td>
                    <td class="text-center"><a href="details.php?client=<?=$client?>&id=<?=$match["id"]?>"><i class="fa-solid fa-eye"></i></a></td>
                </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
        <!-- Ajouter la pagination -->
        <div class="pagination">
            <?php
                // Calculer le nombre total de matchs pour la recherche
                if (!empty($searchTerm)) {
                    $stmt = $bdd->prepare('SELECT COUNT(*) AS total FROM matchs WHERE users_client = :user_client 
                        AND (date LIKE :searchTerm OR categorie LIKE :searchTerm OR joueur LIKE :searchTerm) ORDER BY id DESC LIMIT :debut, :fin');
                    $stmt->bindParam(':user_client', $client, PDO::PARAM_STR);
                    $searchTermWithWildcards = '%' . $searchTerm . '%';
                    $stmt->bindParam(':searchTerm', $searchTermWithWildcards, PDO::PARAM_STR);
                    $stmt->bindParam(':debut', $debut, PDO::PARAM_INT);
                    $stmt->bindParam(':fin', $matchsParPage, PDO::PARAM_INT);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $totalMatchs = $result['total'];
                } else {
                    $stmt = $bdd->prepare('SELECT COUNT(*) AS total FROM matchs WHERE users_client = :user_client');
                    $stmt->bindParam(':user_client', $client, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $totalMatchs = $result['total'];
                }
                $totalPages = ceil($totalMatchs / $matchsParPage);

                // Calculer le nombre total de matchs pour la recherche
                if (!empty($searchTerm)) {
                    $stmt = $bdd->prepare('SELECT COUNT(*) AS total FROM matchs WHERE users_client = :user_client 
                        AND (date LIKE :searchTerm OR categorie LIKE :searchTerm OR joueur LIKE :searchTerm) ORDER BY id DESC LIMIT :debut, :fin');
                    $stmt->bindParam(':user_client', $client, PDO::PARAM_STR);
                    $searchTermWithWildcards = '%' . $searchTerm . '%';
                    $stmt->bindParam(':searchTerm', $searchTermWithWildcards, PDO::PARAM_STR);
                    $stmt->bindParam(':debut', $debut, PDO::PARAM_INT);
                    $stmt->bindParam(':fin', $matchsParPage, PDO::PARAM_INT);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    // $totalMatchs = $result['total'];
                }

                // Afficher les liens de pagination
                for ($i = 1; $i <= $totalPages; $i++) {
                    if ($i == $pageActuelle) {
                        // Page active non cliquable
                        echo '<a class="current disabled">' . $i . '</a>';
                    } else {
                        echo '<a href="statistiques.php?client=' . $client . '&page=' . $i . '">' . $i . '</a>';
                    }
                }
            ?>
        </div>
    </section>

    <?php 
        // On vérifie que la liste des matchs est supérieure à 0

        if($totalMatchs !=0){
        ?>
            <section class="graphiques container-fluid">
                <h4>Matchs par catégorie</h4>
                <table class="table container col-md-6 align-middle">
                    <thead>
                        <tr class="table-primary align-middle">
                            <th>Catégorie</th>
                            <th>Matchs joués</th>
                            <th>Matchs gagnés</th>
                            <th>% de victoire</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            // Récupérer les catégories distinctes des matchs associés à l'utilisateur
                            $stmt = $bdd->prepare('SELECT DISTINCT categorie FROM matchs WHERE users_client = :user_client');
                            $stmt->execute(array('user_client' => $client));
                            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($categories as $categorie){
                                    $categorie_nom = $categorie['categorie'];

                                    // Compter le nombre de matchs joués dans cette catégorie
                                    $stmt = $bdd->prepare('SELECT COUNT(*) AS matchs_joues FROM matchs WHERE users_client = :user_client AND categorie = :categorie');
                                    $stmt->execute(array('user_client' => $client, 'categorie' => $categorie_nom));
                                    $matchs_joues = $stmt->fetch(PDO::FETCH_ASSOC)['matchs_joues'];

                                    // Compter le nombre de matchs gagnés dans cette catégorie
                                    $stmt = $bdd->prepare('SELECT COUNT(*) AS match_gagnes FROM matchs WHERE users_client = :user_client AND categorie = :categorie AND (score_joueur1 > score_joueur2)');
                                    $stmt->execute(array('user_client' => $client, 'categorie' => $categorie_nom));
                                    $match_gagnes = $stmt->fetch(PDO::FETCH_ASSOC)['match_gagnes'];

                                    // Calculer le pourcentage de matchs gagnés dans cette catégorie
                                    $pourcentage_victoire = ($match_gagnes / $matchs_joues) * 100;
                                ?>
                                <tr>
                                    <?php 
                                        if($categorie_nom == 1){
                                    ?>
                                        <td>Tournoi individuel</td><?php
                                    }else if($categorie_nom == 2){
                                        ?>
                                        <td>Tournoi équipe</td>
                                        <?php
                                    }else if($categorie_nom == 3){
                                        ?>
                                            <td>Amical</td>
                                        <?php
                                    }else if($categorie_nom == 4){
                                        ?>
                                            <td>Entraînement</td>
                                        <?php
                                    }
                                    ?>
                                    <td><?= $matchs_joues?></td>
                                    <td><?= $match_gagnes?></td>
                                    <td><?= round($pourcentage_victoire,2)?> %</td>
                                </tr><?php
                            }
                        ?>
                    </tbody>
                </table>
                <?php
                    // Récupérer le nombre total de parties jouées
                    $stmt = $bdd->prepare('SELECT COUNT(*) AS total_parties FROM partie WHERE user_client = :user_client');
                    $stmt->execute(array('user_client' => $client));
                    $total_parties = $stmt->fetch(PDO::FETCH_ASSOC)['total_parties'];
                    // Récupérer le nombre de parties gagnées et calculer le pourcentage
                    $stmt = $bdd->prepare('SELECT COUNT(*) AS parties_gagnees FROM partie WHERE gagne = 1 AND user_client = :user_client');
                    $stmt->execute(array('user_client' => $client));
                    $parties_gagnees = $stmt->fetch(PDO::FETCH_ASSOC)['parties_gagnees'];
                    $pourcentage_gagnees = ($parties_gagnees / $total_parties) * 100;

                    // Récupérer le nombre de parties jouées avec les jaunes et les rouges
                    $stmt = $bdd->prepare('SELECT COUNT(*) AS parties_jaunes FROM partie WHERE jaune = 1 AND user_client = :user_client');
                    $stmt->execute(array('user_client' => $client));
                    $parties_jaunes = $stmt->fetch(PDO::FETCH_ASSOC)['parties_jaunes'];

                    $parties_rouges = $total_parties - $parties_jaunes;

                    // Récupérer le nombre de parties gagnées avec les jaunes et les rouges
                    $stmt = $bdd->prepare('SELECT COUNT(*) AS parties_gagnees_jaunes FROM partie WHERE jaune = 1 AND gagne = 1 AND user_client = :user_client');
                    $stmt->execute(array('user_client' => $client));
                    $parties_gagnees_jaunes = $stmt->fetch(PDO::FETCH_ASSOC)['parties_gagnees_jaunes'];

                    $parties_gagnees_rouges = $parties_gagnees - $parties_gagnees_jaunes;
                ?>

                <!-- Affichage du tableau récapitulatif des parties jaunes et rouges gagnées / perdues -->
                <h4>Partie par couleur</h4>
                <table class="table container">
                    <tbody>
                        <tr>
                            <td colspan="2" style="background-color: rgba(255, 99, 132, 1);">Rouge</td>
                            <td colspan="2" style="background-color: rgba(255, 206, 86, 1);">Jaune</td>
                        </tr>
                        <tr>
                            <td>gagnée</td>
                            <td>perdue</td>
                            <td>gagnée</td>
                            <td>perdue</td>
                        </tr>
                        <tr>
                            <td><?= $parties_gagnees_rouges ?></td>
                            <td><?= $parties_rouges - $parties_gagnees_rouges ?></td>
                            <td><?= $parties_gagnees_jaunes ?></td>
                            <td><?= $parties_jaunes - $parties_gagnees_jaunes ?></td>
                        </tr>
                    </tbody>
                </table>
                <!-- Affichage des graphiques statistiques -->
                <div class="graphiques chart col-md-6 col-xs-12">
                    <canvas id="myChart" class="chart-canvas"></canvas>
                    <canvas id="pieChart"></canvas>
                </div>
            </section>
            <?php } else{
                ?>
                <section class="graphiques container-fluid">
                    <h4><i class="fa-sharp fa-solid fa-face-smile-wink" style="color: #ffffff;"></i></h4>
                    <p>Commence à jouer pour voir tes stats !</p>
                </section><?php
            }?>


    <!-- Partie des scripts -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchButton = document.getElementById("searchButton");
            const searchInput = document.getElementById("searchInput");

            if (searchButton) {
                searchButton.addEventListener("click", function(event) {
                    event.preventDefault(); // Empêche le formulaire de se soumettre
                    searchMatches(); // Appelle la fonction de recherche
                });
            }

            if (searchInput) {
                searchInput.addEventListener("keydown", function(event) {
                    if (event.keyCode === 13) {
                        event.preventDefault(); // Empêche l'événement par défaut (soumission du formulaire)
                        searchMatches(); // Appelle la fonction de recherche
                    }
                });
            }

        function searchMatches() {
            const searchInput = document.getElementById("searchInput");
            const searchTerm = searchInput.value.toLowerCase().trim();
            const rows = document.querySelectorAll(".table-striped tr");

            let matchCount = 0; // Compteur pour les résultats de recherche
            for (const row of rows) {
                const dateCell = row.querySelector(".date");
                const categorieCell = row.querySelector(".categorie");
                const adversaireCell = row.querySelector(".adversaire");

                const dateText = dateCell.textContent.toLowerCase();
                const categorieText = categorieCell.textContent.toLowerCase();
                const adversaireText = adversaireCell.textContent.toLowerCase();

                if (
                    dateText.includes(searchTerm) ||
                    categorieText.includes(searchTerm) ||
                    adversaireText.includes(searchTerm)
                ) {
                    row.style.display = "";
                    // matchCount++;
                } else {
                    row.style.display = "none";
                }
            }

            // Mettre à jour la pagination en fonction des résultats de recherche
            const pagination = document.querySelector(".pagination");
            if (pagination) {
                pagination.innerHTML = ""; // Effacer la pagination actuelle

                const matchsParPage = 10;
                const totalPages = Math.ceil(matchCount / matchsParPage);

                for (let i = 1; i <= totalPages; i++) {
                    const pageLink = document.createElement("a");
                    pageLink.href = "statistiques.php?client=" + client + "&page=" + i;
                    pageLink.textContent = i;
                    pagination.appendChild(pageLink);
                }

                // Rétablir la pagination complète si la recherche est effacée
                if (searchTerm === "") {
                    for (let i = 1; i <= totalPages; i++) {
                        const pageLink = document.createElement("a");
                        pageLink.href = "statistiques.php?client=" + client + "&page=" + i;
                        pageLink.textContent = i;
                        pagination.appendChild(pageLink);
                    }
                }
            }
        }
    });
    </script>
</body>

</html>

<script>
    // Récupérer les données nécessaires pour le graphique
    var totalParties = <?= $total_parties ?>;
    var partiesGagnees = <?= $parties_gagnees ?>;

    // Créer le graphique en barres
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Total de parties jouées', 'Parties gagnées'],
            datasets: [{
                barThickness: 30,
                barPercentage: 0.5,
                label: 'Nombre de parties',
                data: [totalParties, partiesGagnees],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                ],
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<script>
    // Récupérer les données nécessaires pour le camembert
    var partiesJaunes = <?= $parties_jaunes ?>;
    var partiesRouges = <?= $parties_rouges ?>;

    // Créer le camembert
    var ctx = document.getElementById('pieChart').getContext('2d');
    var pieChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Jaune', 'Rouge'],
            datasets: [{
                data: [partiesJaunes, partiesRouges],
                backgroundColor: [
                    'rgba(255, 206, 86, 0.2)', // Jaune
                    'rgba(255, 99, 132, 0.2)', // Rouge
                ],
                borderColor: [
                    'rgba(255, 206, 86, 1)', // Jaune
                    'rgba(255, 99, 132, 1)', // Rouge
                ],
                borderWidth: 1
            }]
        },
    });
</script>
