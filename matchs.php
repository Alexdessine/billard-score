<?php 
session_start();
// Générer le jeton CSRF et le stocker dans la session
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once('config/config.php');
$nombreDeManches = $_GET['m']; // Remplacez cette ligne par le moyen de récupérer le nombre de manches
$numeroMatch = $_GET['id']; // Remplacez cette ligne par le moyen de récupérer le nombre de manches
$client_id = $_GET['client'];


$stmt= $bdd->prepare('SELECT * FROM tournois WHERE user_client = :user_client ORDER BY id ASC');
$stmt->execute(array('user_client' => $client_id));
$tournois = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="icon" href="img/avatar.ico">
    <title>BillardScore - Match</title>
</head>
<body>
    <section class="matchEnCours">
    <h1>Enregistrement des parties par manche</h1>
    <form action="form/traitement_formulaire.php?client=<?php echo $client_id ?>&m=<?php echo $nombreDeManches; ?>&id=<?php echo $numeroMatch ?>" method="post" enctype="multipart/form-data">
        <?php

        // Récupérer le nom du client
        $stmt = $bdd->prepare('SELECT * FROM users WHERE client = :client');
        $stmt->execute(array('client' => $client_id));
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        // Récupérez le nombre de manches à partir de votre formulaire de création de match
        $stmt = $bdd->prepare('SELECT * FROM matchs WHERE id = :match_id');
        $stmt->execute(array('match_id' => $numeroMatch));
        $match = $stmt->fetch(PDO::FETCH_ASSOC);

if($match) {
    // Utilisez $match selon vos besoins pour afficher les détails du matchs
    ?>
    <div class="match_infos">
        <!-- <p>Client : <span class="infos"><?=$client['username']?></p>
        <p>Match : <span class="infos"><?= $match['id'] ?></span></span></p> -->
        <p>Joueur : <span class="infos"><?= $match['joueur'] ?></span></p>
        <p>Nombre de manches : <span class="infos"><?= $nombreDeManches ?></span></p>
        
        <?php
        if($match['casse'] == 1){
            ?>
            <p>Première casse : <span class="infos"><?=$client['username']?></span></p>
            <?php
        }else{
            ?>
            <p>Première casse :<span class="infos"> <?=$match['joueur']?></span></p>
            <?php
        }
        ?>

 
            <!-- Élément pour afficher le chronomètre -->
    <p>Temps écoulé : <span id="timer">00:00:00</span></p>
    <!-- Bouton pour lancer le timer -->
        <a href="#" id="startStopTimer">Lancer le timer</a>
    </div>
   <div class="match_counter">
    <div class="player1">
        <p class="joueur"><?=$client['username']?></p>
        <p id="point1">0</p>
    </div>
    <div class="player2">
                <p class="joueur"><?=$match['joueur']?></p>
        <p id="point2">0</p>
    </div>
   </div>
           <?php
        if($match['categorie'] == 1){
            ?>
            <div class="match_infos">
                <label for="tournoi">Tournoi :</label>
                <select name="tournoi" id="tournoi" required>
                    <option value="">--Sélectionner un tournoi--</option>
                <?php
            // Boucle pour afficher chaque tournoi dans la liste déroulante
           foreach ($tournois as $row) {
                $tournoi_id = $row["id"];
                $tournoi_nom = $row["tournois"];
                ?>
                <option value="<?= $tournoi_id ?>"><?= $tournoi_nom ?></option>
                <?php
            }
            ?>
            </select><br>
                <label for="tour">Tour :</label>
                <select type="text" name="tour" placeholder="tour" required>
                    <option value="">-- Sélectionner un tour --</option>
                        <?php
                        $stmt_tours = $bdd->prepare('SELECT * FROM tournoi_tour WHERE user_client = :client ORDER BY id ASC');
                        $stmt_tours->execute(array('client' => $client_id));
                        $tours = $stmt_tours->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($tours as $row) {
                            $tour_id = $row["id"];
                            $tour_nom = $row["tour"];
                            ?>
                            <option value="<?= $tour_id ?>"><?= $tour_nom ?></option>
                            <?php
                        }
                        ?>
                </select>
            </div>
            <?php
        }
        ?>
    <?php
        ?>
                <input type="hidden" name="match_id" value="<?php echo $numeroMatch; ?>">
                <input type="hidden" name="joueur" value="<?php echo $match['joueur']; ?>">
                <input type="hidden" name="nombreDeManches" value="<?php echo $nombreDeManches; ?>">
                <input type="hidden" name="moi" value="moi">
<?php
    // Boucle pour afficher un formulaire pour chaque manche
    for ($i = 1; $i <= ($nombreDeManches*2)-1; $i++) {
        $isEven = ($i % 2 == 0); // Vérifier si $i est pair
        // $isCasse = ($match['casse'] == 1);

        // Définissez la classe CSS en fonction de la parité de $i et de $match['casse']
        $colorClass = (!$isEven && $match['casse'] == 1) ? 'odd-color' : (($isEven && $match['casse'] == 0) ? 'even-color' : '');

        ?>
        <div class="listMatchs <?=$colorClass?>">
            <h2>Manche <?=$i?></h2>
            <div class="matchAFaire">
                <div class="couleur">
                    <label>Couleur (rouge ou jaune) :</label>
                    <input type='radio' name='couleur_<?=$i?>' value='jaune'><i class="fa-solid fa-circle" style="color: #dbd400; font-size: 1.5em;"></i>
                    <input type='radio' name='couleur_<?=$i?>' value='rouge'><i class="fa-solid fa-circle" style="color: #db0000;  font-size: 1.5em;"></i><br>
                </div>
        
                <div class="casse">
                    <label>Casse ferme :</label>
                    <input type='checkbox' name='casse_<?=$i?>'><br>
                </div>
        
                <div class="reprise">
                    <label>Reprise ferme :</label>
                    <input type='checkbox' name='reprise_<?=$i?>'><br>
                </div>
        
                <div class="vainqueur">
                    <!-- Liste déroulante pour choisir le vainqueur -->
                        <label>Vainqueur :</label>
                        <select name='vainqueur_manche_<?php echo $i?>' onchange="updatePoints('moi', <?php echo $i?>); updatePoints('adversaire', <?php echo $i?>);">
                        <option>-- Choisir un vainqueur --</option>
                        <option value='moi'><?php echo $client['username']?></option>
                        <option value='<?php echo $match['joueur']?>'><?php echo $match['joueur']?></option>
                        </select><br>
                </div>
                <div class="manche">
                    <label>Manche jouée : </label>
                    <input type='checkbox' name='manche_jouee_<?=$i?>' value='1'><br>
                </div>
                <div class="noire">
                    <label>Noire entrée en cours de partie :</label>
                    <input type='checkbox' name='noire_entree_manche_<?=$i?>'><br>
                </div>
                <div class="grise">
                    <label>Grise :</label>
                    <input type='checkbox' name='grise_manche_<?=$i?>'><br>
                </div>
            </div>
        </div>
        <?php
    }
}else{
    ?>
    <p>Match non trouvé</p>
    <?php
}
        ?>
        <div class="fichier">
            <label for="feuille_match">Feuille de match (JPEG uniquement, max 5Mo) :</label>
            <input type="file" name="feuille_match" id="feuille_match" accept="image/jpeg" class="button_fichier" maxlength="5242880" onchange="displayFileSize()">
            <p id="file_size_limit"></p>
        </div>
        <div class="envoyer">
            <button type="submit"><i class="fa-solid fa-floppy-disk" style="color: #ffffff;"></i> Enregistrer</button>
        </div>
    </form>
    </section>
</body>
</html>

<script>
    function displayFileSize(){
        var input = document.getElementById("feuille_match");
        var fileSize = input.files[0].size; //Taille du fichier en octets
        var maxSize = 5 * 1024 * 1024; //5Mo en octets

        var fileSizeDisplay = (fileSize / (1024 * 1024)).toFixed(2) + "Mo";
        var fileSizelimitDisplay = (maxSize / (1024 * 1024)).toFixed(2) + "Mo";

        var fileLimitElement = document.getElementById("file_size_limit");
        fileLimitElement.innerHTML = "Taille du fichier : " + fileSizeDisplay + " / Limite : " + fileSizelimitDisplay;

        if (fileSize > maxSize){
            // Le fichier dépasse la  limite, vous pouvez ajouter ici des styles CSS pour indiquer le dépassement de la limite
            fileLimitElement.style.color = "red";
        } else {
            fileLimitElement.style.color = "black";
        }
    }

</script>

<script>
  // Variables pour le chronomètre
  let timerInterval; // ID de l'intervalle pour le chronomètre
  let timerRunning = false; // Indicateur si le chronomètre est en cours d'exécution
  let startTime; // Heure de début du chronomètre

  // Fonction pour démarrer ou arrêter le chronomètre
  function startStopTimer() {
    if (timerRunning) {
      // Arrêter le chronomètre
      clearInterval(timerInterval);
      timerRunning = false;
    } else {
      // Démarrer le chronomètre
      startTime = Date.now();
      timerInterval = setInterval(updateTimer, 1000);
      timerRunning = true;
    }
  }

  // Fonction pour mettre à jour le chronomètre
  function updateTimer() {
    const currentTime = Date.now();
    const elapsedTimeInSeconds = Math.floor((currentTime - startTime) / 1000);

    // Calculer les heures, minutes et secondes
    const hours = Math.floor(elapsedTimeInSeconds / 3600);
    const minutes = Math.floor((elapsedTimeInSeconds % 3600) / 60);
    const seconds = elapsedTimeInSeconds % 60;

    // Formater l'heure sous forme de chaîne (00:00:00)
    const formattedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

    // Mettre à jour l'affichage du chronomètre
    document.getElementById('timer').textContent = formattedTime;
  }

//   Gestionnaire d'évènements pour le chargement de la page
document.addEventListener('DOMContentLoaded', function(){
    startStopTimer();
});
  // Gestionnaire d'événements pour le bouton "Lancer le timer"
  document.getElementById('startStopTimer').addEventListener('click', function (event) {
    event.preventDefault(); // Empêcher le comportement par défaut du lien

    // Démarrer ou arrêter le chronomètre en fonction de son état actuel
    startStopTimer();
  });
</script>

<script>
    const point1 = document.getElementById("point1");
    const point2 = document.getElementById("point2");
    const nombreDeManches = <?php echo $nombreDeManches; ?>;

    let scorePlayer1= 0;
    let scorePlayer2= 0;

    document.querySelector(".player1").addEventListener("click", () => {
        if (scorePlayer1 < nombreDeManches && scorePlayer2 < nombreDeManches){
            scorePlayer1++;
            point1.textContent = scorePlayer1;
        }
        checkWinner();
    });

    document.querySelector('.player2').addEventListener("click", () => {
        if (scorePlayer1 < nombreDeManches && scorePlayer2 < nombreDeManches){
            scorePlayer2++;
            point2.textContent = scorePlayer2;
        }
        checkWinner();
    });

    function checkWinner(){
    if (scorePlayer1 === nombreDeManches) {
        document.querySelector(".player1").classList.add("vert");
        document.querySelector(".player1").removeEventListener("click", player1ClickHandler);
    }
    if (scorePlayer2 === nombreDeManches) {
        document.querySelector(".player2").classList.add("vert");
        document.querySelector(".player2").removeEventListener("click", player2ClickHandler);
    }
}

document.querySelector(".player1").addEventListener("click", player1ClickHandler);
document.querySelector(".player2").addEventListener("click", player2ClickHandler);

</script>

<script>
    const matchCounter = document.querySelector(".match_counter");
    const initialOffset = matchCounter.getBoundingClientRect().top;

    window.addEventListener("scroll", () => {
        if (window.pageYOffset > initialOffset){
            matchCounter.style.position = "fixed";
            matchCounter.style.top = "20px";
            matchCounter.style.transform = "translateX(14%)";
            matchCounter.style.width = "350px";
            matchCounter.style.transition = "ease-in-out .5s";
        } else {
            matchCounter.style.position = "static";
            matchCounter.style.top = "auto";
            matchCounter.style.transform = "none";
        }
    })
</script>