<?php
$editId = isset($_GET['edit']) ? trim($_GET['edit']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add / Edit Product</title>
    <link rel="stylesheet" href="/Project_IMS/assests/css/add_edit.css">
</head>
<body>
    <main class="page-shell">
        
    </main>
    <script>
        const editId = `<?= json_encode($editId); ?>`;
    </script>
    <script src="/Project_IMS/assests/js/add.js"></script>
    <script src="/Project_IMS/assests/js/dashboard.js"></script>
</body>
</html>
