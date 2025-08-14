<?php
session_start();
$jwt = $_SESSION['jwt'] ?? '';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ダッシュボード</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<button id="getContacts">API取得</button>
<pre id="result"></pre>
<script>
$('#getContacts').on('click', function() {
    $.ajax({
        url: '/api/contacts.php',
        type: 'GET',
        headers: { 'Authorization': 'Bearer <?= $jwt ?>' },
        success: function(data) { $('#result').text(JSON.stringify(data, null, 2)); },
        error: function(xhr) { $('#result').text(xhr.responseText); }
    });
});
</script>
</body>
</html>
