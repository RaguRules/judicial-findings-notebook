<?php
require_once("db.class.php");

class AuthManager{


    private $db;


    public function __construct($username, $password){
        $this->username = $username;
        $this->password = $password;
        echo "hi";
        $this->db = new Database();
        $this->dbConnection = $this->db->getConn();
    }


    public function validateInput($input, $type) {

        $input = trim($input);

        if (empty($input)){
            return "Password, and Username are required!";
        }
        switch ($type) {
            case 'username':
                $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $input)) {
                    return "Invalid username format. Please use only alphanumeric characters and underscores.";
                }
                break;

            case 'password':
                if (strlen($input) < 8 || !preg_match('/[A-Za-z]/', $input) || !preg_match('/[0-9]/', $input)) {
                // if (strlen($input) < 8 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $input)) {
                    // return "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.";
                    return "Password must be at least 8 characters long and contain both letters and numbers.";
                }
                break;

            default:
                return "Invalid input type.";
        }

        return true; // Input is valid
    }


    // By accepting the password as a parameter, it can be used to hash any password, not just the one associated with the current AuthManager object.
    // The hashPassword() method should only be concerned with hashing the password it receives, not with accessing or modifying the internal state ($this->password) of the AuthManager object.
    public function hashPassword($password){

        // 1. Generate a strong random salt
        $salt = random_bytes(16);

        // 2. Hash the password with the salt using a secure hashing algorithm
        $hashedPassword = password_hash($password . $salt, PASSWORD_DEFAULT);

        // 3. Return the salt and hashed password as a combined string
        return base64_encode($salt) . ':' . $hashedPassword;
    }


    public function generateAccessToken(){

    }


    public function generateRefreshtoken(){

    }


    public function signup($username, $password){
        //save username, hashed password in user database
        // 1. Validate input (username and password)
        $validUserName = $this->validateInput($username,'username');
        $validPassword = $this->validateInput($password,'password');

        if($validUserName!=true){
            return $validUserName;
        }if($validPassword!=true){
            return $validPassword;
        }

        // 2. Hash the password
        // echo $validPassword."lol";
        // echo $validUserName;
        // echo "222";
        $hashedPassword = $this->hashPassword($password);
        
     
        // 3. Prepare the SQL statement
        $stmt = $this->dbConnection->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->bindValue(':username', $username, SQLITE3_TEXT); 
        $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);

        // 4. Execute the statement and handle errors
        try{
            if ($stmt->execute()) {
                return true; // Signup successful
            } else {
                // Handle database error (e.g., duplicate username)
                return "Error creating user. Please try different username.";
            }
        }catch(Exception $e){
            // Log the exception and return a generic error message
            error_log("Signup error: " . $e->getMessage());
            return "An error occurred during signup.";
        }
    }


    public function getUser($id){

    }


    public function login($username, $password){

    }


    public function logout(){
    
    }
}

// class AuthManager {
//     private $db;

//     public function __construct($db) {
//         $this->db = $db;
//     }

//     // Create a user account
//     public function createUser($username, $password) {
//         $stmt = $this->db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
//         $stmt->bindValue(':username', htmlspecialchars($username), SQLITE3_TEXT); // Prevent XSS
//         $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT); // Hash password
//         return $stmt->execute();
//     }

//     // Login and generate a token
//     public function login($username, $password) {
//         $stmt = $this->db->prepare("SELECT id, password FROM users WHERE username = :username");
//         $stmt->bindValue(':username', htmlspecialchars($username), SQLITE3_TEXT);
//         $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

//         if ($result && password_verify($password, $result['password'])) {
//             // Generate a token
//             $token = bin2hex(random_bytes(16));
//             $stmt = $this->db->prepare("INSERT INTO auth_tokens (user_id, token, created_at) VALUES (:user_id, :token, :created_at)");
//             $stmt->bindValue(':user_id', $result['id'], SQLITE3_INTEGER);
//             $stmt->bindValue(':token', $token, SQLITE3_TEXT);
//             $stmt->bindValue(':created_at', date('Y-m-d H:i:s'), SQLITE3_TEXT);
//             $stmt->execute();

//             return $token; // Return token to client
//         }
//         return false; // Invalid credentials
//     }

//     // Validate a token
//     public function validateToken($token) {
//         $stmt = $this->db->prepare("SELECT user_id FROM auth_tokens WHERE token = :token AND created_at >= datetime('now', '-1 day')");
//         $stmt->bindValue(':token', $token, SQLITE3_TEXT);
//         $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

//         return $result ? $result['user_id'] : false; // Return user_id or false if invalid
//     }
// }


?>