<?php 
session_start();
if($_SESSION["autoriser"]!="oui" || !isset($_GET['client'])){
    header("location:login.php");
    die();
}
require_once('config/config.php');

// Récupérer l'ID de l'utilisateur connecté
$user_client = $_GET['client'];

// Récupérer le username correspondant à l'ID de l'utilisateur
$stmt = $bdd->prepare('SELECT username, client FROM users WHERE client = :user_client');
$stmt->execute(array('user_client' => $user_client));
$user = $stmt->fetch(PDO::FETCH_ASSOC);


// Le username de l'utilisateur connecté
$username = $user['username'];
$client = $user['client'];


// Récupérer le nombre total de parties jouées
$stmt = $bdd->prepare('SELECT COUNT(*) AS total_parties FROM partie WHERE user_client = :user_client');
$stmt->execute(array('user_client' => $client));
$total_parties = $stmt->fetch(PDO::FETCH_ASSOC)['total_parties'];


// Vérifier si aucun match n'est enregistré
if ($total_parties == 0) {
    // Afficher un message d'information
    ?>
    <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/a6212ffa8d.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Profil <?=$username?></title>
</head>
<body>
    <div class="infos_profil_admin">
        <a href="login.php"><i class="fa-solid fa-power-off" style="color: #0370b7;"></i></a>
        <a href="#"><i class="fa-solid fa-user" style="color: #0370b7;"></i></a>
        <a href="index.php?client=<?=$client ?>"><i class="fa-solid fa-memo-pad" style="color: #0370b7;"></i></a>
    </div>
    <div class="welcome-msg-profil">
    <h1>Bienvenue, <?= $username ?></h1>
    <p>Tu n'as pas encore joué de match !</p>
</div>
</body>
    <?php
} else {

// Récupérer le nombre total de parties jouées
$stmt = $bdd->prepare('SELECT COUNT(*) AS total_matchs FROM matchs WHERE users_client = :user_client');
$stmt->execute(array('user_client' => $client));
$total_matchs = $stmt->fetch(PDO::FETCH_ASSOC)['total_matchs'];

// Récupérer le nombre de parties gagnées et perdues
$stmt = $bdd->prepare('SELECT COUNT(*) AS matchs_gagnes FROM matchs WHERE users_client = :user_client AND (score_joueur1 > score_joueur2 OR (score_joueur1 = score_joueur2 AND casse = 1))');
$stmt->execute(array('user_client' => $client));
$matchs_gagnes = $stmt->fetch(PDO::FETCH_ASSOC)['matchs_gagnes'];

$matchs_perdus = $total_matchs - $matchs_gagnes;

// Récupérer les catégories distinctes des matchs associés à l'utilisateur
$stmt = $bdd->prepare('SELECT DISTINCT categorie FROM matchs WHERE users_client = :user_client');
$stmt->execute(array('user_client' => $client));
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    // Récupérer le nombre de casse ferme effectuées avec la date de la partie correspondante
    $stmt = $bdd->prepare('SELECT COUNT(*) AS casse FROM partie WHERE casse_ferme = 1 AND user_client = :user_client');
    $stmt->execute(array('user_client' => $client));
    $cas_ferme = $stmt->fetch(PDO::FETCH_ASSOC)['casse'];

    // Récupérer le nombre de reprise ferme effectuées avec la date de la partie correspondante
    $stmt = $bdd->prepare('SELECT COUNT(*) AS reprise FROM partie WHERE reprise_ferme = 1 AND user_client = :user_client');
    $stmt->execute(array('user_client' => $client));
    $reprise_ferme = $stmt->fetch(PDO::FETCH_ASSOC)['reprise'];

    // Nouvelles requêtes SQL pour compter les victoires par catégorie
    $stmt = $bdd->prepare('SELECT COUNT(*) AS parties_gagnees_categorie FROM partie WHERE gagne = 1 AND user_client = :user_client AND categorie = :categorie');

    ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/a6212ffa8d.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Profil <?=$username?></title>
</head>
<body>
    <div class="infos_profil_admin">
        <a href="login.php"><i class="fa-solid fa-power-off" style="color: #0370b7;"></i></a>
        <a href="index.php?client=<?=$client ?>"><i class="fa-solid fa-memo-pad" style="color: #0370b7;"></i></a>
    </div>
    <section class="tableaux tableau_profil">
        <!-- Ajout tournoi / tour -->
        <div class="ajout_tournoi">
                <h1>Mon Profil</h1>
    
    <h2>Ajouter un Tournoi</h2>
    <form action="form/ajouter_tournoi.php?client=<?=$client?>" method="post">
        <label for="nom_tournoi">Nom du Tournoi :</label>
        <input type="text" name="nom_tournoi" id="nom_tournoi" required>
        <label for="lieu">Lieu :</label>
        <input type="text" name="lieu" id="lieu" required>
        <button type="submit">Ajouter</button>
    </form>

    <h2 class="tour">Ajouter un Tour</h2>
    <form action="form/ajouter_tour.php?client=<?=$client?>" method="post">
        <label for="nom_tour">Nom du Tour :</label>
        <input type="text" name="nom_tour" id="nom_tour" required>
        </select>
        <button type="submit">Ajouter</button>
    </form>
        </div>
        <!-- Ajouter le tableau des statistiques après le tableau des matchs -->
        <div class="tableau_stat">
            <table>
                <thead>
                    <tr>
                        <th>Statistiques</th>
                        <th>Valeur</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Nombre total de parties jouées</td>
                        <td><?= $total_parties ?></td>
                    </tr>
                    <tr>
                        <td>Pourcentage de victoire</td>
                        <td><?= round($pourcentage_gagnees, 2) ?>%</td>
                    </tr>
                    <tr>
                        <td>Nombre de parties jouées avec les jaunes</td>
                        <td><?= $parties_jaunes ?></td>
                    </tr>
                    <tr>
                        <td>Nombre de parties jouées avec les rouges</td>
                        <td><?= $parties_rouges ?></td>
                    </tr>
                    <tr>
                        <td>Nombre de casse ferme effectuées</td>
                        <td><?= $cas_ferme ?></td>
                    </tr>
                    <tr>
                        <td>Nombre de reprise ferme effectuées</td>
                        <td><?= $reprise_ferme ?></td>
                    </tr>
                </tbody>
            </table>
            <table class="tableaux tableau_couleur">
                <tbody>
                    <tr>
                        <td colspan="4" style="background-color: #0370b7; color:#fff; font-weight:500;">Par couleur</td>
                    </tr>
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

            <table>
                <thead>
                    <tr>
                        <th>Catégorie</th>
                        <th>Matchs joués</th>
                        <th>Matchs gagnés</th>
                        <th>% de victoire</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
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
        </div>
        <!-- Affichage des graphiques statistiques -->
        <div class="graphiques">
            <canvas id="myChart"></canvas>
            <canvas id="pieChart"></canvas>
        </div>
    </section>
</body>
</html>
<?php
}
?>
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
        type: 'pie',
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


