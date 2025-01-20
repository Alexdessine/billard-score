<?php
/////////////////////////////////////////
// Génération de mot de pass           //
// Fichier: pass_gen.php               //
// Date de création: 31.12.09          //
// Crée par: sdk Narkos                //
// Ce fichier peut être modifié        //
/////////////////////////////////////////

// Génération d'un mot de passe
function GenVerif($nbr_caractere = 6) // Reçoi le nbr de caractère que doit contenir le mdp (sinon 6 par défaut)
{
	if(is_numeric($nbr_caractere))
	{
		// Liste des caractères disponible pour la génération du mdp (cases de 0 à 61)
		$caracteres = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",0,1,2,3,4,5,6,7,8,9);
		
		// Création de l'array qui contiendra le mdp
		$array_mdp = array();
		for($boucle = 1; $boucle <= $nbr_caractere; $boucle++)
		{	
			// Ajout du caractère aléatoire dans l'array du mdp
			$array_mdp[] = $caracteres[mt_rand(0,count($caracteres)-1)];
		}
		
		$mdp = implode("",$array_mdp); // Transfo de l'array en string
		return $mdp; // Retourne la chaine contenant le mdp
	}
	else
	{
		return false; // la fonction n'a pas reçu un nombre en paramètre
	}
}
?>
