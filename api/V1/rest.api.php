<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "../../lib/auth.class.php";

class REST {
    protected $db; // Database connection
    protected $request ; // Store processed request data
    protected $allowedMethods ; // Allowed HTTP methods for the endpoint
    protected $contentType = "application/json"; // Default content type for responses
    private $statusCode = 200; // Default status code

    public function __construct() {
        // Initialize the database connection using the Database class
        $this->db = (new Database("../../../model/data.db"))->getConn(); 
        $this->processRequest(); // Process the incoming request
    }

    public function getReferer() { 
        return $_SERVER['HTTP_REFERER']; // Get the HTTP referer header
    }

    public function respond($data, $statusCode = null) {
        // Set the response status code
        if ($statusCode !== null) {
            $this->statusCode = $statusCode;
        }
        http_response_code($this->statusCode);

        // Set the Content-Type header
        header('Content-Type: ' . $this->contentType);

        // Encode the data as JSON and send the response
        echo json_encode($data); 
        exit;
    }

    private function getStatusMessage() {
        // Array of status code messages
        $statusMessages = [
            // ... (your status code messages)
        ];
        // Return the message for the current status code or a default message
        return $statusMessages[$this->statusCode] ?? $statusMessages[500]; 
    }

    public function getRequestMethod() {
        return $_SERVER['REQUEST_METHOD']; // Get the HTTP request method
    }

    protected function processRequest() { 
        $method = $this->getRequestMethod();

        // Check if the request method is allowed
        if (!in_array($method, $this->allowedMethods)) {
            $this->respond(['error' => 'Method Not Allowed'], 405);
        }

        // Process the request data based on the method
        switch ($method) {
            case "POST":
            case "PUT":
                // Decode JSON data from the request body
                $this->request = $this->cleanInputs(json_decode(file_get_contents("php://input"), true)); 
                break;
            case "GET":
            case "DELETE":
                $this->request = $this->cleanInputs($_GET); // Get data from query parameters
                break;
        }
    }

    private function cleanInputs($data) {
        // ... (your input sanitization logic)
        // Consider using a dedicated validation library for more robust validation

        $cleanInput = [];
    
        if (is_array($data)) {
            // Recursively clean each element of the array
            foreach ($data as $key => $value) {
                $cleanInput[$key] = $this->cleanInputs($value);
            }
        } else {
            // Sanitize the input string
            $data = trim($data); // Remove leading/trailing whitespace
            $data = strip_tags($data); // Remove HTML tags
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); // Escape special HTML characters
    
            $cleanInput = $data;
        }
    
        return $cleanInput;
                
    }
}

?>