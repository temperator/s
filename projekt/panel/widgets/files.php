<?php
$uploadDir = __DIR__ . '/../../uploads/';
$fileCount = 0;
$totalSize = 0;
$imageCount = 0;
$bigFiles = 0;

foreach (glob($uploadDir . "*") as $file) {
    if (is_file($file)) {
        $fileCount++;
        $size = filesize($file);
        $totalSize += $size;

        if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
            $imageCount++;
        }

        if ($size > 5 * 1024 * 1024) {
            $bigFiles++;
        }
    }
}

$totalSizeMB = round($totalSize / 1048576, 2);
$avgFileSizeKB = $fileCount ? round($totalSize / $fileCount / 1024, 1) : 0;

?>
 

  <div class="col-md-12 mb-4">
    <div class="card border-info text-center">
    <div class="card-body">
            <h5 class="card-title">📁 Statystyki dysku</h5>
            <ul class="list-group text-start small">
                <li class="list-group-item">🗂️ Plików: <strong><?= $fileCount ?></strong></li>
                <li class="list-group-item">🖼️ Obrazków: <strong><?= $imageCount ?></strong></li>
                <li class="list-group-item">📦 Rozmiar łączny: <strong><?= $totalSizeMB ?> MB</strong></li>
                <li class="list-group-item">📊 Średni rozmiar: <strong><?= $avgFileSizeKB ?> KB</strong></li>
                <li class="list-group-item text-danger">⚠️ Duże pliki >5MB: <strong><?= $bigFiles ?></strong></li>
            </ul>
        </div>
    </div>
</div>
