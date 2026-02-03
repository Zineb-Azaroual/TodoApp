<?php
// Script qui affiche les taches
session_start();
require("BaseDeDonnees.php");
global $conn;

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['idUtilisateur'])) {
    // Rediriger vers la page de connexion
    header("Location: Login_Page.php");
    exit();
}

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['idUtilisateur'];

// Traiter les actions (terminer/réactiver/supprimer tâche)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['idTache'])) {
        $taskId = intval($_POST['idTache']);
        $action = $_POST['action'];
        $activeTab = isset($_POST['tab']) ? $_POST['tab'] : 'active';

        // Vérifier que la tâche existe et appartient à l'utilisateur
        $checkSql = "SELECT idTache FROM Taches WHERE idTache = ? AND idUtilisateur = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $taskId, $user_id);
        $checkStmt->execute();
        $taskExists = ($checkStmt->get_result()->num_rows > 0);
        $checkStmt->close();

        if (!$taskExists) {
            $_SESSION['errors'] = "❌ Cette tâche n'existe pas ou ne vous appartient pas.";
        } else {
            switch ($action) {
                case 'complete':
                    // Marquer comme terminée
                    $sql = "UPDATE Taches SET statusTache = 1 WHERE idTache = ? AND idUtilisateur = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ii", $taskId, $user_id);

                    if ($stmt->execute() && $stmt->affected_rows > 0) {
                        $_SESSION['success'] = "✅ Tâche marquée comme terminée avec succès.";
                    } else {
                        $_SESSION['errors'] = "❌ Erreur lors de la mise à jour du statut.";
                    }
                    $stmt->close();
                    break;

                case 'reactivate':
                    // Réactiver la tâche
                    $sql = "UPDATE Taches SET statusTache = 0 WHERE idTache = ? AND idUtilisateur = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ii", $taskId, $user_id);

                    if ($stmt->execute() && $stmt->affected_rows > 0) {
                        $_SESSION['success'] = "✅ Tâche réactivée avec succès.";
                    } else {
                        $_SESSION['errors'] = "❌ Erreur lors de la réactivation.";
                    }
                    $stmt->close();
                    break;

                case 'delete':
                    // Supprimer la tâche
                    $sql = "DELETE FROM Taches WHERE idTache = ? AND idUtilisateur = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ii", $taskId, $user_id);

                    if ($stmt->execute() && $stmt->affected_rows > 0) {
                        $_SESSION['success'] = "✅ Tâche supprimée avec succès.";
                    } else {
                        $_SESSION['errors'] = "❌ Erreur lors de la suppression.";
                    }
                    $stmt->close();
                    break;
            }
        }

        // Rediriger pour éviter les soumissions multiples
        header("Location: Select.php?tab=" . $activeTab);
        exit();
    }
}

// Récupérer l'onglet actif
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'active';

// Validation de l'onglet
if (!in_array($activeTab, ['all', 'active', 'completed', 'expired'])) {
    $activeTab = 'active';
}

// Construire la requête SQL en fonction de l'onglet actif
$sqlCondition = "WHERE idUtilisateur = ?";
$params = array($user_id);
$paramTypes = "i";

switch ($activeTab) {
    case 'all':
        // Toutes les tâches
        break;
    case 'active':
        // Tâches actives (non terminées et non expirées)
        $sqlCondition .= " AND statusTache = 0 AND dateTacheFin > NOW()";
        break;
    case 'completed':
        // Tâches terminées
        $sqlCondition .= " AND statusTache = 1";
        break;
    case 'expired':
        // Tâches expirées mais non terminées
        $sqlCondition .= " AND statusTache = 0 AND dateTacheFin <= NOW()";
        break;
}

// Requête SQL complète
$sql = "SELECT * FROM Taches $sqlCondition ORDER BY dateTacheDebut";
$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Afficher les messages de succès ou d'erreur si présents
if (isset($_SESSION['success'])) {
    $successMessage = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['errors'])) {
    $errorMessage = $_SESSION['errors'];
    unset($_SESSION['errors']);
}

// Compter le nombre de tâches dans chaque catégorie pour les badges
function countTasks($conn, $user_id, $condition) {
    $sql = "SELECT COUNT(*) AS count FROM Taches WHERE idUtilisateur = ? $condition";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['count'];
}

$allCount = countTasks($conn, $user_id, "");
$activeCount = countTasks($conn, $user_id, "AND statusTache = 0 AND dateTacheFin > NOW()");
$completedCount = countTasks($conn, $user_id, "AND statusTache = 1");
$expiredCount = countTasks($conn, $user_id, "AND statusTache = 0 AND dateTacheFin <= NOW()");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Todo-list</title>
    <link rel="stylesheet" href="Style.css"/>
    <!-- Ajout de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Ajout de Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .task-completed {
            background-color: rgba(40, 167, 69, 0.1);
        }
        .expired-task {
            background-color: rgba(220, 53, 69, 0.1);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 10px;
        }
        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .status-completed {
            background-color: #28a745;
            color: white;
        }
        .status-expired {
            background-color: #dc3545;
            color: white;
        }
        .nav-tabs .nav-link {
            color: #495057;
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            color: #256db4;
            border-bottom: 2px solid #256db4;
        }
        .badge {
            margin-left: 5px;
        }
    </style>
</head>
<body>
<div class="container mt-4" id = "Taches">
    <h1 class="text-center mb-4" style="color: #256db4; font-family: 'Times New Roman', serif;">
        <i class="fa-solid fa-clipboard-list"></i> Gestionnaire de tâches
    </h1>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success text-center">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger text-center">
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>

    <!-- Navigation par onglets -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab == 'all' ? 'active' : ''; ?>" href="Select.php?tab=all">
                <i class="fa-solid fa-list"></i> Toutes
                <span class="badge bg-secondary"><?php echo $allCount; ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab == 'active' ? 'active' : ''; ?>" href="Select.php?tab=active">
                <i class="fa-solid fa-spinner"></i> En cours
                <span class="badge bg-warning"><?php echo $activeCount; ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab == 'completed' ? 'active' : ''; ?>" href="Select.php?tab=completed">
                <i class="fa-solid fa-check-circle"></i> Terminées
                <span class="badge bg-success"><?php echo $completedCount; ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab == 'expired' ? 'active' : ''; ?>" href="Select.php?tab=expired">
                <i class="fa-solid fa-clock"></i> Expirées
                <span class="badge bg-danger"><?php echo $expiredCount; ?></span>
            </a>
        </li>
    </ul>

    <?php if ($result->num_rows > 0): ?>
        <div class="col-12">
            <table class="table table-bordered table-hover">
                <thead class="table-primary">
                <tr>
                    <th>Tâche</th>
                    <th>Date de début</th>
                    <th>Date de fin</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($row = $result->fetch_assoc()):
                    $isCompleted = $row['statusTache'] == 1;

                    // Vérifier si la tâche est expirée
                    $dateFin = new DateTime($row['dateTacheFin']);
                    $isExpired = $dateFin <= new DateTime() && !$isCompleted;

                    // Déterminer la classe de la ligne
                    if ($isCompleted) {
                        $rowClass = 'task-completed';
                        $statusClass = 'status-completed';
                        $statusText = 'Terminée';
                    } elseif ($isExpired) {
                        $rowClass = 'expired-task';
                        $statusClass = 'status-expired';
                        $statusText = 'Expirée';
                    } else {
                        $rowClass = '';
                        $statusClass = 'status-pending';
                        $statusText = 'En cours';
                    }
                    ?>
                    <tr class="<?php echo $rowClass; ?>">
                        <td><?php echo htmlspecialchars($row['TitreTache']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['dateTacheDebut'])); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['dateTacheFin'])); ?></td>
                        <td>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo $statusText; ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="idTache" value="<?php echo $row['idTache']; ?>">
                                <input type="hidden" name="tab" value="<?php echo $activeTab; ?>">

                                <?php if (!$isCompleted): ?>
                                    <button type="submit" name="action" value="complete" class="btn btn-success btn-sm" title="Marquer comme terminée">
                                        <i class="fa-solid fa-check"></i> Terminer
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="action" value="reactivate" class="btn btn-outline-success btn-sm" title="Marquer comme non terminée">
                                        <i class="fa-solid fa-rotate"></i> Réactiver
                                    </button>
                                <?php endif; ?>

                                <a href="Update.php?id=<?php echo $row['idTache']; ?>&tab=<?php echo $activeTab; ?>" class="btn btn-info btn-sm">
                                    <i class="fa-solid fa-edit"></i> Modifier
                                </a>

                                <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette tâche?');">
                                    <i class="fa-solid fa-trash-can"></i> Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="fa-solid fa-info-circle"></i>
            <?php
            switch ($activeTab) {
                case 'all':
                    echo "Vous n'avez pas encore de tâches.";
                    break;
                case 'active':
                    echo "Vous n'avez pas de tâches en cours.";
                    break;
                case 'completed':
                    echo "Vous n'avez pas de tâches terminées.";
                    break;
                case 'expired':
                    echo "Vous n'avez pas de tâches expirées.";
                    break;
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="./Insert.php" class="btn btn-primary" style="--bs-primary: #256db4;--bs-primary-rgb: 37,109,180;background: #256db4;">
            <i class="fa-solid fa-plus"></i> Ajouter tâche
        </a>
    </div>
</div>

<!-- Scripts Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>