<?php

// Include guard to prevent direct access to the file.
if (!defined('*JusticeDelayedIsJusticeDenied@1')) {
    die('Direct access is not allowed.');
}

require_once("db.class.php");

/**
 * NotesManager class for managing user notes.
 */
class NotesManager extends Database {

    /**
     * NotesManager constructor.
     */
    public function __construct() {
        parent::__construct("../../../model/data.db");
    }

    /**
     * Creates a new note.
     * @param int $userId The ID of the user creating the note.
     * @param string $title The title of the note.
     * @param string $data The content of the note.
     * @return bool True on success, false on failure.
     */
    public function createNotes($userId, $title, $data) {
        $createdAt = date("Y-m-d H:i:s");
        return $this->noteCreate($userId, $title, $data, $createdAt);
    }

    /**
     * Updates an existing note.
     * @param int $userId The ID of the user updating the note.
     * @param int $noteId The ID of the note to update.
     * @param string $title The new title of the note.
     * @param string $data The new content of the note.
     * @return bool True on success, false on failure.
     */
    public function updateNotes($userId, $noteId, $title, $data) {
        // Removed $createdAt as it's not used in the noteUpdate method in db.class.php
        return $this->noteUpdate($userId, $noteId, $title, $data);
    }

    /**
     * @param int $userId The ID of the user who owns the note.
     * @param int $noteId The ID of the note to read.
     * @return mixed The note data as an associative array on success, false on failure.
     */
    public function readNotes($userId, $noteId) {
        return $this->noteRead($userId, $noteId);
    }

    /**
     * Deletes a note based on its title and the user it belongs to.
     * @param int $userId The ID of the user who owns the note.
     * @param string $title The title of the note to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteNotes($userId, $noteId) {
        return $this->noteDelete($userId, $noteId);
    }

    /**
     * Retrieves all notes for a user.
     * @param int $userId The ID of the user.
     * @return mixed An array of notes (associative arrays) on success, false on failure.
     */
    public function getAllNotes($userId) {
        return $this->noteListAll($userId);
    }
}