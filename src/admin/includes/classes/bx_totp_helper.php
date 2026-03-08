<?php
declare(strict_types=1);
/**
 * TOTP (Time-based One-Time Password) Authenticator
 * RFC 6238 compliant implementation for Modified Shop
 * 
 * Provides secure two-factor authentication using TOTP algorithm
 * Compatible with Google Authenticator, Authy, and other TOTP apps
 * 
 * @package    Modified Shop
 * @subpackage Two-Factor Authentication
 * @author     benax
 * @version    1.0.0
 * @since      2026-01-19
 */

class bx_totp_helper
{
    /**
     * Length of the generated TOTP code
     * @var int
     */
    private int $codeLength;
    
    /**
     * Time step in seconds for TOTP calculation
     * @var int
     */
    private int $timeStep;


    public function __construct()   
    {
        $this->codeLength = 6;
        $this->timeStep = 30;
    }
    
    /**
     * Generate a new cryptographically secure secret key
     * 
     * @param int $length Length of the secret (16-128 characters)
     * @return string Base32 encoded secret
     * @throws InvalidArgumentException If length is invalid
     */
    public function createSecret(int $length = 16): string
    {
        if ($length < 16 || $length > 128) {
            throw new InvalidArgumentException('Secret length must be between 16 and 128');
        }
        
        $validChars  = $this->getBase32Chars();
        $secret      = '';
        $randomBytes = random_bytes($length);
        
        for ($i = 0; $i < $length; $i++) {
            $secret .= $validChars[ord($randomBytes[$i]) & 31];
        }
        
        return $secret;
    }
    
    /**
     * Generate TOTP code for given secret and time
     * 
     * Implements RFC 6238 TOTP algorithm:
     * 1. Calculate time counter (T = floor(unix_time / time_step))
     * 2. Decode Base32 secret to binary
     * 3. Generate HMAC-SHA1(secret, time_counter)
     * 4. Apply dynamic truncation to extract 31-bit code
     * 5. Convert to decimal and apply modulo for desired length
     * 
     * The algorithm ensures that the same code is generated for a 30-second window,
     * allowing synchronization between server and client authenticator apps.
     * 
     * @param string $secret Base32 encoded secret key (16-128 characters)
     * @param int|null $timeSlice Optional time counter (defaults to current time / 30)
     *                            Used internally for verification with tolerance
     * @return string Zero-padded numeric code (default: 6 digits, e.g., "042137")
     * 
     * @throws InvalidArgumentException If secret is invalid Base32
     * 
     * @see https://tools.ietf.org/html/rfc6238 RFC 6238 - TOTP Specification
     * @see https://tools.ietf.org/html/rfc4226#section-5.3 RFC 4226 - Dynamic Truncation
     */
    public function getCode(string $secret, ?int $timeSlice = null): string
    {
        // Step 1: Calculate time counter
        // If no specific time slice provided, use current time divided by time step (default 30s)
        // This ensures the code changes every 30 seconds
        if ($timeSlice === null) {
            $timeSlice = (int)floor(time() / $this->timeStep);
        }
        
        // Step 2: Decode the Base32 encoded secret to binary format
        // Base32 is used because it's URL-safe and case-insensitive for QR codes
        $secretKey = $this->base32Decode($secret);
        
        // Step 3: Pack time counter into 8-byte binary string (64-bit, big-endian)
        // Format: 4 bytes of zeros (high 32 bits) + 4 bytes of time counter (low 32 bits)
        // Big-endian ensures consistency across different platforms
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        
        // Step 4: Generate HMAC-SHA1 hash of the time counter using the secret key
        // HMAC ensures the hash can only be generated with the correct secret
        // Result is 20 bytes (160 bits) of cryptographically secure data
        $hash = hash_hmac('sha1', $time, $secretKey, true);
        
        // Step 5: Dynamic Truncation (RFC 6238 Section 5.3)
        // Extract the last 4 bits of the hash to determine offset (0-15)
        // This offset determines which 4 bytes to extract from the 20-byte hash
        // The 0x0F mask extracts only the lower 4 bits: binary AND with 00001111
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        
        // Extract 4 bytes starting at the dynamic offset position
        // These 4 bytes (32 bits) will be converted to our final code
        $truncatedHash = substr($hash, $offset, 4);
        
        // Step 6: Convert 4 bytes to 32-bit unsigned integer (big-endian)
        // unpack('N', ...) interprets 4 bytes as network byte order (big-endian) integer
        $value = unpack('N', $truncatedHash)[1];
        
        // Apply mask to remove the most significant bit (sign bit)
        // 0x7FFFFFFF = binary 01111111111111111111111111111111 (31 bits set to 1)
        // This ensures we get a positive 31-bit integer, removing any sign ambiguity
        // RFC 4226 requires this to ensure consistent behavior across platforms
        $value = $value & 0x7FFFFFFF;
        
        // Step 7: Generate the final numeric code
        // Calculate modulo to get a number with desired digit length
        // For 6-digit code: modulo = 10^6 = 1,000,000 (range: 0-999,999)
        $modulo = 10 ** $this->codeLength;
        
        // Convert to string and pad with leading zeros if necessary
        // Example: 42137 becomes "042137" for 6-digit code
        return str_pad((string)($value % $modulo), $this->codeLength, '0', STR_PAD_LEFT);
    }
    
    /**
     * Verify a TOTP code against a secret
     * 
     * @param string $secret Base32 encoded secret
     * @param string $code User-provided code to verify
     * @param int $tolerance Time drift tolerance in 30-second units (default: 2 = ±60 seconds)
     * @return bool True if code is valid
     */
    public function verifyCode(string $secret, string $code, int $tolerance = 2): bool
    {
        // Code must be exactly 6 digits
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            return false;
        }
        
        $currentTimeSlice = (int)floor(time() / $this->timeStep);
        
        // Check current time slot plus/minus tolerance
        for ($i = -$tolerance; $i <= $tolerance; $i++) {
            $calculatedCode = $this->getCode($secret, $currentTimeSlice + $i);
            
            // Timing-safe comparison to prevent timing attacks
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generate QR code as Base64 string for inline display
     * 
     * @param string $secret Base32 encoded secret
     * @param string $accountName User's email or account name
     * @param string $issuer Application name (default: 'Modified Shop')
     * @return string Base64 encoded PNG image
     * @throws RuntimeException If QR code library is not available 
     */
    public function getQrCodeBase64(string $secret, string $accountName, string $issuer = 'Modified Shop'): string
    {
        if (!defined('TOTP_QRCODE_AVAILABLE') || !TOTP_QRCODE_AVAILABLE) {
            throw new RuntimeException('QR-Code Library not available. Please install endroid/qr-code.');
        }
        
        // Build otpauth URI according to Google Authenticator specification
        // Format: otpauth://totp/ISSUER:ACCOUNT?secret=SECRET&issuer=ISSUER
        $otpauthUrl = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            rawurlencode($issuer),
            rawurlencode($accountName),
            $secret,
            rawurlencode($issuer)
        );
        
        try {
            // Generate QR code using Endroid QR Code library (v5+ API)
            $qrCode = new \Endroid\QrCode\QrCode(
                data: $otpauthUrl,
                size: 300,
                margin: 10
            );
            
            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qrCode);
            
            return base64_encode($result->getString());
        } catch (Exception $e) {
            throw new RuntimeException('Failed to generate QR code: ' . $e->getMessage());
        }
    }
    
    /**
     * Get otpauth URI for manual entry in authenticator apps
     * 
     * @param string $secret Base32 encoded secret
     * @param string $accountName User's email or account name
     * @param string $issuer Application name
     * @return string otpauth:// URI
     */
    public function getOtpauthUrl(string $secret, string $accountName, string $issuer = 'Modified Shop'): string
    {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            rawurlencode($issuer),
            rawurlencode($accountName),
            $secret,
            rawurlencode($issuer)
        );
    }
    
    /**
     * Decode Base32 encoded string
     * 
     * @param string $secret Base32 encoded string
     * @return string Binary decoded string
     * @throws InvalidArgumentException If invalid Base32 character found
     */
    private function base32Decode(string $secret): string
    {
        if (empty($secret)) {
            return '';
        }
        
        $base32Chars = $this->getBase32Chars();
        $base32Lookup = array_flip($base32Chars);
        
        // Remove padding
        $secret = rtrim($secret, '=');
        $secret = strtoupper($secret);
        
        $binaryString = '';
        
        // Convert each Base32 character to 5-bit binary
        foreach (str_split($secret) as $char) {
            if (!isset($base32Lookup[$char])) {
                throw new InvalidArgumentException('Invalid Base32 character: ' . $char);
            }
            $binaryString .= str_pad(decbin($base32Lookup[$char]), 5, '0', STR_PAD_LEFT);
        }
        
        // Convert binary string to bytes
        $result = '';
        foreach (str_split($binaryString, 8) as $byte) {
            if (strlen($byte) === 8) {
                $result .= chr((int)bindec($byte));
            }
        }
        
        return $result;
    }
    
    /**
     * Get Base32 character set (RFC 4648)
     * 
     * @return array Base32 characters
     */
    private function getBase32Chars(): array
    {
        return [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X',
            'Y', 'Z', '2', '3', '4', '5', '6', '7'
        ];
    }
    
    /**
     * Set custom code length
     * 
     * @param int $length Code length (minimum 6)
     * @return self
     */
    public function setCodeLength(int $length): self
    {
        if ($length < 6) {
            throw new InvalidArgumentException('Code length must be at least 6');
        }
        
        $this->codeLength = $length;
        return $this;
    }
    
    /**
     * Set custom time step
     * 
     * @param int $seconds Time step in seconds
     * @return self
     */
    public function setTimeStep(int $seconds): self
    {
        if ($seconds < 1) {
            throw new InvalidArgumentException('Time step must be at least 1 second');
        }
        
        $this->timeStep = $seconds;
        return $this;
    }
}