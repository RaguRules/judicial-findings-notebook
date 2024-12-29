<?php

require_once("db.class.php");

class AuthManager extends Database{

    public function __construct(){
        parent::__construct("../../../model/data.db");
    }


    public function getUser(){
        return $this->username;
    }


    public function signup($username, $password) {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        return $results = $this->register($username, $hashedPassword);

       if($results){
            // echo "You have successfully registered. Go to log in";
            // return true;
       }
    }


    public function login($username, $password){
        
            $user = $this->signin($username, $password);

            if($user && password_verify($password, $user['password'])){
                echo "Login Success";

                // post login actions. i.e. Token gen and maintain
                $this->invalidateTokens($user['id']);

                $accessToken = $this->genToken($user['id'], 'access', 30);
                $refreshToken = $this->genToken($user['id'], 'refresh', 30*24*60);
                
                // print_r("[
                //     'access_token' => $accessToken,
                //     'refresh_token' => $refreshToken
                // ]");

                return [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken
                ];

            }else{
                echo "Invalid username/ password";
            }
    }


    public function logout($accessToken){
        $this->deleteToken($accessToken);
    }


    public function genToken($userId, $tokenType, $expiryMinutes){

        $token = bin2hex(random_bytes(32));

        $created = date("Y-m-d H:i:s");
        $expiry = date("Y-m-d H:i:s", strtotime("+$expiryMinutes minutes"));

        return $this->writeTokensOnDb($userId, $token, $tokenType, $created, $expiry);
    }


    public function refreshAccessToken($refreshToken){
        $this->refreshingAccessToken($refreshToken);
    }


    public function validateToken($token) {
        
        $result = $this->isValidateToken($token);

        if ($result && $result['expires_at'] > date('Y-m-d H:i:s')) {
            return [
                'user_id' => $result['user_id'],
                'type' => $result['type']
            ];
        } else {
            // echo "Invalid Token";
            return false; // Invalid or expired token
        }

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