<?php
require_once("Lib.php");
$action = key_exists('action', $_GET)? trim($_GET['action']): null;
$sauvegarde = key_exists('sauvegarde', $_GET)? trim($_GET['sauvegarde']): null;
$zoneTitre="Bienvenue<br> Ce site vous permet de répertorier des séries ou des films d'animations";

switch ($action) {

	case "liste":
		$zoneTitre="Votre liste d'animés";
		$corps="<nav class=n1>";

		$connection = connecter();
		$requete="SELECT * FROM Anime ORDER BY titre ASC";
		$query = $connection->query($requete);
		$query->setFetchMode(PDO::FETCH_OBJ);

		while($enregistrement = $query->fetch())
		{
			$idA=$enregistrement->idA; 
			$titre=$enregistrement->titre; 
			$createur=$enregistrement->createur; 
			$studio=$enregistrement->studio; 
			$dateS=$enregistrement->dateS;
			$dateSQL = explode("-",$dateS);
			$date = $dateSQL[2] . "-" . $dateSQL[1] . "-" . $dateSQL[0];
			$statut=$enregistrement->statut; 
			$note=$enregistrement->note; 
			$image=$enregistrement->image;

			$corps.= '<a href="index.php?action=select&idA=' . $idA . '" class="f2">
					 	<figure> 
						<h3>' . $titre . '</h3> 
						<img src="' . $image . '" width="150" height="210" alt="' . $image. '">
						</figure>
					 </a>';
		}

		$zoneTitre;
		$zonePrincipale = $corps . "</nav>";
		$query = null;
		$connection = null;
		break;
		
	case "insert":
		$zoneTitre ="Ajout d'un animé";
		$cible='insert';
		if (!isset($_POST["titre"]) && !isset($_POST["createur"]))
		{
			include("formulaireAnime.html");
		}
		else{
			$titre = key_exists('titre', $_POST)? trim($_POST['titre']): null;
			$createur = key_exists('createur', $_POST)? trim($_POST['createur']): null;
			$studio = key_exists('studio', $_POST)? trim($_POST['studio']): null;
			$dateS = key_exists('dateS', $_POST)? trim($_POST['dateS']): null;
			$statut = key_exists('statut', $_POST)? trim($_POST['statut']): null;
			$note = key_exists('note', $_POST)? trim($_POST['note']): null;
			$image = key_exists('image', $_FILES)? ($_FILES['image']): null;

			// Gestion des erreurs
			if ($titre=="") $erreur["titre"] = "il manque un titre"; 
			if ($createur=="") $erreur["createur"] = "il manque un createur"; 
			if ($studio=="") $erreur["studio"] = "il manque un studio"; 
			if (controlerDate($dateS)==false) $erreur["dateS"] = "la date de sortie n'a pas le bon format (dd-mm-yyyy)";
			if ($dateS=="") $erreur["dateS"] = "il manque une date de naissance";
			if ($statut=="") $erreur["statut"] = "il manque un statut";
			if ($note=="") $erreur["note"] = "il manque une note";
			if ($image['name']=="") $erreur["image"] = "il manque une image";
			if (controlerImage($image['name'])==false) $erreur["image"] = "l'image doit être au format JPG ou PNG et ne doit pas contenir d'espace";
			$compteur_erreur=count($erreur);
			foreach ($erreur as $cle=>$valeur){
				if ($valeur==null) $compteur_erreur=$compteur_erreur-1;
			}

			if ($compteur_erreur == 0) {
				$connection =connecter();
				$corps = "Ajout réussi <br>";

				// Met la date au foramt SQL année-mois-jour
				$dateS = explode('-', $dateS);
				$dateSQL = $dateS[2] . "-" . $dateS[1] . "-" . $dateS[0];

				// Convertie la note en integer pour correspondre au type de la table
				$note = intval($note);

				// Gestion de l'enregistrement d'une image
				if(isset($_FILES['image'])){
					$file_name = $_FILES['image']['name'];
					$file_tmp = $_FILES['image']['tmp_name'];
					$file_type = $_FILES['image']['type'];
					$file_size = $_FILES['image']['size'];
					
					$upload_dir = "image/";
					$upload_path = $upload_dir . $file_name;
				
					if (file_exists($upload_path)) {
						unlink($upload_path);
					}
				
					move_uploaded_file($file_tmp, $upload_path);
				}
				$query = $connection->prepare("INSERT INTO Anime (titre, createur, studio, dateS, statut, note, image) VALUES (?,?,?,?,?,?,?)");
                $query->execute([$titre, $createur, $studio, $dateSQL, $statut, $note, $upload_path]);
				$anime = new Anime($idA, $titre, $createur, $studio, $dateSQL, $statut, $note, $upload_path);
				$corps .= "Saisie des informations suivantes -> <br>" . $anime;
				$zonePrincipale = $corps;
				$connection = null;
			}
			else {
				include("formulaireAnime.html");
			}
		}
		break;

	case "select":
		if (isset($_GET["idA"])) {
			$idA = $_GET["idA"];
			$connection = connecter();
			$query = $connection->query("SELECT titre, createur, studio, dateS, statut, note, image FROM Anime WHERE idA = $idA");
			$query->setFetchMode(PDO::FETCH_OBJ);
			$anime = $query->fetch();
			$dateSQL = explode('-',$anime->dateS);
			$dateS = $dateSQL[2] . "-" . $dateSQL[1] . "-" . $dateSQL[0];

			$zoneTitre= $anime->titre;
			$zonePrincipale = '<figure class=fiche>
							<img src="' . $anime->image . '" width="350" height="500" alt="' . $image. '"> 
							<div class=info>
								<p class=type>Createur original :</p>' . $anime->createur . '
								<p class=type>Studio :</p>' . $anime->studio . ' 
								<p class=type>Date de sortie :</p>' . $dateS . '  
								<p class=type>Statut :</p>' . $anime->statut . '  
								<p class=type>Note :</p>' . $anime->note . ' 
							</div>
							<nav>
								<a href="index.php?action=update&idA=' . $idA . '">Modifier</a>
								<a href="index.php?action=delete&idA=' . $idA . '">Supprimer</a>
							</nav>
							</figure>
							';
			$connection = null;
		} else {
			$zonePrincipale = "erreur";
		}
		break;

	case "update":
		$zoneTitre = "Modification d'un animé";
        $cible="update";
        if (isset($_GET["idA"])){
            $connection = connecter();
            $idA = $_GET["idA"];

            $requete="SELECT titre, createur, studio, dateS, statut, note, image FROM Anime WHERE idA = $idA";
            $query = $connection->query($requete);
            $query->setFetchMode(PDO::FETCH_OBJ);
            $enregistrement = $query->fetch();

            $titre = $enregistrement->titre;
            $createur = $enregistrement->createur;
            $studio = $enregistrement->studio;
			$dateSQL = explode('-', $enregistrement->dateS);
			$dateS = $dateSQL[2] . "-" . $dateSQL[1] . "-" . $dateSQL[0];
            $statut = $enregistrement->statut;
			$note = $enregistrement->note;
            include("formulaireAnime.html");

            if (!isset($_POST["titre"]) && !isset($_POST["createur"])){
                include("formulaireAnime.html");
            }
            else{
				$titre = key_exists('titre', $_POST)? trim($_POST['titre']): null;
				$createur = key_exists('createur', $_POST)? trim($_POST['createur']): null;
				$studio = key_exists('studio', $_POST)? trim($_POST['studio']): null;
				$dateS = key_exists('dateS', $_POST)? trim($_POST['dateS']): null;
				$statut = key_exists('statut', $_POST)? trim($_POST['statut']): null;
				$note = key_exists('note', $_POST)? trim($_POST['note']): null;			
				$image = key_exists('image', $_FILES)? ($_FILES['image']): null;
				
				// Gestion des erreurs
				if ($titre=="") $erreur["titre"] = "il manque un titre"; 
				if ($createur=="") $erreur["createur"] = "il manque un createur"; 
				if ($studio=="") $erreur["studio"] = "il manque un studio"; 
				if ($dateS=="") $erreur["dateS"] = "il manque une date de naissance";
				if (controlerDate($dateS)==false) $erreur["dateS"] = "la date de sortie n'a pas le bon format (dd-mm-yyyy)";
				if ($note=="") $erreur["note"] = "il manque une note";
				if ($image['name']=="") $erreur["image"] = "il manque une image";
				if (controlerImage($image['name'])==false) $erreur["image"] = "l'image doit être au format JPG ou PNG";
				if ($image['name']!=trim($image['name'])) $erreur["image"] = "le nom de l'image ne doit pas contenir d'espace";
				$compteur_erreur=count($erreur);
                foreach ($erreur as $cle=>$valeur){
                    if ($valeur==null) $compteur_erreur=$compteur_erreur-1;
                }

                if ($compteur_erreur == 0) {

					// Met la date au foramt SQL année-mois-jour
					$dateS = explode('-', $dateS);
					$dateSQL = $dateS[2] . "-" . $dateS[1] . "-" . $dateS[0];

					// Convertie la note en integer pour correspondre au type de la table
					$note = intval($note);

					// Gestion de l'enregistrement d'une image
					if(isset($_FILES['image'])){
						$file_name = $_FILES['image']['name'];
						$file_tmp = $_FILES['image']['tmp_name'];
						$file_type = $_FILES['image']['type'];
						$file_size = $_FILES['image']['size'];
						
						$upload_dir = "image/";
						$upload_path = $upload_dir . $file_name;
					
						if (file_exists($upload_path)) {
							unlink($upload_path);
						}
					
						move_uploaded_file($file_tmp, $upload_path);
						
					}
                    $sql="update Anime set titre='$titre', createur='$createur', studio='$studio', dateS='$dateSQL', statut='$statut', note='$note', image='$upload_path' where idA='$idA'";
					
					$corps='
					<form action="index.php?action=sauvegarde" method="post">
						<input type="hidden" name="type" value="' . 'confirmupdate' . '">
						<input type="hidden" name="idP" value="' . $idA . '">
						<input type="hidden" name="sql" value="' . $sql . '">
						<h3>Etes vous sûr de vouloir modifier la fiche?</h3>
						<nav  class="valide">
							<input type="submit" value="Valider">
							<a href="index.php?action=select&idA=' . $idA . '">Annuler</a>
						</nav>
						</form>';
					$zonePrincipale = $corps ;
                }
                else {
                    include("formulaireAnime.html");
                }
            }        
        }
        break;

	case "delete":
		if (isset($_GET["idA"])) {
			$zoneTitre ="Suppression";
			$idA = $_GET["idA"];
			
			$connection = connecter();
			$sql = "DELETE FROM Anime WHERE idA LIKE '$idA'";
			$corps='
			<form action="index.php?action=sauvegarde" method="post">
			<input type="hidden" name="type" value="' . 'confirmdelete' . '">
		 	<input type="hidden" name="idP" value="' . $idA . '">
			<input type="hidden" name="sql" value="' . $sql . '">
			<h3>Etes vous sûr de vouloir supprimer la fiche?</h3>
			<nav  class="valide">
			<input type="submit" value="Valider">
			<a href="index.php?action=select&idA=' . $idA . '">Annuler</a>
			</nav>
			</form>';
			$zonePrincipale = $corps;
			$connection = null;
		} else {
			$zonePrincipale = "erreur";
		}
		break;

	case "sauvegarde":
		$connection = connecter();
		$type = key_exists('type',$_POST)? $_POST['type']: null;
		$idA = key_exists('idA',$_POST)? $_POST['idA']: null;
		$sql = key_exists('sql',$_POST)? $_POST['sql']: null;

		if ($type =="confirmupdate"){
			$zoneTitre ="Modification d'un anime";
			$corps="<h1>La fiche a été mis à jour avec succès</h1>" ; 
		}
		else{
			$zoneTitre ="Suppression";
			$corps="<h1>La fiche a été supprimé avec succès</h1>" ; 
		}

		$req=$connection->prepare($sql);
		$req->execute();        
		$zonePrincipale=$corps;
		$connection = null;
		break;

	case "apropos":
		$zoneTitre ="Merci d'avoir visiter mon site";
		$corps="<p>Numéro étudiant : 22105104</p>
				<p>Nom : Le Bris</p>
				<p>Prénom : Ilan</p>
				<p>Goupe de TP : 4A</p>
				<p>Point réalisés :</p>
				<ul>
					<li>Formulaire pour créer un objet</li>
					<li>Affichage de tous les objets</li>
					<li>Affichage du détail d'un objet</li>
					<li>Modification d'un objet</li>
					<li>Suppression d'un objet</li>
					<li>Ajout d'une image sur le serveur</li>
				</ul>
				<p>Remarque : La verfication de W3C de la page sauvgarde renvoie des erreurs, car je pense que W3C ne prend pas en compte la requète SQL</p>" ;   
		$zonePrincipale = $corps;
		break;
	
 default:
   $zonePrincipale="" ;
   break;
   
}
include("squelette.php");

?>
