<?php
    Class Groupe{
        private int $idGroupe;
        private string $nomGroupe;
        private bool $voteBlancCompte;
        private ?string $lienPhotoIcone;    // ? ⇒ nullable
        private ?string $lienPhotoBanniere;    // ? ⇒ nullable
        private int $nbMembres;
        private array $listeVote;
        private array $listeEtiquette;

        public const DEFAULT_LIEN_PHOTO_ICONE = 'media/filled-default-group-icon-1600.png';

        
        public function get($attribute){
            return $this->$attribute;
        }

        public function set($attribute, $val){
            $this->$attribute = $val;
        }

        public function __construct($idGroupe=NULL, $nomGroupe=NULL){
            if(!is_null($idGroupe)){
                $this->idGroupe = $idGroupe;
                $this->nomGroupe = $nomGroupe;
            }
        }

        public static function getGroupe(int $idGroupe){
            $requete = "SELECT idGroupe, nomGroupe, voteBlancCompte, lienPhotoIcone, lienPhotoBanniere, COUNT(*) AS nbMembres
                        FROM Groupe
                        NATURAL JOIN Membre 
                        WHERE idGroupe = $idGroupe;";
            $resultat = Connexion::pdo()->query($requete);
            $resultat->setFetchmode(PDO::FETCH_CLASS,"Groupe");
            $groupe = $resultat->fetch();
            $groupe->listeVote = Vote::getVotesGroupe($idGroupe);

            if(is_null($groupe->lienPhotoIcone))
                $groupe->lienPhotoIcone = Groupe::DEFAULT_LIEN_PHOTO_ICONE;

            return $groupe;
        }
        
        public static function getGroupesUtilisateur(int $idUtilisateur){
            $requete = "SELECT G.idGroupe, nomGroupe, voteBlancCompte, lienPhotoIcone, lienPhotoBanniere, COUNT(*) AS nbMembres
                        FROM Groupe G
                        INNER JOIN Membre M1 ON G.idGroupe=M1.idGroupe 
                        INNER JOIN Membre M2 ON G.idGroupe=M2.idGroupe
                        WHERE M1.idUtilisateur = $idUtilisateur
                        GROUP BY G.idGroupe, nomGroupe, lienPhotoIcone;";

            $resultat = Connexion::pdo()->query($requete);
            $resultat->setFetchmode(PDO::FETCH_CLASS,"Groupe");

            $listeGroupes = $resultat->fetchAll();

            foreach($listeGroupes as $groupe){
                if(is_null($groupe->lienPhotoIcone))
                    $groupe->lienPhotoIcone = Groupe::DEFAULT_LIEN_PHOTO_ICONE;
                $groupe->fillEtiquettes();
                $groupe->listeVote = Vote::getVotesGroupe($groupe->idGroupe);
            }

            return $listeGroupes;
        }

        public static function getJSON(int $idGroupe){
            $requete = "SELECT idGroupe, nomGroupe FROM Groupe WHERE idGroupe = $idGroupe;";
            $resultat = Connexion::pdo()->query($requete);
            $resultat->setFetchmode(PDO::FETCH_CLASS,"Groupe");
            
            $data = $resultat->fetch(PDO::FETCH_ASSOC);

            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        public function fillEtiquettes(){
            $requete = "SELECT idEtiquette, labelEtiquette, couleur 
                        FROM Etiquette
                        WHERE idGroupe = $this->idGroupe";
    
            $resultat = Connexion::pdo()->query($requete);
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            
            $this->listeEtiquettes = $resultat->fetchAll();
        }
        
        // la description du groupe n'est pas attribut de la classe car string de taille conséquente et on l'affiche rarement
        public function getDescription(){
            $requete = "SELECT descriptionGroupe
                        FROM Groupe
                        WHERE idGroupe = $this->idGroupe;";
            $resultat = Connexion::pdo()->query($requete);
            
            return $resultat->fetch()["descriptionGroupe"];
        }


        // les regles du groupe ne sont pas attribut de la classe car string de taille conséquente et on les affiche rarement
        public function getRegles(){
            $requete = "SELECT reglesGroupe
                        FROM Groupe
                        WHERE idGroupe = $this->idGroupe;";
            $resultat = Connexion::pdo()->query($requete);

            return $resultat->fetch()["reglesGroupe"];;
        }
        public function getUrlRejoindre(){
            $id= $this->idGroupe;
            return "https://projets.iut-orsay.fr/saes3-vjacqu3/classePHP/controleur/rejoindreGroupe.php?idInvit=$id";
        }

        public function getRoleMembre($idU){
                $id= $this->idGroupe;
                $requetePreparee = Connexion::pdo()->prepare( "SELECT idRole FROM `Membre` WHERE idUtilisateur=:idU and idGroupe=$id");
                $requetePreparee -> bindParam(':idU',$idU);
                $requetePreparee->execute();
    
                return $requetePreparee->fetchColumn();
        }
        public function __toString(){
            return "<h3> Groupe </h3>
                    <p>
                    id : $this->idGroupe<br />
                    nom : $this->nomGroupe<br />
                    nbMembres : $this->nbMembres
                    </p>";
        }

        public function display(){
            echo $this;
            echo "<pre>";
            print_r($this->listeVote);
            echo "</pre>";
        }
    }
?>