
<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['idUtilisateur'])) {
    $_SESSION['errors'] = "Veuillez vous connecter pour ajouter une tâche.";
    header("Location: Login_Page.php");
    exit();
}

require("BaseDeDonnees.php");
global $conn;

// Obtenir la date et l'heure actuelles au format MySQL
$now = date('Y-m-d H:i:s');
// Format pour l'attribut "min" des inputs datetime-local
$nowForInput = date('Y-m-d\TH:i');

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $titre = isset($_POST['titre']) ? trim($_POST['titre']) : '';
    $dateDebut = isset($_POST['datedebut']) ? $_POST['datedebut'] : '';
    $dateFin = isset($_POST['datefin']) ? $_POST['datefin'] : '';

    $erreurs = [];

    // Validation des champs obligatoires
    if (empty($titre)) {
        $erreurs[] = "Le titre de la tâche est obligatoire.";
    }

    if (empty($dateDebut)) {
        $erreurs[] = "La date de début est obligatoire.";
    }

    if (empty($dateFin)) {
        $erreurs[] = "La date de fin est obligatoire.";
    }

    // Si tous les champs sont remplis, valider les dates
    if (empty($erreurs)) {
        // Convertir les dates pour comparaison
        $dateDebutObj = new DateTime($dateDebut);
        $dateFinObj = new DateTime($dateFin);
        $nowObj = new DateTime($now);

        // Vérifier que la date de début est supérieure à maintenant
        if ($dateDebutObj <= $nowObj) {
            $erreurs[] = "La date de début doit être supérieure à l'heure actuelle.";
        }

        // Vérifier que la date de fin est supérieure à la date de début
        if ($dateFinObj <= $dateDebutObj) {
            $erreurs[] = "La date de fin doit être supérieure à la date de début.";
        }
    }

    if (empty($erreurs)) {
        $status = 0; // 0 pour false, 1 pour true
        $idUtilisateur = $_SESSION['idUtilisateur'];

        $sql = "INSERT INTO Taches (TitreTache, dateTacheDebut, DateTacheFin, statusTache, idUtilisateur) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $titre, $dateDebut, $dateFin, $status, $idUtilisateur);

        if ($stmt->execute()) {
            $_SESSION['success'] = "✅ Tâche ajoutée avec succès !";
            header("Location: ./Select.php");
            exit();
        } else {
            $erreurs[] = "Erreur lors de l'ajout de la tâche : " . $stmt->error;
        }
        $stmt->close();
    }

    // S'il y a des erreurs, les stocker dans la session
    if (!empty($erreurs)) {
        $_SESSION['errors'] = "❌ " . implode("<br>❌ ", $erreurs);
    }
}

// Information utilisateur actuel (pour référence)
$currentUser = $_SESSION['idUtilisateur'].'ismailboulahya';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Todo-list</title>
    <link rel="stylesheet" href="Style.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
<div>
    <h1 style="color: #256db4;
        font-family: 'Times New Roman', Times, serif, Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
        text-align: center">Todo-list<br></h1>
</div>

<!-- Affichage de l'utilisateur connecté -->
<div class="text-center mb-3">
    <small class="text-muted">Connecté en tant que: <?php echo htmlspecialchars($currentUser); ?></small>
</div>

<!-- Affichage des messages d'erreur s'il y en a -->
<?php if(isset($_SESSION['errors'])): ?>
    <div class="alert alert-danger text-center">
        <?php
        echo $_SESSION['errors'];
        unset($_SESSION['errors']);
        ?>
    </div>
<?php endif; ?>

<!-- Affichage des messages de succès s'il y en a -->
<?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success text-center">
        <?php
        echo $_SESSION['success'];
        unset($_SESSION['success']);
        ?>
    </div>
<?php endif; ?>

<h1 style="font-size: large; color: black;">Taches</h1>
<div class="text-center" id="cc" style="text-align: center">
    <form method="POST" action="Insert.php" novalidate>
        <fieldset>
            <div class="mb-3 div1" style="text-align: center">
                <span> </span>
                <span>
                    <label for="titre" class="form-label required-field">Tache a ajouter :</label>
                    <input class="form-control" type="text" placeholder="Tache á ajouter" maxlength="200" name="titre" required />
                </span>
            </div>
            <div class="mb-3 div1">
                <span>
                    <label for="datedebut" class="form-label required-field">Date debut :</label>
                    <input class="form-control" type="datetime-local" min="<?php echo $nowForInput; ?>" placeholder="dd-mm-yyyy" name="datedebut" required>
                </span>
            </div>
            <div class="mb-3 div1">
                <span>
                    <label name="datefin" class="form-label required-field">Date fin :</label>
                    <input class="form-control" type="datetime-local" min="<?php echo $nowForInput; ?>" placeholder="dd-mm-yyyy" name="datefin" required>
                </span>
            </div>
            <div>
                <small class="text-danger">Tous les champs marqués d'un * sont obligatoires</small>
            </div>
        </fieldset>
        <div>
            <span>
                <button class="btn btn-primary" type="submit" style="--bs-primary: #256db4;--bs-primary-rgb: 37,109,180;background: #256db4;">
                    <i class="fa-solid fa-plus"></i> Ajouter
                </button>
                <a href="Select.php" class="btn btn-primary" style="--bs-primary: #256db4;--bs-primary-rgb: 37,109,180;background: #256db4;">
                    <i class="fa-solid fa-times"></i> Annuler
                </a>
            </span>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>