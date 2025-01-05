<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Upload</title>
</head>
<body>

<pre>
<?php
require_once("../config/connexion.php");
require_once("../modele/vote.php");

Connexion::connect();

if(isset($_POST['nbEtiquettes'])){
    $cpt = $_POST['nbEtiquettes'];
}else{
    $cpt = 0;
}

$listeEtiquette = array();

$i = 0;
while(isset($_POST["etiquette$i"])){
    $listeEtiquette[$i] = $_POST["etiquette$i"];
    $i++;
}

if(isset($_POST['nbChoix'])){
    $nbChoix = $_POST['nbChoix'];
}else{
    $nbChoix = 0;
}

$listeChoix = array();

for($i=0; $i < $nbChoix; $i++){
    $nomID = "choix$i";
    if($_POST[$nomID] != ""){
        $listeChoix[$i] = $_POST[$nomID];
    }
}

$voteBlanc = 0;

if(isset($_POST["voteBlanc"])){
    $voteBlanc = 1;
}

$multiChoix = 0;

if(isset($_POST["multiChoix"])){
    $multiChoix = 1;
}

$idCreateur = $_POST["idCreateur"];


if(count($listeChoix) < 2){ //Si il y a moins de deux choix, on refuse le vote et on redirige vers le formulaire
    $url = "../routeur.php?controleur=controleurGroupe&action=nouvelleProposition";

    echo "Erreur, un vote doit avoir au moins deux options";
    echo " <meta http-equiv=\"refresh\" content=\"1; url=$url\"> ";
} else {
    $idVote = Vote::insererVote($_POST["titre"],$_POST["delaiDiscussion"],$_POST["delaiVote"],$_POST["description"],$voteBlanc,$multiChoix,$_POST["idGroupe"], $listeEtiquette, $listeChoix, $idCreateur);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        
        $target_dir = "../img/groupPicture/";
        $target_file = $target_dir . basename($_FILES["photo"]["name"]);
        $rename=$target_dir . (string) $idVote; 
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $rename=$target_dir . (string) $idVote .'.'.$imageFileType; 

        print_r($_FILES);
        
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            echo "The file " . htmlspecialchars(basename($_FILES["photo"]["name"])) . " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
        rename($target_file,$rename);
    }

    $url = "../routeur.php";
    echo "Proposition enregistrée !";
    echo " <meta http-equiv=\"refresh\" content=\"1; url=$url\"> "; //redirige vers l'url donnée au bout de 0 secondes, modifier le 0 ou commenter la ligne si on veut voir la page de debug
}
?>
</pre>
</body>
</html>