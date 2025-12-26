<?php

class JwtHelper {
    private $secretKey = "your-secret-key-change-this";
    private $algorithm = "HS256";
    
    /**
     * Generate JWT Token
     */
    public function generateToken($userId, $email) {
        $issuedAt = time();
        $expiresAt = $issuedAt + (7 * 24 * 60 * 60); // 7 days
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'userId' => $userId,
            'email' => $email
        ];
        
        $header = base64UrlEncode(json_encode(['alg' => $this->algorithm, 'typ' => 'JWT']));
        $payload = base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', "$header.$payload", $this->secretKey, true);
        $signature = base64UrlEncode($signature);
        
        return "$header.$payload.$signature";
    }
    
    /**
     * Verify JWT Token
     */
    public function verifyToken($token) {
        try {
            $parts = explode('.', $token);
            if (count($parts) != 3) {
                return ['valid' => false, 'message' => 'Invalid token format'];
            }
            
            list($header, $payload, $signature) = $parts;
            
            // Verify signature
            $validSignature = hash_hmac('sha256', "$header.$payload", $this->secretKey, true);
            $validSignature = base64UrlEncode($validSignature);
            
            if ($signature !== $validSignature) {
                return ['valid' => false, 'message' => 'Invalid signature'];
            }
            
            // Decode and verify payload
            $decodedPayload = json_decode(base64UrlDecode($payload), true);
            
            // Check expiration
            if ($decodedPayload['exp'] < time()) {
                return ['valid' => false, 'message' => 'Token expired'];
            }
            
            return [
                'valid' => true,
                'userId' => $decodedPayload['userId'],
                'email' => $decodedPayload['email']
            ];
        } catch (Exception $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }
    }
}

/**
 * Base64 URL Encode
 */
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Base64 URL Decode
 */
function base64UrlDecode($data) {
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 4 - strlen($data) % 4));
}
?>