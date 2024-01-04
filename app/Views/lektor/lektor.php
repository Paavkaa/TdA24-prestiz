<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="\css\style.css">
    <title>Lektor</title>
</head>
<body>
<div class="card">
    <div class="center">
        <img src="<?php echo $data['picture_url'] ?? "" ?>"
             alt="lector_pic">

        <?php if (isset($data['tags']) && is_array($data['tags'])): ?>
            <ul>
                <?php foreach ($data['tags'] as $tag): ?>
                    <li><?= htmlspecialchars($tag['name']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    </div>
    <div>
        <h1>
            <?php echo $data['title_before'] ?? "" ?>
            <?php echo $data['first_name'] ?? "" ?>
            <?php echo $data['middle_name'] ?? "" ?>
            <?php echo $data['last_name'] ?? "" ?>
            <?php echo $data['title_before'] ?? "" ?>
        </h1>
        <h2><?php echo $data['claim'] ?? "" ?></h2>

        <span>Lokalita:</span>
        <p><?php echo $data['location'] ?? "" ?></p> <br>
        <span>Cena za hodinu:</span>
        <p><?php echo $data['price_per_hour'] ?? "" ?> Kč</p> <br>

        <span class="contact">Kontakt:</span>
        <table class="contact">
            <tr>
                <td>
                    <p>
                        <?php echo implode(" , ", array_values($data['contact']['telephone_numbers'])) ?? "" ?>
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p>
                        <?php echo implode(" , ", array_values($data['contact']['emails'])) ?? "" ?>
                    </p>
                </td>
            </tr>
        </table>
        <br>

        <span>O mně:</span><br>
        <p><?php echo $data['bio'] ?? "" ?></p>
    </div>


</div>
</body>
</html>
