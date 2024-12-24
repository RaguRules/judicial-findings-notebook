<?php

// ------------Newer PDO_SQLite driver based approach------------

class Database{

    // ---------------------------------SECTION 1:  FOR DATABASE CLASS ITSELF ---------------------------------

    private $db;

    protected function __construct($dbpath){
        try{
            $this->db = new PDO ("sqlite:" . $dbpath);
            $this->db->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $this->createTables();
            // echo "Connected to Database successfully..<br>";
        }catch (PDOException $e){
            echo "Error connecting to databse: ". $e->getMessage() ;
        }
    }

    protected function createTables() {
        $usersTableSQL = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL
        )";

        // $tokensTableSQL = "CREATE TABLE IF NOT EXISTS auth_tokens (
        //     id INTEGER PRIMARY KEY AUTOINCREMENT,
        //     user_id INTEGER NOT NULL,
        //     token TEXT UNIQUE NOT NULL,
        //     type TEXT NOT NULL,
        //     created_at TEXT NOT NULL,
        //     FOREIGN KEY (user_id) REFERENCES users (id)
        // )";
        $tokensTableSQL = "CREATE TABLE IF NOT EXISTS auth_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token TEXT UNIQUE NOT NULL,
            type TEXT NOT NULL, -- 'access' or 'refresh'
            created_at TEXT NOT NULL,
            expires_at TEXT NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users (id)
        )";

        $notesTableSQL = "CREATE TABLE IF NOT EXISTS notes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            content TEXT,
            created_at TEXT NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users (id)
        )";

        $this->db->exec($usersTableSQL);
        $this->db->exec($tokensTableSQL);
        $this->db->exec($notesTableSQL);
    }


    protected function getConn() {
        return $this->db;
    }

     // ---------------------------------SECTION 2:  FOR AUTH CLASS ---------------------------------

    protected function register($newuser, $hashedPassword){
        try {
            $this->getConn()->exec("CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL,
                password TEXT NOT NULL
            )");

            // Register new user
            // fetchArray() is in SQLite3Stml class from sqlite3 extension/Driver; not from PDO. instead use  fetch(PDO::FETCH::FETCH_ASSOC);
            $stmt_write = $this->getConn()->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt_write->bindValue(':username', $newuser, PDO::PARAM_STR);
            $stmt_write->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt_write->execute();

            return true;

        } catch (PDOException $e) {
            if($e->getCode()==23000){ //Error code for Integrity constraint violation
                echo "This username is already taken. Choose a different username!";
                return false;
            }else{
                echo "Error: " . $e->getCode() . $e->getMessage();
                return false;
            }
        }
    }


    protected function signin($username, $password){
        try{
            $stmt = $this->getConn()->prepare("SELECT id, password FROM users WHERE username=:username");
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        }catch(PDOException $e){
            echo "Error Username not found !" . $e->getCode() . $e->getMessage();
            return false;
        }
    }


    protected function writeTokensOnDb($userId, $token, $tokenType, $created, $expiry){
        try{
            $stmt_write = $this->getConn()->prepare("INSERT INTO auth_tokens (user_id, token, type, created_at, expires_at) VALUES (:user_id, :token, :type, :created_at, :expires_at)");
            $stmt_write->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt_write->bindValue(':token', $token, PDO::PARAM_STR);
            $stmt_write->bindValue(':type', $tokenType, PDO::PARAM_STR);
            $stmt_write->bindValue(':created_at', $created, PDO::PARAM_STR);
            $stmt_write->bindValue(':expires_at', $expiry, PDO::PARAM_STR);
            $stmt_write->execute();
        }catch(PDOException $e){
            error_log("Error generating token: " . $e->getMessage());
            return false;
        }
        return $token;
    }


    protected function invalidateTokens($userId) {
        try {
            $stmt = $this->getConn()->prepare("DELETE FROM auth_tokens WHERE user_id = :user_id");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            echo'Error '. $e->getMessage();
            return false;
        }
    }


    protected function deleteToken($accessToken){
        try{
            try{
                $stmt = $this->db->prepare("SELECT * FROM auth_tokens WHERE token=:token");
                $stmt->bindValue(':token', $accessToken);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            }catch (PDOException $e){
                // echo "Invalid Token";
                echo $e->getMessage();
            }
            if (!$result){
                echo "No logged-in user found for this token.";
                return false;
            }

            $userId = $result['user_id'];

            $stmt = $this->db->prepare("DELETE FROM auth_tokens WHERE user_id =:user_id");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            echo "<br> Tokens have been deleted, User has been logged out";
        }catch(PDOExeption $e){
            echo $e->getCode() . $e->getMessage();
        }
    }


    protected function refreshingAccessToken($refreshToken){
        try{
            $stmt = $this->db->prepare("SELECT user_id, expires_at FROM auth_tokens WHERE token=:token AND type='refresh'");
            $stmt->bindValue(':token', $refreshToken);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result || $result['expires_at']< date('Y-m-d H:i:s')){
                // throw new Exception('Invalid or expired refresh token.');
                echo "Invalid or expired refresh token.";
            }else{
                $userId = $result['user_id'];

                $newAccessToken = bin2hex(random_bytes(32));
                $newExpiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));

                $stmt = $this->db->prepare("UPDATE auth_tokens SET token = :token, expires_at = :expires_at WHERE user_id = :user_id AND type = 'access'");
                $stmt->bindValue(':token', $newAccessToken, PDO::PARAM_STR);
                $stmt->bindValue(':expires_at', $newExpiry, PDO::PARAM_STR);
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
        
                echo "Your new Access Token is: " . $newAccessToken;
                return $newAccessToken;
            }

        }catch (PDOException $e){
            echo $e->getCode() . $e->getMessage();
        }
    }


    protected function isValidateToken($token){
        try {
            $stmt = $this->db->prepare("SELECT user_id, type, expires_at FROM auth_tokens WHERE token = :token");
            $stmt->bindValue(':token', $token, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
            return $result;
    
        } catch (PDOException $e) {
            echo "$e->getCode() . $e->getMessage()";
        }
    }


     // ---------------------------------SECTION 3:  FOR NOTES CLASS ---------------------------------

    protected function noteCreate($userId, $title, $content,$createdAt){
        try {
            $stmt = $this->db->prepare("INSERT INTO notes (user_id, title, content, created_at) VALUES (:user_id, :title, :content, :created_at)");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':title', $title, PDO::PARAM_STR);
            $stmt->bindValue(':content', $content, PDO::PARAM_STR);
            $stmt->bindValue(':created_at', $createdAt, PDO::PARAM_STR);
            $stmt->execute();
            return true;

        } catch (PDOException $e) {
            echo "Error creating Notes: " . $e->getCode() . $e->getMessage();
            return false;
        }
    }


    protected function noteUpdate($userId, $noteId, $newTitle, $newContent) {
        try {
            // 1. Check if the note exists and belongs to the user
            $stmt = $this->db->prepare("SELECT id FROM notes WHERE id = :note_id AND user_id = :user_id");
            $stmt->bindValue(':note_id', $noteId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$result) {
                echo "Note not found or doesn't belong to the user.";
                return false;
            }
    
            // 2. Update the note with the new title and content
            $stmt = $this->db->prepare("UPDATE notes SET title = :title, content = :content WHERE id = :note_id");
            $stmt->bindValue(':title', $newTitle, PDO::PARAM_STR);
            $stmt->bindValue(':content', $newContent, PDO::PARAM_STR);
            $stmt->bindValue(':note_id', $noteId, PDO::PARAM_INT);
            $stmt->execute();
    
            // echo "Note updated successfully.";
            return true;
    
        } catch (PDOException $e) {
            echo "Error updating note: " . $e->getMessage();
            return false;
        }
    }


    public function noteRead($userId, $noteId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM notes WHERE id = :note_id AND user_id = :user_id");
            $stmt->bindValue(':note_id', $noteId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $note = $stmt->fetch(PDO::FETCH_ASSOC);

            return $note; // Return the note data as an associative array

        } catch (PDOException $e) {
            // Handle database error
            error_log("Error fetching note: " . $e->getMessage());
            return false; // Or throw an exception
        }
    }

    public function noteListAll($userId){
        try {
            $stmt = $this->db->prepare("SELECT * FROM notes WHERE user_id = :user_id");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            return $notes; // Return an array of notes (associative arrays)
    
        } catch (PDOException $e) {
            // Handle database error (e.g., log the error)
            error_log("Error fetching notes: " . $e->getMessage());
            return false; // Or throw an exception
        }
    }


// Mistakes
// Incorrect comparison: You were comparing the entire $this->noteToDelete array (which holds the note data, including id, title, etc.) with the $title string. This comparison would always be false because an array is not equal to a string.
// Deleting by title only: You were using the $title to delete the note, which could potentially delete multiple notes if the user had multiple notes with the same title.
// I didn't explicitly declare $noteToDelete as an array. However, in PHP, you don't need to explicitly declare variable types. PHP determines the type of a variable dynamically based on the value assigned to it.
    // protected function noteDelete($userId, $title){
    //     echo "$title";
    //     try{
    //         try{
    //             $stmt = $this->db->prepare("SELECT * FROM notes WHERE user_id=:user_id");
    //             $stmt->bindValue(':user_id', $userId);
    //             $stmt->execute();
    //             // $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
    //             $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //             // print_r($result);

    //             $noteToDelete = null;
    //             foreach ($result as $note) { // $result is an array of arrays
    //                 // print_r($note);
    //                 $tit = $note['title'];
    //                 // echo "$tit";
    //                 echo "$title";
    //                 if ($tit == $title) {
    //                     $this->noteToDelete = $note; // Assigning an array to $noteToDelete
    //                     print_r("$noteToDelete");
    //                     break;
    //                 }
    //             }
    //         }catch (PDOException $e){
    //             echo "Invalid User";
    //             echo $e->getMessage();
    //         }
    //         if (!$result){
    //             echo "No Notes found for this user.";
    //             return false;
    //         }

    //         // echo "$result['title']";
    //         // $r = $result['title'];
    //         // echo "$r";
    //         // echo "$title";
    //         // $selectedNote = $result['title'];
    //         if ($this->noteToDelete == $title){
    //             try{
    //                 echo "True";
    //                 $stmt = $this->db->prepare("DELETE FROM notes WHERE title =:title");
    //                 // $stmt->bindValue(':title', $selectedNote, PDO::PARAM_STR);
    //                 $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    //                 $stmt->execute();
    //                 echo "<br> Note has been deleted.";
    //                 return true;
    //             }catch (PDOException $e){
    //                 echo "False";
    //                 echo $e->getMessage();
    //             }
    //         }else{
    //             echo "Deletion failed! Either no notes found or This Notes could be created by another user!!";
    //             return false;
    //         }

    //     }catch(PDOExeption $e){
    //         echo $e->getCode() . $e->getMessage();
    //     }

    // }
   
    protected function noteDelete($userId, $title) {
        try {
            try {
                $stmt = $this->db->prepare("SELECT * FROM notes WHERE user_id=:user_id");
                $stmt->bindValue(':user_id', $userId);
                $stmt->execute();
    
                // Fetch all results into $result
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    
                $noteToDelete = null;
                foreach ($result as $note) {
                    // $tit = $note['title'];
                    if ($note['title'] == $title) {
                        $noteToDelete = $note;
                        // print_r($noteToDelete);
                        break;
                    }
                }
            } catch (PDOException $e) {
                echo "Invalid User";
                echo $e->getMessage();
            }
            
            if (!$result) {
                echo "No Notes found for this user.";
                return false;
            }
    
            // Check if a note to delete was found
            if ($noteToDelete) {
                try {
                    echo "True";
                    // Use the ID of the found note to delete
                    $stmt = $this->db->prepare("DELETE FROM notes WHERE id = :id"); 
                    $stmt->bindValue(':id', $noteToDelete['id'], PDO::PARAM_INT); 
                    $stmt->execute();
                    return true;
                } catch (PDOException $e) {
                    echo "False";
                    echo $e->getMessage();
                }
            } else {
                // echo "Deletion failed! Either no notes found or This Notes could be created by another user!!";
                return false;
            }
    
        } catch (PDOException $e) {
            echo $e->getCode() . $e->getMessage();
        }
    }

}


// ------------Older sqlite driver based approach------------

// class Database {
//     public $db;
//     // public $dbpath = "db.db";

//     public function __construct($dbpath){
//         $this->db = new SQLite3 ($dbpath);
//     }


//     public function createTables() {
//         // SQL statements to create the tables
//         $usersTableSQL = "CREATE TABLE IF NOT EXISTS users (
//             id INTEGER PRIMARY KEY AUTOINCREMENT,
//             username TEXT UNIQUE NOT NULL, 
//             password TEXT NOT NULL
//         )";

//         $tokensTableSQL = "CREATE TABLE IF NOT EXISTS auth_tokens (
//             id INTEGER PRIMARY KEY AUTOINCREMENT,
//             user_id INTEGER NOT NULL,
//             token TEXT UNIQUE NOT NULL, 
//             type TEXT NOT NULL, 
//             created_at TEXT NOT NULL,
//             FOREIGN KEY (user_id) REFERENCES users (id)
//         )";

//         $notesTableSQL = "CREATE TABLE IF NOT EXISTS notes (
//             id INTEGER PRIMARY KEY AUTOINCREMENT,
//             user_id INTEGER NOT NULL,
//             title TEXT NOT NULL,
//             content TEXT,
//             created_at TEXT NOT NULL,
//             FOREIGN KEY (user_id) REFERENCES users (id)
//         )";

//         // Execute the SQL statements
//         Try{
//             $this->db->exec($usersTableSQL);
//             echo "db->executed";
//         }catch(Exception $e){
//             echo $e->getMessage();
//         }
//         $this->db->exec($tokensTableSQL);
//         $this->db->exec($notesTableSQL);
//     }

//     public function getConn(){
//         return $this->db;
//     }
// }

?>