<?php 
Class Rest{

    public static function getGroupe(int $idGroupe, int $votes=0){
        $requete = "SELECT idGroupe, nomGroupe,budget FROM Groupe WHERE idGroupe = $idGroupe;";
        $resultat = Connexion::pdo()->query($requete);
        $resultat->setFetchmode(PDO::FETCH_CLASS,"Groupe");
        $data = $resultat->fetch(PDO::FETCH_ASSOC);
        if($votes){
            $requete = "SELECT idVote FROM Vote where idGroupe=$idGroupe;";
            $resultat = Connexion::pdo()->query($requete);
            $votes = array();
            $a=$resultat->fetch(PDO::FETCH_ASSOC);
            while($a!=null){
                array_push($votes,$a["idVote"]);
                $a=$resultat->fetch(PDO::FETCH_ASSOC);

            }
            $data["votes"] = $votes;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public static function getUtilisateur($idUtilisateur){
        $requete = "SELECT * FROM Utilisateur WHERE idUtilisateur = $idUtilisateur;";
        $resultat = Connexion::pdo()->query($requete);

        return json_encode($resultat->fetch(PDO::FETCH_ASSOC),JSON_UNESCAPED_UNICODE);
    }




    /*
        ATTENTION: si json_encode() ou getJSON() ne fonctionne plus, c'est de la faute de Vianney (moi)
        car les DateTime et les DateInterval n'ont pas de méthode __toString() (il faut utiliser leur méthode format())
    */
    public static function getVote(int $idVote, int $idUser=NULL){
        $vote = Vote::getVote($idVote, $idUser);
        // echo"<pre>";
        // print_r($vote->toArray());
        // echo"</pre>";
        return json_encode($vote->toArray(),JSON_UNESCAPED_UNICODE);
        //Vote va garder un json fucked up pour l'instant TODO : creer une fonction to_array pour Vote
    }
    public static function put(string $table,array $clefs , array $valeurs){
        //ce code est assez sensible aux injections sql mais vu que l'api put et post devraient logiquement être reservées a des gestionnaires tout va bien
        $requete = "UPDATE $table SET ";
        while(count($clefs)>1 && count($valeurs) >1 ){ // un seul count devrait suffire car les array seront modifiés en meme temps mais au cas ou on fait les deux
            $k = array_pop($clefs);
            $v = array_pop($valeurs);
            if(count($clefs) == 1 && count($valeurs) == 1){ //si on est sur la derniere boucle on ne met pas de virgule car rien ne suivra cette modification
                $ajout = "$k = \"$v\"";
            }
            /*
            Amélioration potentielle : préciser le type inséré pour assurer un bon traitement, actuellement tout est inséré entre guillement et ça marche pour les string et int
            mais je me dis que ça risque de deconner par exemple pour les dates, donc c'est passable mais si on met une ptite lettre collée à la valeur ou la clef ça peut
            permettre d'adapter le traitement
            */
            else{
                $ajout = "$k = \"$v\", ";   
            }
            
            $requete = $requete . $ajout;
        }
        $clef = array_pop($clefs);
        $valDonnee=array_pop($valeurs);
        $fin = " WHERE $clef=$valDonnee";
        $requete=$requete . $fin;
        echo "$requete";
        echo "<br/>";
        try {
            $rowsAffected = Connexion::pdo()->exec($requete);
            echo "Mise à jour réussie. Nombre de lignes affectées : $rowsAffected";
        } catch (PDOException $e) {
            echo "Erreur lors de l'exécution de la requête : " . $e->getMessage();
        }

    }
    public static function post(string $table,array $valeurs,array $clefs=NULL){
        $requete = "";
        $baseRequete = "INSERT INTO $table ";
        if($clefs!=NULL){
            $cpt = 0;
            $baseRequete = $baseRequete . "( ";
            foreach($clefs as $clefAInsert){
                if ($cpt==0){ $baseRequete=$baseRequete . "`$clefAInsert`";}
                else{$baseRequete=$baseRequete . ", `$clefAInsert`";}
                $cpt++;
            }
            $baseRequete=$baseRequete . ") ";
        }
        $baseRequete = $baseRequete . "VALUES ";
        $valeurRequete="";
        $valeurRequete = $valeurRequete . "( ";
        $cpt=0;
        foreach($valeurs as $valAInsert){
            if ($cpt==0){ $valeurRequete=$valeurRequete . "\"$valAInsert\"";}
            else{$valeurRequete=$valeurRequete . ", \"$valAInsert\"";}
            $cpt++;
        }
        $valeurRequete=$valeurRequete . ") ";
        $requete = $baseRequete . $valeurRequete;
        echo $requete;
        try {
            $rowsAffected = Connexion::pdo()->exec($requete);
            echo "Mise à jour réussie. Nombre de lignes affectées : $rowsAffected";
        } catch (PDOException $e) {
            echo "Erreur lors de l'exécution de la requête : " . $e->getMessage();
        }
    }
}

?>