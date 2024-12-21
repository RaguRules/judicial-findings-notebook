<?php

require_once("db.class.php");

class AuthManager{

    public function __construct(){
        // $this->username = $username;
        // $this->password = $password;
        $this->database = new Database("../../../model/data.db");
        $this->conn = $this->database->getConn();

    }

    public function getUser(){
        return $this->username;
    }

    // public function signup(){   
    //     try{
    //         // $this->database = new Database("data.db");
    //         // $this->database = new Database("data.db");
    //         $this->conn->exec("CREATE TABLE IF NOT EXISTS users (
    //             id INTEGER PRIMARY KEY AUTOINCREMENT, 
    //             username TEXT NOT NULL,
    //             password TEXT NOT NULL
    //         )");
    //         echo "ok"; 
    
            // $stmt = $this->conn->prepare("INSERT INTO users (username, password) VALUES ('ab', 'b')");
    
            // // Assuming $this->username and $this->password are already sanitized and validated
            // // $stmt->bindValue(':username', $this->username, SQLITE3_TEXT);
            // // $stmt->bindValue(':password', $this->password, SQLITE3_TEXT); // Make sure $this->password is hashed!
            // $stmt->execute();

    //         // $stmt = $this->conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");

    //         // // "CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE NOT NULL,password TEXT NOT NULL)"

    //         // $stmt->bindValue(':username', $this->username, SQLITE3_TEXT); 
    //         // $stmt->bindValue(':password', $this->password, SQLITE3_TEXT);
    //         // $stmt->execute();



    //         $stmt = $this->conn->prepare("SELECT * FROM users"); 
    //         $stmt->execute();

    //         // $results = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all results as an associative array
    //         $results = [];
    //         while ($row = $stmt->fetchArray(SQLITE3_ASSOC)) {
    //             $results[] = $row;
    //         }

    //         // Fetch and display user data (for demonstration)
    //         $stmt2 = $this->conn->prepare("SELECT * FROM users");
    //         $stmt2->execute();

    //         $results = [];
    //         while ($row = $stmt2->fetchArray(SQLITE3_ASSOC)) {
    //             $results[] = $row;
    //         }

    //         foreach ($results as $row) {
    //             echo "ID: " . $row['id'] . ", Username: " . $row['username'] . ", Password: " . $row['password'] . "<br>";
    //         }





    //     }catch(Exception $e){
    //         echo $e->getMessage();
    //     }  

        
    // }

    public function signup($username, $password) {
        try {
            // Create the users table if it doesn't exist
            $this->conn->exec("CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL,
                password TEXT NOT NULL
            )");

            // Hash the password 
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Register new user
            $stmt_write = $this->conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt_write->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt_write->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt_write->execute();

            // fetchArray() is in SQLite3Stml class from sqlite3 extension/Driver; not from PDO. instead use  fetch(PDO::FETCH::FETCH_ASSOC);
            // $stmt_read = $this->conn->prepare("SELECT * FROM users"); 
            $stmt_read = $this->conn->prepare("SELECT * FROM users"); 
            $stmt_read->execute();

            $results = [];
            while ($row = $stmt_read->fetch(PDO::FETCH_ASSOC)) { 
                $results[] = $row['username'];
            }
            // Instead of using print_r($results), which displays the array structure, the code now uses a foreach loop to iterate through the $results array.
            // Inside the loop, echo $username . "<br>"; prints each username followed by a line break (<br>)
            // print_r($results);
            foreach($results as $user){
                echo $user . "<br>";
            }

        } catch (PDOException $e) {
            if($e->getCode()==23000){ //Error code for Integrity constraint violation
                echo "This username is already taken. Choose a different username!";
            }else{
                echo "Error: " . $e->getCode() . $e->getMessage();
            }
        }
    }

    public function login($username, $password){
        
            $user = $this->database->signin($username, $password);

            if($user && password_verify($password, $user['password'])){
                // post login actions. i.e. Token gen and maintain
                echo "Login Success";

                $accessToken = $this->genToken($user['id'], 'access', 5);
                $refreshToken = $this->genToken($user['id'], 'refresh', 30*24*60);
                // $this->storeToken($user['id'], $accessToken, $refreshToken);
                print_r("[
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken
                ]");

                return [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken
                ];

            }else{
                echo "Invalid username/ password";
            }
    }

    // public function logout($user="1"){
    //     $this->database->deleteToken($user); 
    // }
    public function logout($accessToken){
        $this->database->deleteToken($accessToken);
    }


    public function genToken($userId, $tokenType, $expiryMinutes){

        $token = bin2hex(random_bytes(32));

        $created = date("Y-m-d H:i:s");
        $expiry = date("Y-m-d H:i:s", strtotime("+$expiryMinutes minutes"));

        return $this->database->writeTokensOnDb($userId, $token, $tokenType, $created, $expiry);

    }

}





// require_once("db.class.php");

// class AuthManager{


//     private $db;


//     public function __construct($username, $password){
//         $this->username = $username;
//         $this->password = $password;
//         echo "hi";
//         $this->db = new Database();
//         $this->dbConnection = $this->db->getConn();
//     }


//     public function validateInput($input, $type) {

//         $input = trim($input);

//         if (empty($input)){
//             return "Password, and Username are required!";
//         }
//         switch ($type) {
//             case 'username':
//                 $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
//                 if (!preg_match('/^[a-zA-Z0-9_]+$/', $input)) {
//                     return "Invalid username format. Please use only alphanumeric characters and underscores.";
//                 }
//                 break;

//             case 'password':
//                 if (strlen($input) < 8 || !preg_match('/[A-Za-z]/', $input) || !preg_match('/[0-9]/', $input)) {
//                 // if (strlen($input) < 8 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $input)) {
//                     // return "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.";
//                     return "Password must be at least 8 characters long and contain both letters and numbers.";
//                 }
//                 break;

//             default:
//                 return "Invalid input type.";
//         }

//         return true; // Input is valid
//     }


//     // By accepting the password as a parameter, it can be used to hash any password, not just the one associated with the current AuthManager object.
//     // The hashPassword() method should only be concerned with hashing the password it receives, not with accessing or modifying the internal state ($this->password) of the AuthManager object.
//     public function hashPassword($password){

//         // 1. Generate a strong random salt
//         $salt = random_bytes(16);

//         // 2. Hash the password with the salt using a secure hashing algorithm
//         $hashedPassword = password_hash($password . $salt, PASSWORD_DEFAULT);

//         // 3. Return the salt and hashed password as a combined string
//         return base64_encode($salt) . ':' . $hashedPassword;
//     }


//     public function generateAccessToken(){

//     }


//     public function generateRefreshtoken(){

//     }


//     public function signup($username, $password){
//         //save username, hashed password in user database
//         // 1. Validate input (username and password)
//         $validUserName = $this->validateInput($username,'username');
//         $validPassword = $this->validateInput($password,'password');

//         if($validUserName!=true){
//             return $validUserName;
//         }if($validPassword!=true){
//             return $validPassword;
//         }

//         // 2. Hash the password
//         // echo $validPassword."lol";
//         // echo $validUserName;
//         // echo "222";
//         $hashedPassword = $this->hashPassword($password);
        
     
//         // 3. Prepare the SQL statement
//         $stmt = $this->dbConnection->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
//         $stmt->bindValue(':username', $username, SQLITE3_TEXT); 
//         $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);

//         // 4. Execute the statement and handle errors
//         try{
//             if ($stmt->execute()) {
//                 return true; // Signup successful
//             } else {
//                 // Handle database error (e.g., duplicate username)
//                 return "Error creating user. Please try different username.";
//             }
//         }catch(Exception $e){
//             // Log the exception and return a generic error message
//             error_log("Signup error: " . $e->getMessage());
//             return "An error occurred during signup.";
//         }
//     }


//     public function getUser($id){

//     }


//     public function login($username, $password){

//     }


//     public function logout(){
    
//     }
// }

?>