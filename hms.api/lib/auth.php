<?php

use \Firebase\JWT\JWT;

class Auth { 

    private $key = "secretSignKey";

    public function generate_jwt($user_data) {        
        $payload = array(
            'user_id' => $user_data['user_id'],
            'fullname' => $user_data['firstname'].' '.$user_data['lastname'],
            'email' => $user_data['email'],
            'role_id' => $user_data['role_id'],
            'exp' => time() + (60*60)
        );

        return JWT::encode($payload, $this->key);
    }

    public function is_jwt_valid($token) {
        $res = array(false, '');

        try {            
            $decoded = JWT::decode($token, $this->key, array('HS256'));
        } catch (Exception $e) {
            return $res;
        }

        $res[0] = true;
        $res[1] = (array)$decoded;
        return $res;
    }

    function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
?>