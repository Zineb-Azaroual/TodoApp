<?php
session_start();
require("BaseDeDonnees.php");
global $conn;

function verifierExistanceMail($mail, $password_input) {

    global $conn;
    // Utiliser les noms exacts des colonnes de votre base de données
    $stmt = $conn->prepare("SELECT idUtilisateur, emailUtilisateur, motDePasseUtilisateur FROM Utilisateur WHERE emailUtilisateur = ?");
    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // L'utilisateur existe
        $row = $result->fetch_assoc();

        // Vérifier si le mot de passe correspond
        // Note: idéalement, les mots de passe devraient être hachés, mais je m'adapte à votre structure actuelle
        if ($password_input == $row['motDePasseUtilisateur']) {
            // Mot de passe correct
            $_SESSION['idUtilisateur'] = $row['idUtilisateur'];
            return true;
        } else {
            // Mot de passe incorrect
            return false;
        }
    } else {
        // Utilisateur non trouvé
        return false;
    }
}

// Traitement du formulaire si soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['emailLogin'];
    $password_input = $_POST['mdpLogin'];

    if (verifierExistanceMail($email, $password_input)) {
        $_SESSION['success'] = "Connexion réussie!";
        header("Location: ./Select.php");
        exit();
    } else {
        $_SESSION['errors'] = "Email ou mot de passe incorrect.";
        // Rediriger vers la même page pour afficher l'erreur
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>VentasPro Login</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/SignIn.css">
</head>

<body>
<div id="main">
    <div class="text-center" id="info">
        <h1 class="text-center" style="color: #256db4;
        font-family: 'Times New Roman', Times, serif ,Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
        ">Todo-list<br></h1>

        <form class="text-start" id="form-login" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <?php if (isset($_SESSION['success'])) : ?>
                <div class="alert alert-success text-center">
                    <?php
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['errors'])) : ?>
                <div class="alert alert-danger text-center">
                    <?php
                    echo $_SESSION['errors'];
                    unset($_SESSION['errors']);
                    ?>
                </div>
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label" id="lbl-usuario" for="txt-usuario">Email</label>
                <input name="emailLogin" class="form-control" type="email" id="txt-usuario" placeholder="exemple@mail.abc" required>
            </div>
            <div class="mb-3">
                <label class="form-label" id="lbl-password" for="txt-password">Mot de passe</label>
                <input name="mdpLogin" class="form-control" type="password" id="txt-password" placeholder="Votre mot de passe" required>
            </div>
            <div class="text-center">
                <button class="btn btn-primary" type="submit" style="--bs-primary: #256db4;--bs-primary-rgb: 37,109,180;background: #256db4;">Se connecter</button>
                <a href="signUpPage.php" class="btn btn-primary" style="--bs-primary: #256db4;--bs-primary-rgb: 37,109,180;background: #256db4;">S'inscrire</a>
            </div>
        </form>
    </div>
</div>
<script src="../assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>