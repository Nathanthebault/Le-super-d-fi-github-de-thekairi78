<?php
// Définit le dossier où les photos seront enregistrées.
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// ----------------------------------------------------------------
// 1. Logique de soumission de photo (exemple simple - à sécuriser !)
// ----------------------------------------------------------------
$message_soumission = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_photo'])) {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $file_name = uniqid('photo_') . '_' . basename($_FILES['photo']['name']);
        $target_file = $upload_dir . $file_name;

        // Déplace le fichier téléchargé vers le dossier cible
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            // Ici, vous enregistreriez $target_file, le nom et le titre dans une base de données.
            $message_soumission = "<p class='message-succes'>✅ Photo soumise avec succès !</p>";
        } else {
            $message_soumission = "<p class='message-erreur'>❌ Erreur lors de l'enregistrement du fichier.</p>";
        }
    } else {
        $message_soumission = "<p class='message-erreur'>❌ Veuillez sélectionner un fichier valide.</p>";
    }
}

// ----------------------------------------------------------------
// 2. Logique de vote (exemple simple - nécessite une BD pour fonctionner)
// ----------------------------------------------------------------
$message_vote = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_vote'])) {
    $photo_id = isset($_POST['photo_id']) ? (int)$_POST['photo_id'] : 0;
    if ($photo_id > 0) {
        // Normalement : Vous incrémenteriez le compteur de votes pour cette photo dans votre base de données.
        $message_vote = "<p class='message-succes'>👍 Votre vote pour la photo ID {$photo_id} a été enregistré !</p>";
    } else {
        $message_vote = "<p class='message-erreur'>❌ Veuillez sélectionner une photo à voter.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🗳️ Concours Photo - Participer & Voter</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="fond-principal">
    <header class="entete-page">
        <h1>Page de Participation et de Vote</h1>
    </header>

    <nav>
        <ul>
            <li><a href="index.html">Accueil</a></li>
            <li><a href="concours.php">Participer & Voter</a></li>
        </ul>
    </nav>

    <main class="contenu-principal">
        
        <section class="module-formulaire">
            <h2>Soumettre une Photo</h2>
            <?php echo $message_soumission; // Affiche le message de soumission ?>
            <form action="concours.php" method="POST" enctype="multipart/form-data">
                <div class="groupe-champ">
                    <label for="nom">Votre Nom/Pseudo :</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="groupe-champ">
                    <label for="titre">Titre de la Photo :</label>
                    <input type="text" id="titre" name="titre" required>
                </div>
                <div class="groupe-champ">
                    <label for="photo">Sélectionner la Photo (Max 5MB) :</label>
                    <input type="file" id="photo" name="photo" accept="image/jpeg, image/png" required>
                </div>
                <button type="submit" name="submit_photo" class="bouton-action">Envoyer ma Photo</button>
            </form>
        </section>

        <hr>
        
        <section class="module-formulaire">
            <h2>Voter pour une Photo</h2>
            <?php echo $message_vote; // Affiche le message de vote ?>
            <p>Sélectionnez la photo pour laquelle vous souhaitez voter. (Ceci est un exemple, l'affichage des photos réelles nécessiterait une base de données).</p>

            <form action="concours.php" method="POST">
                <div class="groupe-champ">
                    <label for="photo_id">Sélectionner la Photo :</label>
                    <select id="photo_id" name="photo_id" required>
                        <option value="">-- Choisir une photo --</option>
                        <option value="1">Photo 1 - "Goutte de Rosée"</option>
                        <option value="2">Photo 2 - "Ombre et Lumière"</option>
                        <option value="3">Photo 3 - "Le Métro Vert"</option>
                    </select>
                </div>
                <button type="submit" name="submit_vote" class="bouton-action">Voter</button>
            </form>
        </section>

    </main>

    <footer class="pied-de-page">
        <p>&copy; 2025 Votre Organisation - Merci de votre participation !</p>
    </footer>
</body>
</html>