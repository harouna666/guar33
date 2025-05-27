<?php
// Connexion PDO
$host = 'localhost';
$db   = 'u68662';
$user = 'u68662';
$pass = '4769335';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erreur de connexion DB : " . $e->getMessage());
}

// Fonction pour valider FIO (lettres cyrilliques, latines, espaces)
function validateFullname($str) {
    return preg_match('/^[\p{L}\s]+$/u', $str) && mb_strlen($str) <= 150;
}

// Récupération & validation des données
$errors = [];

$fullname = $_POST['fullname'] ?? '';
if (!validateFullname($fullname)) {
    $errors[] = "ФИО doit contenir uniquement des lettres et espaces, max 150 caractères.";
}

$phone = $_POST['phone'] ?? '';
if (!preg_match('/^[\d\s\+\-\(\)]+$/', $phone)) {
    $errors[] = "Téléphone invalide.";
}

$email = $_POST['email'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "E-mail invalide.";
}

$birthdate = $_POST['birthdate'] ?? '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
    $errors[] = "Date de naissance invalide.";
}

$gender = $_POST['gender'] ?? '';
if (!in_array($gender, ['male', 'female', 'other'])) {
    $errors[] = "Genre invalide.";
}

$languages = $_POST['languages'] ?? [];
$allowed_languages = ["Pascal", "C", "C++", "JavaScript", "PHP", "Python", "Java", "Haskell", "Clojure", "Prolog", "Scala", "Go"];
if (!is_array($languages) || count(array_intersect($languages, $allowed_languages)) !== count($languages)) {
    $errors[] = "Langages invalides sélectionnés.";
}

$bio = $_POST['bio'] ?? '';

$agreed_contract = isset($_POST['agreed_contract']) ? true : false;
if (!$agreed_contract) {
    $errors[] = "Vous devez accepter le contrat.";
}

if (!empty($errors)) {
    echo "<div style='color:red;'><strong>Erreurs :</strong><ul>";
    foreach ($errors as $err) {
        echo "<li>" . htmlspecialchars($err) . "</li>";
    }
    echo "</ul></div>";
    echo "<a href='javascript:history.back()'>Retour au formulaire</a>";
    exit;
}

// Insertion en base avec transaction
try {
    $pdo->beginTransaction();

    $stmtUser = $pdo->prepare("INSERT INTO users (fullname, phone, email, birthdate, gender, bio, agreed_contract) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtUser->execute([$fullname, $phone, $email, $birthdate, $gender, $bio, $agreed_contract]);

    $userId = $pdo->lastInsertId();

    $stmtLang = $pdo->prepare("INSERT INTO languages (user_id, language) VALUES (?, ?)");
    foreach ($languages as $lang) {
        $stmtLang->execute([$userId, $lang]);
    }

    $pdo->commit();

    echo "<div style='color:green;'>Données enregistrées avec succès !</div>";
    echo "<a href='index.html'>Retour au formulaire</a>";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "<div style='color:red;'>Erreur lors de l'enregistrement : " . htmlspecialchars($e->getMessage()) . "</div
