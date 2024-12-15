<?php
class RestoreManager {
    public function restoreDatabase($dbName, $backupPath) {
        if (!file_exists($backupPath)) {
            return ["status" => "error", "message" => "Backup file does not exist"];
        }

        if (!in_array($dbName, ['auth.db', 'notes.db'])) {
            return ["status" => "error", "message" => "Invalid database name"];
        }

        if (!copy($backupPath, $dbName)) {
            return ["status" => "error", "message" => "Failed to restore $dbName"];
        }

        return ["status" => "success", "message" => "$dbName successfully restored"];
    }
}

?>