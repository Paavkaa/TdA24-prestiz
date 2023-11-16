<!-- app/Views/user/index.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
</head>
<body>
<?php if (!empty($message)): ?>
    <p><?php echo $message ?></p>
<?php endif; ?>

<script src="/js/user/user.js"></script>
</body>
</html>
