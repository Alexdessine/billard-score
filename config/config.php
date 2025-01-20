<?php 
// Activer le rapport d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définir un gestionnaire d'erreurs personnalisé
function customErrorHandler($severity, $message, $file, $line) {
    // Enregistrer l'erreur dans un fichier de journal (vous pouvez personnaliser le chemin du fichier)
    $logFilePath = 'logs/error_log.txt';
    $logMessage = date('Y-m-d H:i:s') . " - [$severity] $message in $file on line $line" . PHP_EOL;
    error_log($logMessage, 3, $logFilePath);
}

// Définir le gestionnaire d'erreurs personnalisé
set_error_handler('customErrorHandler');

$rootPath = dirname(__DIR__);
require_once $rootPath . '/vendor/autoload.php';
$dotenv=Dotenv\Dotenv::createImmutable($rootPath);
$dotenv->load();


if(isset($_SERVER['SERVER_NAME']))
{
    switch($_SERVER['SERVER_NAME']){
        case 'matchbillard':
            $pdoOptions = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
            if ($_ENV['DB_DEV_PASS'] === ''){
                // Pas de mot de passe pour la connexion à la base de données en local
                $dsn = 'mysql:host=' . $_ENV['DB_DEV_HOST'] . ';dbname=' . $_ENV['DB_DEV_NAME'] . ';charset=utf8';
                $bdd = new PDO($dsn ,$_ENV['DB_DEV_USER'] , null, $pdoOptions);
                break;
            }else {
                $dsn = 'mysql:host=' . $_ENV['DB_DEV_HOST'] . ';dbname=' . $_ENV['DB_DEV_NAME'] . ';charset=utf8';
                $bdd = new PDO($dsn, $_ENV['DB_DEV_USER'] , $_ENV['DB_DEV_PASS'], $pdoOptions );
                break;
            }
             
        case 'billard-score.fr':
            $pdoOptions = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
             $dsn = 'mysql:host=' . $_ENV['DB_PROD_HOST'] . ';dbname=' . $_ENV['DB_PROD_NAME'] . ';charset=utf8';
                $bdd = new PDO($dsn, $_ENV['DB_PROD_USER'] , $_ENV['DB_PROD_PASS'], $pdoOptions );
                break;
                
        case 'beta.billard-score.fr':
            $pdoOptions = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
             $dsn = 'mysql:host=' . $_ENV['DB_PROD_HOST'] . ';dbname=' . $_ENV['DB_PROD_NAME'] . ';charset=utf8';
                $bdd = new PDO($dsn, $_ENV['DB_PROD_USER'] , $_ENV['DB_PROD_PASS'], $pdoOptions );
                break;
    }
}


$categorie = $bdd->query("SELECT categorie FROM categorie ORDER BY id ASC");
// $match = $bdd->query("SELECT * FROM matchs INNER JOIN categorie c ON categorie = c.id");
$sql = "SELECT m.*, c.categorie
        FROM matchs m
        INNER JOIN categorie c ON m.categorie = c.id";

$stmt = $bdd->query($sql);
if ($stmt !== false) {
    $matchs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}else{
    echo "Aucun résultat trouvé";
}

$details=$bdd->query('SELECT * FROM partie ORDER BY id ASC');
$user=$bdd->query('SELECT * FROM users ORDER BY id ASC');


?>