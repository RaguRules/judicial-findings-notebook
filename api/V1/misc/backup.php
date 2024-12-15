<?php
// Example route for backup API
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'backup') {
    $backupManager = new BackupManager();
    $response = $backupManager->backupDatabases();
    echo json_encode($response);
}

?>