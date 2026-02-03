<?php
session_start();

if (!isset($_SESSION['idUtilisateur'])) {
    // Rediriger vers la page de connexion si non connecté
    $_SESSION['errors'] = "Veuillez vous connecter pour modifier une tâche.";
    header("Location: Login_Page.php");
    exit();
}
require("BaseDeDonnees.php");
global $conn;

// Traitement du formulaire soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $idTache = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $titre = isset($_POST['titre']) ? trim($_POST['titre']) : '';
    $dateDebut = isset($_POST['datedebut']) ? $_POST['datedebut'] : '';
    $dateFin = isset($_POST['datefin']) ? $_POST['datefin'] : '';
    $idUtilisateur = $_SESSION['idUtilisateur'];

    // Valider les données
    if (empty($titre) || empty($dateDebut) || empty($dateFin)) {
        $_SESSION['errors'] = "Tous les champs sont obligatoires.";
    } else {
        // Mettre à jour la tâche
        $sql = "UPDATE Taches SET TitreTache=?, dateTacheDebut=?, dateTacheFin=? WHERE idTache=? AND idUtilisateur=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $titre, $dateDebut, $dateFin, $idTache, $idUtilisateur);

        if ($stmt->execute()) {
            $_SESSION['success'] = "✅ Tâche mise à jour avec succès.";
            header("Location: Select.php");
            exit();
        } else {
            $_SESSION['errors'] = "❌ Erreur lors de la mise à jour de la tâche: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Récupération des données de la tâche à modifier
if (isset($_GET["id"])) {
    $idTache = intval($_GET['id']);
    $idUtilisateur = $_SESSION['idUtilisateur'];

    $sql = "SELECT * FROM Taches WHERE idTache=? AND idUtilisateur=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idTache, $idUtilisateur);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['errors'] = "Tâche introuvable !";
        header("Location: Select.php");
        exit();
    }

    $row = $result->fetch_assoc();
    $stmt->close();
} else {
    $_SESSION['errors'] = "ID de tâche manquant.";
    header("Location: Select.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une tâche</title>
    <link rel="stylesheet" href="Style.css"/>
    <!-- Ajout de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Ajout de Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container mt-4">
    <h1 class="text-center mb-4" style="color: #256db4; font-family: 'Times New Roman', serif;">Modifier une tâche</h1>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <form action="Update.php?id=<?php echo $idTache; ?>" method="POST">
                <fieldset class="border p-4 rounded">
                    <div class="mb-3">
                        <label for="titre" class="form-label">Tâche à modifier :</label>
                        <input id="titre" class="form-control" value="<?php echo htmlspecialchars($row['TitreTache']); ?>" type="text" placeholder="Titre de la tâche" maxlength="200" name="titre" required />
                    </div>

                    <div class="mb-3">
                        <label for="datedebut" class="form-label">Date début :</label>
                        <input id="datedebut" class="form-control" type="datetime-local"
                               value="<?php echo date('Y-m-d\TH:i', strtotime($row['dateTacheDebut'])); ?>"
                               name="datedebut" required>
                    </div>

                    <div class="mb-3">
                        <label for="datefin" class="form-label">Date fin :</label>
                        <input id="datefin" class="form-control" type="datetime-local"
                               value="<?php echo date('Y-m-d\TH:i', strtotime($row['dateTacheFin'])); ?>"
                               name="datefin" required>
                    </div>
                </fieldset>

                <div class="d-flex justify-content-center mt-3">
                    <button class="btn btn-primary me-2" type="submit" style="--bs-primary: #256db4; --bs-primary-rgb: 37,109,180; background: #256db4;">
                        <i class="fa-solid fa-save"></i> Mettre à jour
                    </button>
                    <a href="Select.php" class="btn btn-secondary">
                        <i class="fa-solid fa-times"></i> Retour
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>