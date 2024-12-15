<?php
class BackupManager {
    public function backupDatabases() {
        $desktopPath = getenv("HOMEDRIVE") . getenv("HOMEPATH") . '\\Desktop'; // For Windows
        if (PHP_OS_FAMILY !== 'Windows') {
            $desktopPath = getenv("HOME") . '/Desktop'; // For macOS/Linux
        }

        // Ensure backup folder exists
        $backupFolder = $desktopPath . DIRECTORY_SEPARATOR . 'SQLite_Backups';
        if (!file_exists($backupFolder)) {
            mkdir($backupFolder, 0777, true);
        }

        // Files to backup
        $files = ['auth.db', 'notes.db'];

        foreach ($files as $file) {
            if (file_exists($file)) {
                $backupPath = $backupFolder . DIRECTORY_SEPARATOR . basename($file) . '_' . date('Y-m-d_H-i-s') . '.db';
                if (!copy($file, $backupPath)) {
                    return ["status" => "error", "message" => "Failed to back up $file"];
                }
            } else {
                return ["status" => "error", "message" => "$file does not exist"];
            }
        }

        return ["status" => "success", "message" => "Databases backed up to $backupFolder"];
    }
}

?>