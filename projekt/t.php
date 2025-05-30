<?php
$storageFile = __DIR__ . '/clipboard.txt';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clipboard'])) {
    $text = trim($_POST['clipboard']);
    file_put_contents($storageFile, $text);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

$currentText = file_exists($storageFile) ? file_get_contents($storageFile) : '';
?>
<!DOCTYPE html>
<html lang="pl">  
<head>
    <meta charset="UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="40">
    <style>
        :root {
            --bg: #1e1e1e;
            --fg: #d0d0d0;
            --panel: #2b2b2b;
            --border: #3a3a3a;
            --accent: #444;
            --accent-hover: #666;
        }

        body {
            background: var(--bg);
            color: var(--fg);
            font-family: 'Segoe UI', Tahoma, sans-serif;
            padding: 40px;
            margin: 0;
        }

        .container {
            width: 800px;
            margin: 0 auto;
            background: var(--panel);
            border: 2px solid var(--border);
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }

        h2 {
            color: #fff;
            text-transform: uppercase;
            font-weight: 600;
            font-size: 24px;
            margin: 0 0 20px 0;
        }

        textarea {
            width: 100%;
            height: 200px;
            background: #1a1a1a;
            border: 2px solid var(--accent);
            color: #e0e0e0;
            padding: 15px;
            font-size: 16px;
            resize: vertical;
            outline: none;
        }

        .btn-group {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .btn {
            flex: 1;
            padding: 14px 20px;
            background: var(--accent);
            color: #fff;
            border: none;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn:hover {
            background: var(--accent-hover);
        }

        pre {
            background: #1a1a1a;
            border: 2px solid var(--accent);
            padding: 15px;
            font-size: 15px;
            white-space: pre-wrap;
            word-break: break-word;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        

        <form method="POST">
            <textarea name="clipboard" id="clipboardInput" placeholder="Wklej tutaj coś..."><?= htmlspecialchars($currentText) ?></textarea>

            <div class="btn-group">
                <input type="submit" class="btn" value="Zapisz">
                <button type="button" class="btn" onclick="copyToClipboard()">Skopiuj</button>
            </div>
        </form>

        <pre id="clipboardText"><?= htmlspecialchars($currentText) ?></pre>
    </div>

    <script>
        function copyToClipboard() {
            const text = document.getElementById('clipboardText').innerText;
            navigator.clipboard.writeText(text).then(() => {
                alert('Skopiowano do schowka.');
            }).catch(err => {
                alert('Błąd kopiowania: ' + err);
            });
        }
    </script>
</body>
</html>