<?php
// Inclure la bibliothèque PhpSpreadsheet
require '../vendor/autoload.php';
require_once '../config/config.php';

use PDO;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$client = htmlspecialchars($_GET['client']);

// Récupération du nom du profil
$stmt_username = $bdd->prepare('SELECT username FROM users WHERE client = :client');
$stmt_username->execute(array('client' => $client));
$username = $stmt_username->fetchColumn();

// ... Votre code pour récupérer les données de matchs depuis la base de données ...
$stmt = $bdd->prepare('SELECT * FROM matchs WHERE users_client = :user_client');
$stmt->execute(array('user_client' => $client));
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ... Code pour récupérer les noms de catégorie ...
// Je suppose que vous avez une table 'categories' avec une colonne 'id' pour l'ID de la catégorie et une colonne 'nom' pour le nom de la catégorie
$stmt_categories = $bdd->prepare('SELECT id, categorie FROM categorie');
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

// Créer un nouvel objet Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Définir les styles pour les entêtes de colonne
$styleHeader = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    'borders' => ['outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR, 'rotation' => 90, 'startColor' => ['argb' => 'FFE5E5E5'], 'endColor' => ['argb' => 'FFE5E5E5']]
];

// Appliquer les styles aux entêtes de colonne
$sheet->getStyle('A1:G1')->applyFromArray($styleHeader);

// Appliquer des styles pour le contenu des cellules
$styleContent = [
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    'borders' => ['outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
];

// Appliquer le style aux données de matchs (de la cellule A2 jusqu'à la dernière cellule)
$lastRow = $sheet->getHighestRow();
$sheet->getStyle('A2:G' . $lastRow)->applyFromArray($styleContent);

// Définir la largeur des colonnes
$sheet->getColumnDimension('A')->setWidth(12);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(20);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(20);
$sheet->getColumnDimension('G')->setWidth(12);

// Entêtes de colonne
$sheet->setCellValue('A1', 'Date');
$sheet->setCellValue('B1', 'Catégorie');
$sheet->setCellValue('C1', 'Adversaire');
$sheet->setCellValue('D1', 'Nombre de manches');
$sheet->setCellValue('E1', 'Score ' .$username);
$sheet->setCellValue('F1', 'Score adversaire');
$sheet->setCellValue('G1', 'Résultat');

// Remplir les données de matchs
$row = 2;
foreach ($matches as $match) {
    $categorie_nom = '';
    foreach ($categories as $categorie) {
        if ($categorie['id'] == $match['categorie']) {
            $categorie_nom = $categorie['categorie'];
            break;
        }
    }

    // Formater la date au format "DD-MM-YYYY"
    $date_formattee = date('d-m-Y', strtotime($match['date']));

    $sheet->setCellValue('A' . $row, $date_formattee);
    $sheet->setCellValue('B' . $row, $categorie_nom); // Utiliser le nom de la catégorie
    $sheet->setCellValue('C' . $row, $match['joueur']);
    $sheet->setCellValue('D' . $row, $match['manches']);
    $sheet->setCellValue('E' . $row, $match['score_joueur1']);
    $sheet->setCellValue('F' . $row, $match['score_joueur2']);
    $sheet->setCellValue('G' . $row, $match['score_joueur1'] > $match['score_joueur2'] ? 'Gagné' : 'Perdu');
    $row++;
}

// Créer un objet Writer pour générer le fichier Excel
$writer = new Xlsx($spreadsheet);

// Définir les en-têtes HTTP pour le téléchargement
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="donnees_matchs.xlsx"');
header('Cache-Control: max-age=0');

// Commencer à stocker le contenu de la sortie dans un tampon
ob_start();

// Envoyer le fichier Excel en sortie
$writer->save('php://output');

// Récupérer le contenu du tampon et envoyer la sortie
ob_end_flush();

?>