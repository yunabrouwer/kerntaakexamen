<?php
require_once 'includes/functions.php';

$vestiging_id = isset($_GET['vestiging_id']) ? (int)$_GET['vestiging_id'] : 1;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = isset($_POST['score']) ? (int)$_POST['score'] : 0;
    $categorie = isset($_POST['categorie']) ? $_POST['categorie'] : '';
    $toelichting = isset($_POST['toelichting']) ? $_POST['toelichting'] : '';

    // Validatie: alles is verplicht
    if ($score < 1 || $score > 5 || empty($categorie) || empty($toelichting)) {
        $error = 'Alle velden zijn verplicht. Vul de survey volledig in.';
    } else {
        // Sla direct op in het tekstbestand
        save_feedback($score, $categorie, $toelichting, $vestiging_id);
        header("Location: thanks.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TableTalk - Feedback</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; margin: 0; padding: 20px; display: flex; justify-content: center; }
        .container { max-width: 400px; width: 100%; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2E7D32; }
        .error { color: red; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .rating { display: flex; justify-content: space-between; flex-direction: row-reverse; margin-bottom: 15px; }
        .rating input { display: none; }
        .rating label { font-size: 30px; color: #ccc; cursor: pointer; }
        .rating input:checked ~ label, .rating label:hover, .rating label:hover ~ label { color: #2E7D32; }
        button { width: 100%; background: #2E7D32; color: white; padding: 12px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
    </style>
</head>
<body>
<div class="container">
    <h2>TableTalk</h2>
    <p style="text-align:center;">Uw mening telt bij <strong>Proef Restaurant</strong></p>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>Hoe was uw ervaring? (1-5 sterren)</label>
            <div class="rating">
                <input type="radio" id="star5" name="score" value="5"><label for="star5">★</label>
                <input type="radio" id="star4" name="score" value="4"><label for="star4">★</label>
                <input type="radio" id="star3" name="score" value="3"><label for="star3">★</label>
                <input type="radio" id="star2" name="score" value="2"><label for="star2">★</label>
                <input type="radio" id="star1" name="score" value="1"><label for="star1">★</label>
            </div>
        </div>

        <div class="form-group">
            <label for="categorie">Categorie</label>
            <select name="categorie" id="categorie">
                <option value="">Kies een categorie...</option>
                <option value="Eten">Eten</option>
                <option value="Bediening">Bediening</option>
                <option value="Sfeer">Sfeer</option>
            </select>
        </div>

        <div class="form-group">
            <label for="toelichting">Toelichting</label>
            <textarea name="toelichting" id="toelichting" rows="4" placeholder="Vertel ons meer over uw ervaring..."></textarea>
        </div>

        <button type="submit">Feedback versturen</button>
    </form>
</div>
</body>
</html>
