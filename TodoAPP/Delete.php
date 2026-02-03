<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['idUtilisateur'])) {
    // Rediriger vers la page de connexion si non connecté
    $_SESSION['errors'] = "Veuillez vous connecter pour supprimer une tâche.";
    header("Location: Login_Page.php");
    exit();
}

require("BaseDeDonnees.php");
global $conn;

// Vérifier si l'ID est fourni dans l'URL (méthode GET)
if (isset($_GET['id'])) {
    // Récupérer et convertir l'ID en entier pour s'assurer qu'il s'agit d'un nombre
    $idTache = intval($_GET['id']);
    $idUtilisateur = $_SESSION['idUtilisateur'];

    // Vérifier d'abord si la tâche existe et appartient à l'utilisateur
    $checkSql = "SELECT idTache FROM Taches WHERE idTache = ? AND idUtilisateur = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $idTache, $idUtilisateur);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        $_SESSION['errors'] = "❌ Cette tâche n'existe pas ou ne vous appartient pas.";
        $checkStmt->close();
        header("Location: Select.php");
        exit();
    }
    $checkStmt->close();

    // Procéder à la suppression
    $sql = "DELETE FROM Taches WHERE idTache = ? AND idUtilisateur = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idTache, $idUtilisateur);

    if ($stmt->execute()) {
        // Vérifier si des lignes ont été affectées
        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "✅ Tâche supprimée avec succès.";
        } else {
            $_SESSION['errors'] = "❌ Erreur: Aucune ligne n'a été supprimée.";
        }
    } else {
        $_SESSION['errors'] = "❌ Erreur lors de la suppression de la tâche: " . $stmt->error;
    }

    $stmt->close();

    // Rediriger vers la page d'index
    header("Location: ./Select.php");
    exit();
} else {
    // Cas où l'ID n'est pas fourni
    $_SESSION['errors'] = "ID de tâche manquant.";
    header("Location: ./Select.php");
    exit();
}
?>