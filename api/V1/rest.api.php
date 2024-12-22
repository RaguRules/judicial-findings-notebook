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
        $status = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported');
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