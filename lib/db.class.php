<?php

// Include guard to prevent direct access to the file.
if (!defined('*JusticeDelayedIsJusticeDenied@1')) {
    die('Direct access is not allowed.');
}

require_once __DIR__ . '/config.php';

/**
 * Database class using PDO for SQLite.
 */
class Database {

    /**
     * @var PDO The PDO database connection object.
     */
    private $db;

    /**
     * Database constructor.
     *
     * @param string $dbpath The path to the SQLite database file.
     * @throws Exception If the database connection cannot be established.
     */
    protected function __construct($dbpath) {
        try {
            // $this->db = new PDO("sqlite:" . $dbpath);
            // $this->db = new PDO("sqlite:" . __DIR__ . "/../model/data.db");    //still this Relative Path in Class Approach (Less Robust)
            $this->db = new PDO("sqlite:" . DB_PATH);    //Centralized config.php this is good Approach
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->createTables();
        } catch (PDOException $e) {
            error_log("Error connecting to database: " . $e->getMessage());
            throw new Exception("Could not connect to the database.");
        }
    }

    /**
     * Creates the necessary database tables if they don't exist.
     *
     * @throws Exception If there is an error creating the tables.
     */
    protected function createTables() {
        $usersTableSQL = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL
        )";

        $tokensTableSQL = "CREATE TABLE IF NOT EXISTS auth_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token TEXT UNIQUE NOT NULL,
            type TEXT NOT NULL CHECK(type IN ('access', 'refresh')),
            created_at TEXT NOT NULL,
            expires_at TEXT NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        )";

        $notesTableSQL = "CREATE TABLE IF NOT EXISTS notes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            content TEXT,
            created_at TEXT NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        )";

        try {
            $this->db->exec($usersTableSQL);
            $this->db->exec($tokensTableSQL);
            $this->db->exec($notesTableSQL);
        } catch (PDOException $e) {
            error_log("Error creating tables: " . $e->getMessage());
            throw new Exception("Could not create database tables.");
        }
    }

    /**
     * Gets the PDO database connection object.
     *
     * @return PDO The database connection object.
     */
    protected function getConn() {
        return $this->db;
    }

    // --------------------------------- SECTION 2: FOR AUTH CLASS ---------------------------------

    /**
     * Registers a new user.
     *
     * @param string $newuser The username of the new user.
     * @param string $hashedPassword The hashed password of the new user.
     * @return mixed Returns true on success, -1 if the username is taken, false on other errors.
     */
    protected function register($newuser, $hashedPassword) {
        try {
            $stmt_write = $this->db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt_write->bindValue(':username', $newuser, PDO::PARAM_STR);
            $stmt_write->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt_write->execute();

            return true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation (likely duplicate username)
                return -1; // Indicate a specific error code for duplicate username
            } else {
                error_log("Error during registration: " . $e->getMessage());
                return false; // General registration error
            }
        }
    }

    /**
     * Signs in a user.
     *
     * @param string $username The username of the user.
     * @param string $password The password of the user (not used directly in the query for security).
     * @return mixed Returns the user data (id and hashed password) as an associative array on success, false on failure.
     */
    protected function signin($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT id, password FROM users WHERE username = :username");
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error during signin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Writes a new token to the database.
     *
     * @param int $userId The ID of the user the token belongs to.
     * @param string $token The generated token string.
     * @param string $tokenType The type of the token ('access' or 'refresh').
     * @param string $created The token creation timestamp.
     * @param string $expiry The token expiry timestamp.
     * @return mixed Returns the token on success, false on failure.
     */
    protected function writeTokensOnDb($userId, $token, $tokenType, $created, $expiry) {
        try {
            $stmt_write = $this->db->prepare("INSERT INTO auth_tokens (user_id, token, type, created_at, expires_at) VALUES (:user_id, :token, :type, :created_at, :expires_at)");
            $stmt_write->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt_write->bindValue(':token', $token, PDO::PARAM_STR);
            $stmt_write->bindValue(':type', $tokenType, PDO::PARAM_STR);
            $stmt_write->bindValue(':created_at', $created, PDO::PARAM_STR);
            $stmt_write->bindValue(':expires_at', $expiry, PDO::PARAM_STR);
            $stmt_write->execute();

            return $token;
        } catch (PDOException $e) {
            error_log("Error writing token to database: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalidates (deletes) all tokens for a given user.
     *
     * @param int $userId The ID of the user whose tokens should be deleted.
     * @return bool True on success, false on failure.
     */
    protected function invalidateTokens($userId) {
        try {
            // Delete all tokens for the given user
            $stmt = $this->db->prepare("DELETE FROM auth_tokens WHERE user_id = :user_id");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            error_log("Error invalidating tokens: " . $e->getMessage());
            return false;
        }
    }

    /**
    * Invalidates (deletes) the access token for a given user.
    *
    * @param int $userId The ID of the user whose access token should be invalidated.
    * @param string $accessToken The access token to invalidate.
    * @return bool True on success, false on failure.
    */
   protected function invalidateAccessTokenOnly($userId, $accessToken) {
       try {
           // Delete the specific access token for the given user
           $stmt = $this->db->prepare("DELETE FROM auth_tokens WHERE user_id = :user_id AND token = :token AND type = 'access'");
           $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
           $stmt->bindValue(':token', $accessToken, PDO::PARAM_STR);
           $stmt->execute();
   
           // Check if any rows were affected (token was deleted)
           return $stmt->rowCount() > 0;
       } catch (PDOException $e) {
           error_log("Error invalidating access token: " . $e->getMessage());
           return false;
       }
   }


    /**
     * Deletes a specific access token from the database.
     *
     * @param string $accessToken The access token to delete.
     * @return bool True on success, false on failure.
     */
    protected function deleteToken($accessToken) {
        try {
            // Find the token (only access tokens are deleted directly)
            $stmt = $this->db->prepare("SELECT user_id FROM auth_tokens WHERE token = :token AND type = 'access'");
            $stmt->bindValue(':token', $accessToken, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch();

            if (!$result) {
                error_log("Error: Access token not found during logout.");
                return false; // Access token not found
            }

            $userId = $result['user_id'];

            // 2. Delete all tokens (both access and refresh) for the user
            $stmt = $this->db->prepare("DELETE FROM auth_tokens WHERE user_id = :user_id");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            error_log("Error deleting token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Refreshes an access token using a refresh token.
     *
     * @param string $refreshToken The refresh token.
     * @return mixed The new access token on success, false on failure.
     */
    protected function refreshingAccessToken($refreshToken) {
        try {
            // Find the refresh token, check if it's valid
            $stmt = $this->db->prepare("SELECT user_id, expires_at FROM auth_tokens WHERE token = :token AND type = 'refresh'");
            $stmt->bindValue(':token', $refreshToken, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch();

            if (!$result || strtotime($result['expires_at']) < time()) {
                return false;
            }

            $userId = $result['user_id'];

            // Generate a new access token
            $newAccessToken = bin2hex(random_bytes(32));
            $newExpiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            // Invalidate the old tokens (by deleting them)
            $this->invalidateTokens($userId);

            // Insert the new access token
            $this->writeTokensOnDb($userId, $newAccessToken, 'access', date('Y-m-d H:i:s'), $newExpiry);

            return $newAccessToken;
        } catch (PDOException $e) {
            error_log("Error refreshing access token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves token data from the database based on the token string.
     *
     * @param string $token The token string.
     * @return mixed An associative array of token data if found, false otherwise.
     */
/**
 * Retrieves token data from the database based on the token string.
 *
 * @param string $token The token string.
 * @return mixed An associative array of token data if found, false otherwise.
 */
    protected function getTokenData($token) {
        try {
            $stmt = $this->db->prepare("SELECT user_id, expires_at, type, token, (SELECT token from auth_tokens WHERE user_id = t.user_id AND type='access') as access_token_to_be_invalidated FROM auth_tokens t WHERE token = :token");
            $stmt->bindValue(':token', $token, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error fetching token data: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validates a given token.
     *
     * @param string $token The token to validate.
     * @return mixed Returns an associative array with user_id, type, expires_at if the token is valid, false otherwise.
     */
    protected function isValidateToken($token) {
        try {
            $stmt = $this->db->prepare("SELECT user_id, type, expires_at FROM auth_tokens WHERE token = :token");
            $stmt->bindValue(':token', $token, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch();
    
            if ($result) {
                if (strtotime($result['expires_at']) > time()) {
                    return $result; // Valid token
                } else {
                    return -1; // Expired token 
                }
            } else {
                return false; // Invalid token (not found)
            }
        } catch (PDOException $e) {
            error_log("Error validating token: " . $e->getMessage());
            return false; // Error during validation
        }
    }

    // --------------------------------- SECTION 3: FOR NOTES CLASS ---------------------------------

    /**
     * Creates a new note for a user.
     *
     * @param int $userId The ID of the user creating the note.
     * @param string $title The title of the note.
     * @param string $content The content of the note.
     * @param string $createdAt The creation timestamp of the note.
     * @return bool True on success, false on failure.
     */
    protected function noteCreate($userId, $title, $content, $createdAt) {
        try {
            $stmt = $this->db->prepare("INSERT INTO notes (user_id, title, content, created_at) VALUES (:user_id, :title, :content, :created_at)");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':title', $title, PDO::PARAM_STR);
            $stmt->bindValue(':content', $content, PDO::PARAM_STR);
            $stmt->bindValue(':created_at', $createdAt, PDO::PARAM_STR);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error creating note: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates an existing note.
     *
     * @param int $userId The ID of the user updating the note.
     * @param int $noteId The ID of the note to update.
     * @param string $newTitle The new title of the note.
     * @param string $newContent The new content of the note.
     * @return bool True on success, false on failure.
     */
    protected function noteUpdate($userId, $noteId, $newTitle, $newContent) {
        try {
            // Check if the note exists and belongs to the user
            $stmt = $this->db->prepare("SELECT id FROM notes WHERE id = :note_id AND user_id = :user_id");
            $stmt->bindValue(':note_id', $noteId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();

            if (!$result) {
                return false;
            }

            // Update the note
            $stmt = $this->db->prepare("UPDATE notes SET title = :title, content = :content WHERE id = :note_id");
            $stmt->bindValue(':title', $newTitle, PDO::PARAM_STR);
            $stmt->bindValue(':content', $newContent, PDO::PARAM_STR);
            $stmt->bindValue(':note_id', $noteId, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            error_log("Error updating note: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves a specific note for a user.
     *
     * @param int $userId The ID of the user.
     * @param int $noteId The ID of the note to retrieve.
     * @return mixed The note data as an associative array on success, false on failure.
     */
    public function noteRead($userId, $noteId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM notes WHERE id = :note_id AND user_id = :user_id");
            $stmt->bindValue(':note_id', $noteId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $note = $stmt->fetch();

            return $note;
        } catch (PDOException $e) {
            error_log("Error fetching note: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves all notes for a user.
     *
     * @param int $userId The ID of the user.
     * @return mixed An array of notes (associative arrays) on success, false on failure.
     */
    public function noteListAll($userId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM notes WHERE user_id = :user_id");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $notes = $stmt->fetchAll();

            return $notes;
        } catch (PDOException $e) {
            error_log("Error fetching notes: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a note based on its title and the user it belongs to.
     *
     * @param int $userId The ID of the user.
     * @param string $title The title of the note to delete.
     * @return bool True on success, false on failure.
     */
    protected function noteDelete($userId, $noteId) {
        try {
            // Check if the note exists and belongs to the user
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM notes WHERE id = :noteId AND user_id = :userId");
            $stmt->bindValue(':noteId', $noteId, PDO::PARAM_INT);
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->fetchColumn() === 0) {
                // Note not found or doesn't belong to the user
                error_log("Note not found or user not authorized to delete. Note ID: " . $noteId . ", User ID: " . $userId);
                return false;
            }

            // Delete the note using its ID and ensuring it belongs to the user
            $stmt = $this->db->prepare("DELETE FROM notes WHERE id = :noteId AND user_id = :userId");
            $stmt->bindValue(':noteId', $noteId, PDO::PARAM_INT);
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            // Check if any rows were affected (note was deleted)
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Error deleting note: " . $e->getMessage());
            return false;
        }
    }
}

?>