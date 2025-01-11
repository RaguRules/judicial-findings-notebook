<?php

// Include guard to prevent direct access to the file.
if (!defined('*JusticeDelayedIsJusticeDenied@1')) {
    die('Direct access is not allowed.');
}

require_once("db.class.php");

/**
 * AuthManager class for user authentication and token management.
 */
class AuthManager extends Database {

    /**
     * AuthManager constructor.
     */
    public function __construct() {
        // parent::__construct("../../../model/data.db");
        // This will ensure that PHP looks for data.db relative to the current directory of auth.class.php
        parent::__construct(__DIR__ . "/../model/data.db");

    }

    /**
     * Signs up a new user.
     *
     * @param string $username The username for the new user.
     * @param string $password The password for the new user.
     * @return mixed Returns true on successful registration, -1 if the username is taken, false on other errors.
     */
    public function signup($username, $password) {
        // 1. Input Validation:
        $username = trim($username);
        $password = trim($password);

        if (empty($username) || empty($password)) {
            return ['status' => 'error', 'message' => 'Username and password are required.'];
        }

        // Basic username format validation
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return ['status' => 'error', 'message' => 'Invalid username format. Use only alphanumeric characters and underscores.'];
        }

        // Password strength validation
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return ['status' => 'error', 'message' => 'Password must be at least 8 characters long and contain letters and numbers.'];
        }

        // 2. Hash the Password:
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 3. Register the User:
        $result = $this->register($username, $hashedPassword);

        // 4. Handle the Result:
        if ($result === true) {
            return ['status' => 'success', 'message' => 'Registration successful.'];
        } elseif ($result === -1) {
            return ['status' => 'error', 'message' => 'Username already taken.'];
        } else {
            return ['status' => 'error', 'message' => 'Error creating user.'];
        }
    }

    /**
     * Logs in a user.
     *
     * @param string $username The username of the user.
     * @param string $password The password of the user.
     * @return array Returns an array with 'status', 'message', 'access_token', and 'refresh_token' on success, or 'status' and 'message' on failure.
     */
    public function login($username, $password) {
        // 1. Input Validation:
        $username = trim($username);
        $password = trim($password);

        if (empty($username) || empty($password)) {
            return ['status' => 'error', 'message' => 'Username and password are required.'];
        }

        // 2. Get User Data:
        $user = $this->signin($username, $password);

        // 3. Verify Password and Generate Tokens:
        if ($user && password_verify($password, $user['password'])) {

            // Invalidate existing tokens
            $this->invalidateTokens($user['id']);

            // Generate new tokens
            $accessToken = $this->genToken($user['id'], 'access', 15);  // 15 minutes for access token
            $refreshToken = $this->genToken($user['id'], 'refresh', 30 * 24 * 60); // 30 days for refresh token

            // 4. Return Success with Tokens:
            return [
                'status' => 'success',
                'message' => 'Login successful.',
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken
            ];
        } else {
            return ['status' => 'error', 'message' => 'Invalid username or password.'];
        }
    }

    /**
     * Logs out a user by deleting their access token.
     *
     * @param string $accessToken The access token to delete.
     * @return array Returns an array with 'status' and 'message'.
     */
    public function logout($accessToken) {
        if ($this->deleteToken($accessToken)) {
            return ['status' => 'success', 'message' => 'Logged out successfully.'];
        } else {
            return ['status' => 'error', 'message' => 'Error during logout.'];
        }
    }

    /**
     * Generates a cryptographically secure random token.
     *
     * @param int $userId The ID of the user the token is associated with.
     * @param string $tokenType The type of token ('access' or 'refresh').
     * @param int $expiryMinutes The token's expiration time in minutes.
     * @return mixed Returns the generated token on success, false on failure.
     */
    public function genToken($userId, $tokenType, $expiryMinutes) {
        $token = bin2hex(random_bytes(32)); // Generate a cryptographically secure token
        $created = date("Y-m-d H:i:s");
        $expiry = date("Y-m-d H:i:s", strtotime("+$expiryMinutes minutes"));

        return $this->writeTokensOnDb($userId, $token, $tokenType, $created, $expiry);
    }

     /**
     * Refreshes an access token using a refresh token.
     *
     * @param string $refreshToken The refresh token.
     * @return array Returns an array with 'status', 'message', and 'access_token' on success, or 'status' and 'message' on failure.
     */
    public function refreshAccessToken($refreshToken) {
        // 1. Get Token Data and Validate:
        $tokenData = $this->getTokenData($refreshToken); 

        if (!$tokenData || $tokenData['type'] !== 'refresh') {
            return ['status' => 'error', 'message' => 'Invalid refresh token.'];
        }

        // 2. Check Expiry:
        if (strtotime($tokenData['expires_at']) < time()) {
            return ['status' => 'error', 'message' => 'Expired refresh token.'];
        }

        // 3. Invalidate Old Access Token:
        $this->invalidateAccessTokenOnly($tokenData['user_id'], $tokenData['access_token_to_be_invalidated']); // Use 'access_token_to_be_invalidated'

        // 4. Generate New Access Token:
        $newAccessToken = $this->genToken($tokenData['user_id'], 'access', 15); // 15 minutes

        if ($newAccessToken) {
            // 5. [Optional] Implement Refresh Token Rotation Here

            return [
                'status' => 'success',
                'message' => 'Access token refreshed.',
                'access_token' => $newAccessToken
            ];
        } else {
            return ['status' => 'error', 'message' => 'Failed to generate new access token.'];
        }
    }

    /**
     * Validates a given token.
     *
     * @param string $token The token to validate.
     * @return mixed Returns an array with 'user_id' and 'type' if the token is valid, false otherwise.
     */
    public function validateToken($token) {
        $result = $this->isValidateToken($token);
    
        if ($result === -1) {
            return [
                'status' => 'expired',
                'message' => 'Access token is expired.'
            ];
        } elseif ($result) {
            return [
                'status' => 'success',
                'message' => 'Token is valid.',
                'user_id' => $result['user_id'],
                'type' => $result['type']
            ];
        } else {
            return ['status' => 'error', 'message' => 'Invalid access token.'];
        }
    }
}

?>