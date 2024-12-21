<?php
// -----Newer PDO_SQLite driver based approach-----
class Database{

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
    //     echo "createTables func starts...<br>";
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
        // echo "createTables func ends...<br>";
    }


    protected function getConn() {
        return $this->db;
    }


    protected function register($newuser, $hashedPassword){
        try {
            // echo "came to db.class.php";
            // Create the users table if it doesn't exist
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
            // error_log("Error invalidating tokens: " . $e->getMessage());
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
                echo "Invalid Token";
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
                $newExpiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

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
}


// -----Older sqlite driver based approach-----

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