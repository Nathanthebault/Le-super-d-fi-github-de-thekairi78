<?php
// Fichier de stockage JSON
$json_file = 'participants.json'; 
$message_soumission = '';
$message_vote = '';
$is_success_soumission = true;
$is_success_vote = true;
$participants = [];

// Tenter de lire les participants existants
if (file_exists($json_file) && filesize($json_file) > 0) {
    $json_data = file_get_contents($json_file);
    $participants = json_decode($json_data, true);
    if ($participants === null) {
        $participants = []; 
    }
}

// Fonction pour sauvegarder la liste complète dans le fichier JSON
function save_participants($data, $file) {
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT)) !== false;
}

// -----------------------------------------------------------
// --- GESTION DES FORMULAIRES ---
// -----------------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. GESTION DU FORMULAIRE DE VOTE
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'vote') {
        $voted_photo_path = $_POST['photo_path_vote'] ?? '';
        
        $found = false;
        foreach ($participants as $key => $participant) {
            if ($participant['photo_path'] === $voted_photo_path) {
                // Initialiser 'votes' à 0 si le champ n'existe pas
                if (!isset($participants[$key]['votes'])) {
                    $participants[$key]['votes'] = 0;
                }
                $participants[$key]['votes']++;
                $found = true;
                break;
            }
        }

        if ($found && save_participants($participants, $json_file)) {
            $message_vote = "Vote enregistré avec succès ! Merci de votre participation.";
            $is_success_vote = true;
        } else {
            $message_vote = "Erreur lors de l'enregistrement du vote ou photo non trouvée.";
            $is_success_vote = false;
        }
    }
    
    // 2. GESTION DU FORMULAIRE DE SOUMISSION DE PHOTO
    elseif (isset($_POST['action_type']) && $_POST['action_type'] === 'soumission') {
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
                
                // Créer la nouvelle entrée (avec le champ 'votes' initialisé à 0)
                $nouvel_participant = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'photo_path' => $photo_path_save,
                    'date_soumission' => date('Y-m-d H:i:s'),
                    'votes' => 0 
                ];

                $participants[] = $nouvel_participant;

                if (save_participants($participants, $json_file)) {
                    $message_soumission = "Participation enregistrée avec succès !";
                    $is_success_soumission = true;
                } else {
                    $message_soumission = "Erreur lors de l'écriture dans le fichier JSON. Vérifiez les permissions.";
                    $is_success_soumission = false;
                }
            } else {
                $message_soumission = "Erreur lors du déplacement du fichier vers 'uploads/'.";
                $is_success_soumission = false;
            }
        } else {
            $message_soumission = "Veuillez sélectionner une photo.";
            $is_success_soumission = false;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Concours Photo - Le Super Défi GitHub</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="fond-principal"> 
    <header class="entete-page">
        <h1>Concours Photo - Espace Naturel de la Motte</h1>
        <nav>
            <a href="index.html" class="bouton-action">Retour à l'Accueil</a> 
        </nav>
    </header>

    <main class="contenu-principal">
        <h2>📸 Règle du Concours (Photos d'Oiseaux)</h2>
        <p>Le thème de cette année est **"Les Oiseaux de la Motte"**. Envoyez votre plus belle photo d'oiseau prise dans l'ENS de la Motte. Une seule photo par participant. Les votes sont ouverts immédiatement après la soumission.</p>
        
        <hr>

        <h2>1. Soumettre votre Photo d'Oiseau</h2>
        <?php if ($message_soumission): ?>
            <p class="<?= $is_success_soumission ? 'message-succes' : 'message-erreur' ?>">
                <?= $message_soumission ?>
            </p>
        <?php endif; ?>

        <form action="concours.php" method="post" enctype="multipart/form-data" class="module-formulaire">
            <input type="hidden" name="action_type" value="soumission">
            
            <div class="groupe-champ">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            <div class="groupe-champ">
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" required>
            </div>
            <div class="groupe-champ">
                <label for="email">Email (Optionnel) :</label>
                <input type="email" id="email" name="email">
            </div>
            <div class="groupe-champ">
                <label for="photo">Sélectionnez votre Photo :</label>
                <input type="file" id="photo" name="photo" accept="image/*" required>
            </div>
            <p>
                <button type="submit" class="bouton-action">Envoyer la Participation</button>
            </p>
        </form>

        <hr>

        <h2>2. Voter pour les Photos !</h2>
        <?php if ($message_vote): ?>
            <p class="<?= $is_success_vote ? 'message-succes' : 'message-erreur' ?>">
                <?= $message_vote ?>
            </p>
        <?php endif; ?>

        <?php if (count($participants) > 0): ?>
            <div class="photo-list-vote">
                <?php foreach ($participants as $photo_data): ?>
                    <div class="vote-item">
                        <img src="<?= htmlspecialchars($photo_data['photo_path']) ?>" alt="Photo de <?= htmlspecialchars($photo_data['prenom']) ?>" >
                        <p>Soumis par : **<?= htmlspecialchars($photo_data['prenom']) ?>**</p>
                        <p>Votes actuels : **<?= $photo_data['votes'] ?? 0 ?>**</p>
                        
                        <form action="concours.php" method="post" class="groupe-champ">
                            <input type="hidden" name="action_type" value="vote">
                            <input type="hidden" name="photo_path_vote" value="<?= htmlspecialchars($photo_data['photo_path']) ?>">
                            <button type="submit" class="bouton-action">Voter pour cette photo</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Aucune photo soumise pour le moment. Soyez le premier à participer !</p>
        <?php endif; ?>

        <hr>

        <h2>🏆 Bonus : Aperçu Interactif (Dernières Soumissions)</h2>
        <?php if (count($participants) > 0): ?>
            <div id="photo-viewer">
                <div id="navigation">
                    <button id="prevBtn" class="bouton-action" disabled> &lt; </button>
                    <span id="counter">1 / <?= count($participants) ?></span>
                    <button id="nextBtn" class="bouton-action" disabled> &gt; </button>
                </div>
                <img id="current-photo" src="" alt="Photo du participant">
                <p>Soumis par : <strong id="current-author"></strong></p>
            </div>

            <script>
                // Récupérer les données PHP dans une variable JavaScript
                const PHOTOS = <?= json_encode($participants) ?>;
                let currentIndex = PHOTOS.length - 1; 

                const photoImg = document.getElementById('current-photo');
                const authorText = document.getElementById('current-author');
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');
                const counterSpan = document.getElementById('counter');

                function updateViewer() {
                    const currentPhoto = PHOTOS[currentIndex];
                    photoImg.src = currentPhoto.photo_path;
                    authorText.textContent = currentPhoto.prenom + ' ' + currentPhoto.nom;
                    counterSpan.textContent = (currentIndex + 1) + ' / ' + PHOTOS.length;

                    prevBtn.disabled = currentIndex === 0;
                    nextBtn.disabled = currentIndex === PHOTOS.length - 1;
                }

                nextBtn.addEventListener('click', () => {
                    if (currentIndex < PHOTOS.length - 1) {
                        currentIndex++;
                        updateViewer();
                    }
                });

                prevBtn.addEventListener('click', () => {
                    if (currentIndex > 0) {
                        currentIndex--;
                        updateViewer();
                    }
                });

                updateViewer();
            </script>
        <?php else: ?>
            <p>Aucune photo soumise pour le moment dans l'aperçu.</p>
        <?php endif; ?>
    </main>
    
    <footer class="pied-de-page">
        &copy; <?= date("Y") ?> Le Super Défi GitHub - Concours Photo
    </footer>
</body>
</html>