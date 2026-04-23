<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?= \AeroCanada\Core\CSRF::meta() ?>

    <title><?= htmlspecialchars($__title ?? 'Sign In') ?> - AeroCanada Industries</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- AeroCanada Theme -->
    <link href="/pages/v2/assets/css/aerocanada.css" rel="stylesheet">
</head>
<body>

    <div class="aci-auth-wrapper">

        <!-- Decorative floating elements -->
        <div style="position:absolute;top:15%;left:10%;opacity:0.05;font-size:6rem;color:white;transform:rotate(-15deg);">
            <i class="fa-solid fa-plane"></i>
        </div>
        <div style="position:absolute;bottom:20%;right:8%;opacity:0.04;font-size:4rem;color:white;transform:rotate(25deg);">
            <i class="fa-solid fa-compass"></i>
        </div>

        <!-- Auth Content -->
        <?= $__content ?? '' ?>

    </div>

    <!-- jQuery 3.7 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap 5.3 Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AeroCanada JS -->
    <script src="/pages/v2/assets/js/aerocanada.js"></script>

    <?php if (!empty($__inline_js)): ?>
        <script><?= $__inline_js ?></script>
    <?php endif; ?>
</body>
</html>
