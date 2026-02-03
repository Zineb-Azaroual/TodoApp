<?php
session_start();
$errors = [];
require("BaseDeDonnees.php");
global $conn;

function validationMotDePasse($motDePasse) {
    // Vérifier qu'il contient au moins une lettre
    $contientLettre = preg_match('/[a-zA-Z]/', $motDePasse);

    // Vérifier qu'il contient au moins un chiffre
    $contientChiffre = preg_match('/[0-9]/', $motDePasse);

    // Retourner vrai si les deux conditions sont remplies
    return $contientLettre && $contientChiffre;
}

function validerEmail($email) {
    $pattern = '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    return preg_match($pattern, $email);
}

if ($_SERVER['REQUEST_METHOD'] == "POST" &&
    isset($_POST['nom']) &&
    isset($_POST['prenom']) &&
    isset($_POST['mail']) &&
    isset($_POST['mdp']) &&
    isset($_POST['conmdp'])) {

    $nom = trim(htmlspecialchars($_POST['nom']));
    $prenom = trim(htmlspecialchars($_POST['prenom']));
    $email = trim(filter_var($_POST['mail'], FILTER_SANITIZE_EMAIL));
    $mdp = $_POST['mdp'];
    $conmdp = $_POST['conmdp'];

    // Vérifier que les mots de passe correspondent
    if ($mdp != $conmdp) {
        $_SESSION['errors'] = "Les mots de passe ne correspondent pas";
        header("location: inscription_form.php");
        exit();
    }

    if (!validerEmail($email)) {
        $_SESSION['errors'] = "L'email doit respecter le format demandé";
        header("location: inscription_form.php");
        exit();
    }

    if (!validationMotDePasse($mdp)) {
        $_SESSION['errors'] = "Le mot de passe doit contenir des lettres et des chiffres !";
        header("location: inscription_form.php");
        exit();
    }

    // Vérifier si l'email existe déjà
    $checkEmail = $conn->prepare("SELECT COUNT(*) AS count FROM Utilisateur WHERE emailUtilisateur = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();
    $row = $result->fetch_assoc();
    $checkEmail->close();

    if ($row['count'] > 0) {
        $_SESSION['errors'] = "Cette adresse email est déjà utilisée";
        header("location: inscription_form.php");
        exit();
    }

    // Hacher le mot de passe
    //$mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);



    // Préparer la requête (adaptez les champs selon votre schéma réel)
    $stmt = $conn->prepare("INSERT INTO Utilisateur (nomUtilisateur, prenomUtilisateur, emailUtilisateur, motDePasseUtilisateur) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nom, $prenom, $email, $mdp);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Utilisateur ajouté avec succès";
        $stmt->close();
        header("location: ./Login_Page.php");
        exit();
    } else {
        $_SESSION['errors'] = "Erreur lors de l'inscription : " . $conn->error;
        $stmt->close();
        header("location: ./signUpPage.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html data-bs-theme="light" lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Todo-list - Inscription</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/SignUp.css">
    <style>
        #main{

        }
    </style>
</head>

<body>
<div id="main">
    <div class="text-center" id="info">
        <h1 class="text-center" style="color: #256db4; font-family: 'Times New Roman', Times, serif, Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;">Todo-list<br></h1>

        <form action="signUpPage.php" method="POST" class="text-start" id="form-login">

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
<div >
            <div class="mb-3"><label class="form-label" id="lbl-usuario1" for="txt-usuario1">Prénom</label><input name="prenom" class="form-control" type="text" id="txt-usuario1" placeholder="Prénom" required></div>
            <div class="mb-3"><label class="form-label" id="lbl-usuario2" for="txt-usuario2">Nom</label><input name="nom" class="form-control" type="text" id="txt-usuario2" placeholder="Nom" required></div>
            <div class="mb-3"><label class="form-label" id="lbl-usuario3" for="txt-usuario3">Email</label><input name="mail" class="form-control" type="email" id="txt-usuario3" placeholder="exemple@mail.abc" required></div>
            <div class="mb-3"><label class="form-label" id="lbl-password4" for="txt-password4">Mot de passe</label><input name="mdp" class="form-control" type="password" id="txt-password4" placeholder="Entre 8 et 15 caractères" minlength="8" maxlength="15" required></div>
            <div class="mb-3"><label class="form-label" id="lbl-password5" for="txt-password5">Confirmer mot de passe</label><input name="conmdp" class="form-control" type="password" id="txt-password5" placeholder="Entre 8 et 15 caractères" minlength="8" maxlength="15" required></div>
            <div class="text-center">
                <button class="btn btn-primary" type="submit" style="--bs-primary: #256db4;--bs-primary-rgb: 37,109,180;background: #256db4;">S'inscrire</button>
            </div>
            <div class="mt-3 text-center">
                <a href="./Login_Page.php"> <button class="btn btn-primary" type="submit" style="--bs-primary: #256db4;--bs-primary-rgb: 37,109,180;background: #256db4;">Se connecter</button></a>
            </div>
        </form>
    </div>
</div>
</div>
<script src="../assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>