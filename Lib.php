<?php

function connecter()
{
    try {

        $dns = 'base de donnée mysql sur phpMyAdmin';
        $utilisateur = '';
        $motDePasse = '';
        
        $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                        );
        $connection = new PDO( $dns, $utilisateur, $motDePasse, $options );
        return($connection);
    
    
    } catch ( Exception $e ) {
        echo "Connection à MySQL impossible : ", $e->getMessage();
        die();
    }
}

function controlerDate($date) {
    if (preg_match("/^(\d{1,2})[\/|\-|\.](\d{1,2})[\/|\-|\.](\d\d)(\d\d)?$/", $date, $regs)) {
        $jour = ($regs[1] < 10) ? "0".$regs[1] : $regs[1]; 
        $mois = ($regs[2] < 10) ? "0".$regs[2] : $regs[2]; 
        if ($regs[4]) $an = $regs[3] . $regs[4];
              if (checkdate($mois, $jour, $an)) return true;
        else return false;
    }
    else return false;
}

function controlerImage($image) {
    $extensions = array('.png', '.jpg');
    $extension = strrchr($image, '.');
    if(in_array($extension, $extensions)){
        if(strstr($image,' ')==false){
            return true;
        }
    }
    return false;
}

class Anime
{
    private $idA;
    private $titre;
    private $createur;
    private $studio;
    private $dateS;
    private $statut;
    private $note;
    private $image;

    public function __construct($idA,$titre,$createur,$studio,$dateS="00-00-0000",$statut,$note,$image)
    {
        $this->idA=$idA;
        $this->titre=$titre;
        $this->createur=$createur;
        $this->studio=$studio;
        $this->dateS=$dateS;
        $this->statut=$statut;
        $this->note=$note;
        $this->image=$image;
    }

    public function __toString()
    {
        $dateSQL = explode('-', $this->dateS);
		$dateS = $dateSQL[2] . "-" . $dateSQL[1] . "-" . $dateSQL[0];
        $ligneT="<p class='indent'> Titre : "  . $this->titre . " <> 
                 Createur original : " . $this->createur . " <>    
                 Studio : " . $this->studio . " <> 
                 Date de sortie : " . $dateS . " <>  
                 Statut : " . $this->statut . " <>
                 Note : " . $this->note . " <> 
                 Image : " . $this->image . "</p>";
        return $ligneT;
    }
}

$idA=null;
$titre = null;
$createur = null;
$studio = null;
$dateS = null;
$statut = null;
$note = null;
$image =null;
$erreur=array("titre"=>null,"createur"=>null,"dateS"=>null,"studio"=>null,"statut"=>null,"note"=>null, "image"=>null);
?>
