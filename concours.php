<?php
// Le fichier de stockage JSON
$json_file = 'participants.json'; 
$message = '';
$participants = [];

// Tenter de lire les participants existants pour le traitement du formulaire ET l'affichage du bonus
if (file_exists($json_file) && filesize($json_file) > 0) {
    $json_data = file_get_contents($json_file);
    $participants = json_decode($json_data, true);
    if ($participants === null) {
        $participants = []; // S'assurer que c'est un tableau même s'il y a une erreur de décodage
    }
}

// --- LOGIQUE DE TRAITEMENT DU FORMULAIRE (IDENTIQUE À LA VERSION PRÉCÉDENTE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = htmlspecialchars($_POST['email'] ?? '');

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $file = $_FILES['photo'];
        $upload_dir = 'uploads/';
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $prenom . '_' . $nom . '_' . time() . '.' . $extension;
        $destination = $upload_dir . $filename;
        $photo_path_save = $destination; 

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            
            // Créer la nouvelle entrée
            $nouvel_participant = [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'photo_path' => $photo_path_save,
                'date_soumission' => date('Y-m-d H:i:s')
            ];

            // Ajouter la nouvelle entrée
            $participants[] = $nouvel_participant;

            // Sauvegarder toute la liste dans le fichier JSON
            if (file_put_contents($json_file, json_encode($participants, JSON_PRETTY_PRINT)) !== false) {
                $message = "✅ Participation enregistrée avec succès !";
            } else {
                $message = "❌ Erreur lors de l'écriture dans le fichier JSON. Vérifie les permissions.";
            }

        } else {
            $message = "❌ Erreur lors du déplacement du fichier vers 'uploads/'.";
        }
    } else {
        $message = "❌ Veuillez sélectionner une photo.";
    }
}
// ---------------------------------------------------------------------------------

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Concours Photo - Le Super Défi GitHub</title>
    <style>
        /* Styles simples pour le Bonus */
        #photo-viewer {
            width: 400px;
            margin: 20px auto;
            text-align: center;
            border: 2px solid #333;
            padding: 10px;
        }
        #current-photo {
            max-width: 100%;
            height: auto;
            display: block;
            margin-bottom: 10px;
        }
        #navigation button {
            padding: 10px 15px;
            cursor: pointer;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <header>
        <h1>Page du Concours Photo</h1>
        <nav>
            <a href="index.html">Retour à l'Accueil</a>
        </nav>
    </header>

    <main>
        <h2>Formulaire de Participation</h2>
        <?php if ($message): ?>
            <p style="padding: 10px; border: 1px solid black;"><?= $message ?></p>
        <?php endif; ?>
        <form action="concours.php" method="post" enctype="multipart/form-data">
            <p><label for="nom">Nom :</label><input type="text" id="nom" name="nom" required></p>
            <p><label for="prenom">Prénom :</label><input type="text" id="prenom" name="prenom" required></p>
            <p><label for="email">Email (Optionnel) :</label><input type="email" id="email" name="email"></p>
            <p><label for="photo">Sélectionnez votre Photo :</label><input type="file" id="photo" name="photo" accept="image/*" required></p>
            <p><button type="submit">Envoyer la Participation</button></p>
        </form>

        <hr>

        <h2>🏆 Bonus Super Défi : Aperçu des Photos</h2>
        
        <?php if (count($participants) > 0): ?>
        
            <div id="photo-viewer">
                <div id="navigation">
                    <button id="prevBtn" disabled> &lt; </button>
                    <span id="counter">1 / <?= count($participants) ?></span>
                    <button id="nextBtn" disabled> &gt; </button>
                </div>
                <img id="current-photo" src="" alt="Photo du participant">
                <p>Soumis par : <strong id="current-author"></strong></p>
            </div>

            <script>
                // Récupérer les données PHP dans une variable JavaScript
                const PHOTOS = <?= json_encode($participants) ?>;
                let currentIndex = PHOTOS.length - 1; // Commencer par la dernière photo soumise

                const photoImg = document.getElementById('current-photo');
                const authorText = document.getElementById('current-author');
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');
                const counterSpan = document.getElementById('counter');

                /** Met à jour l'affichage de la photo actuelle **/
                function updateViewer() {
                    const currentPhoto = PHOTOS[currentIndex];
                    // Définit le chemin de l'image
                    photoImg.src = currentPhoto.photo_path;
                    // Définit le nom de l'auteur
                    authorText.textContent = currentPhoto.prenom + ' ' + currentPhoto.nom;
                    // Met à jour le compteur
                    counterSpan.textContent = (currentIndex + 1