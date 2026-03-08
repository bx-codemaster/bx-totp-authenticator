<?php
/**
 * BX Two-Factor Authentication - Main Handler
 * 
 * Central class for managing two-factor authentication (TOTP + Email).
 * Orchestrates bx_totp_helper and bx_two_factor_email_handler.
 * 
 * @package    BX Two-Factor Auth
 * @version    1.0.0
 * @author     benax
 * @copyright  2026
 * @license    GPL
 */

class bx_two_factor_auth {
    
    /**
     * TOTP Handler
     * @var bx_totp_helper
     */
    private $totp;
    
    /**
     * Email Code Handler
     * @var bx_two_factor_email_handler
     */
    private $email;
    
    /**
     * Number of backup codes to generate
     */
    private const BACKUP_CODES_COUNT = 10;
    
    /**
     * Constructor - Initialize handlers
     */
    public function __construct() {
        // Load TOTP handler (from admin classes, shared)
        if (!class_exists('bx_totp_helper')) {
            require_once(DIR_FS_CATALOG . 'admin/includes/classes/bx_totp_helper.php');
        }
        
        // Load Email handler
        if (!class_exists('bx_two_factor_email_handler')) {
            require_once(DIR_FS_CATALOG . 'includes/classes/bx_two_factor_email_handler.php');
        }
        
        $this->totp  = new bx_totp_helper();
        $this->email = new bx_two_factor_email_handler();
    }
    
    /**
     * Check if 2FA is enabled for customer
     * 
     * @param int $customerId Customer ID
     * @return bool True if enabled 
     */
    public function isEnabled(int $customerId): bool {
        $query = "SELECT two_factor_enabled FROM " . TABLE_CUSTOMERS . " 
                  WHERE customers_id = '" . (int)$customerId . "'";
        
        $result = xtc_db_query($query);
        
        if (xtc_db_num_rows($result) === 0) {
            return false;
        }
        
        $data = xtc_db_fetch_array($result);
        return (bool)$data['two_factor_enabled'];
    }
    
    /**
     * Get active 2FA method for customer
     * 
     * @param int $customerId Customer ID
     * @return string|null 'totp', 'email' or null if not enabled
     */
    public function getMethod(int $customerId): ?string {
        if (!$this->isEnabled($customerId)) {
            return null;
        }
        
        $query = "SELECT two_factor_method FROM " . TABLE_CUSTOMERS . " 
                  WHERE customers_id = '" . (int)$customerId . "'";
        
        $result = xtc_db_query($query);
        $data = xtc_db_fetch_array($result);
        
        return $data['two_factor_method'] ?? null;
    }
    
    /**
     * Get all 2FA data for customer
     * 
     * @param int $customerId Customer ID
     * @return array|false Customer 2FA data or false
     */
    public function getCustomerData(int $customerId) {
        $query = "SELECT 
                    two_factor_enabled,
                    two_factor_method,
                    two_factor_totp_secret,
                    two_factor_backup_codes,
                    two_factor_secret_created
                  FROM " . TABLE_CUSTOMERS . " 
                  WHERE customers_id = '" . (int)$customerId . "'";
        
        $result = xtc_db_query($query);
        
        if (xtc_db_num_rows($result) === 0) {
            return false;
        }
        
        return xtc_db_fetch_array($result);
    }
    
    /**
     * Enable 2FA for customer with specified method
     * 
     * @param int $customerId Customer ID
     * @param string $method 'totp' or 'email'
     * @param string|null $totpSecret TOTP secret (required for totp method)
     * @return array ['success' => bool, 'message' => string, 'backup_codes' => array]
     */
    public function enable(int $customerId, string $method, ?string $totpSecret = null): array {
        // Validate method
        if (!in_array($method, ['totp', 'email'])) {
            return [
                'success' => false,
                'message' => 'Ungültige Methode.',
                'backup_codes' => []
            ];
        }
        
        // For TOTP, secret is required
        if ($method === 'totp' && empty($totpSecret)) {
            return [
                'success' => false,
                'message' => 'TOTP-Secret fehlt.',
                'backup_codes' => []
            ];
        }
        
        // Generate backup codes
        $backupCodes = $this->generateBackupCodesRaw();
        $hashedBackupCodes = $this->hashBackupCodes($backupCodes);
        $backupCodesJson = json_encode($hashedBackupCodes);
        
        // Update database
        $updateQuery = "UPDATE " . TABLE_CUSTOMERS . " 
                        SET two_factor_enabled = 1,
                            two_factor_method = '" . xtc_db_input($method) . "',
                            two_factor_totp_secret = '" . xtc_db_input($totpSecret ?? '') . "',
                            two_factor_backup_codes = '" . xtc_db_input($backupCodesJson) . "',
                            two_factor_secret_created = NOW()
                        WHERE customers_id = '" . (int)$customerId . "'";
        
        xtc_db_query($updateQuery);
        
        return [
            'success' => true,
            'message' => '2FA erfolgreich aktiviert.',
            'backup_codes' => $backupCodes
        ];
    }
    
    /**
     * Disable 2FA for customer
     * 
     * @param int $customerId Customer ID
     * @return array ['success' => bool, 'message' => string]
     */
    public function disable(int $customerId): array {
        $updateQuery = "UPDATE " . TABLE_CUSTOMERS . " 
                        SET two_factor_enabled = 0,
                            two_factor_method = 'totp',
                            two_factor_totp_secret = NULL,
                            two_factor_backup_codes = NULL,
                            two_factor_secret_created = '1000-01-01 00:00:00' 
                        WHERE customers_id = '" . (int)$customerId . "'";
        
        xtc_db_query($updateQuery);
        
        return [
            'success' => true,
            'message' => '2FA wurde deaktiviert.'
        ];
    }
    
    /**
     * Verify a 2FA code (TOTP or Email)
     * 
     * @param int $customerId Customer ID
     * @param string $code The code to verify
     * @return array ['success' => bool, 'message' => string]
     */
    public function verify(int $customerId, string $code): array {
        if (!$this->isEnabled($customerId)) {
            return [
                'success' => false,
                'message' => '2FA ist nicht aktiviert.'
            ];
        }
        
        $method = $this->getMethod($customerId);
        
        if ($method === 'totp') {
            return $this->verifyTOTP($customerId, $code);
        } elseif ($method === 'email') {
            return $this->email->verifyCode($customerId, $code);
        }
        
        return [
            'success' => false,
            'message' => 'Ungültige 2FA-Methode.'
        ];
    }
    
    /**
     * Verify TOTP code
     * 
     * @param int $customerId Customer ID
     * @param string $code TOTP code
     * @return array ['success' => bool, 'message' => string]
     */
    private function verifyTOTP(int $customerId, string $code): array {
        $customerData = $this->getCustomerData($customerId);
        
        if (!$customerData || empty($customerData['two_factor_totp_secret'])) {
            return [
                'success' => false,
                'message' => 'TOTP-Secret nicht gefunden.'
            ];
        }
        
        $isValid = $this->totp->verifyCode(
            $customerData['two_factor_totp_secret'],
            $code,
            2 // tolerance (±60 seconds)
        );
        
        if ($isValid) {
            return [
                'success' => true,
                'message' => 'Code erfolgreich verifiziert.'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Ungültiger Code.'
        ];
    }
    
    /**
     * Verify backup code
     * 
     * @param int $customerId Customer ID
     * @param string $code Backup code
     * @return array ['success' => bool, 'message' => string, 'remaining_codes' => int]
     */
    public function verifyBackupCode(int $customerId, string $code): array {
        $customerData = $this->getCustomerData($customerId);
        
        if (!$customerData || empty($customerData['two_factor_backup_codes'])) {
            return [
                'success' => false,
                'message' => 'Keine Backup-Codes vorhanden.',
                'remaining_codes' => 0
            ];
        }
        
        $backupCodes = json_decode($customerData['two_factor_backup_codes'], true);
        
        if (!is_array($backupCodes)) {
            return [
                'success' => false,
                'message' => 'Fehler beim Lesen der Backup-Codes.',
                'remaining_codes' => 0
            ];
        }
        
        // Check each backup code
        $codeFound = false;
        $codeIndex = null;
        
        foreach ($backupCodes as $index => $hashedCode) {
            if ($hashedCode['used']) {
                continue;
            }
            
            if (password_verify($code, $hashedCode['hash'])) {
                $codeFound = true;
                $codeIndex = $index;
                break;
            }
        }
        
        if (!$codeFound) {
            return [
                'success' => false,
                'message' => 'Ungültiger Backup-Code.',
                'remaining_codes' => $this->countRemainingBackupCodes($backupCodes)
            ];
        }
        
        // Mark code as used
        $backupCodes[$codeIndex]['used'] = true;
        $backupCodesJson = json_encode($backupCodes);
        
        $updateQuery = "UPDATE " . TABLE_CUSTOMERS . " 
                        SET two_factor_backup_codes = '" . xtc_db_input($backupCodesJson) . "'
                        WHERE customers_id = '" . (int)$customerId . "'";
        
        xtc_db_query($updateQuery);
        
        $remainingCodes = $this->countRemainingBackupCodes($backupCodes);
        
        return [
            'success' => true,
            'message' => 'Backup-Code erfolgreich verwendet.',
            'remaining_codes' => $remainingCodes
        ];
    }
    
    /**
     * Generate new backup codes for customer
     * 
     * @param int $customerId Customer ID
     * @return array ['success' => bool, 'message' => string, 'backup_codes' => array]
     */
    public function regenerateBackupCodes(int $customerId): array {
        if (!$this->isEnabled($customerId)) {
            return [
                'success' => false,
                'message' => '2FA muss aktiviert sein.',
                'backup_codes' => []
            ];
        }
        
        // Generate new codes
        $backupCodes = $this->generateBackupCodesRaw();
        $hashedBackupCodes = $this->hashBackupCodes($backupCodes);
        $backupCodesJson = json_encode($hashedBackupCodes);
        
        // Update database
        $updateQuery = "UPDATE " . TABLE_CUSTOMERS . " 
                        SET two_factor_backup_codes = '" . xtc_db_input($backupCodesJson) . "'
                        WHERE customers_id = '" . (int)$customerId . "'";
        
        xtc_db_query($updateQuery);
        
        return [
            'success' => true,
            'message' => 'Neue Backup-Codes wurden generiert.',
            'backup_codes' => $backupCodes
        ];
    }
    
    /**
     * Get remaining backup codes count
     * 
     * @param int $customerId Customer ID
     * @return int Number of remaining codes
     */
    public function getRemainingBackupCodesCount(int $customerId): int {
        $customerData = $this->getCustomerData($customerId);
        
        if (!$customerData || empty($customerData['two_factor_backup_codes'])) {
            return 0;
        }
        
        $backupCodes = json_decode($customerData['two_factor_backup_codes'], true);
        
        if (!is_array($backupCodes)) {
            return 0;
        }
        
        return $this->countRemainingBackupCodes($backupCodes);
    }
    
    /**
     * Count remaining (unused) backup codes
     * 
     * @param array $backupCodes Array of backup codes
     * @return int Count
     */
    private function countRemainingBackupCodes(array $backupCodes): int {
        $count = 0;
        foreach ($backupCodes as $code) {
            if (!$code['used']) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Generate raw backup codes (not hashed)
     * 
     * @return array Array of codes (strings)
     */
    private function generateBackupCodesRaw(): array {
        $codes = [];
        
        for ($i = 0; $i < self::BACKUP_CODES_COUNT; $i++) {
            // Generate 8-character alphanumeric code
            $codes[] = $this->generateRandomCode(8);
        }
        
        return $codes;
    }
    
    /**
     * Hash backup codes for storage
     * 
     * @param array $codes Raw codes
     * @return array Hashed codes with metadata
     */
    private function hashBackupCodes(array $codes): array {
        $hashed = [];
        
        foreach ($codes as $code) {
            $hashed[] = [
                'hash' => password_hash($code, PASSWORD_DEFAULT),
                'used' => false,
                'created' => date('Y-m-d H:i:s')
            ];
        }
        
        return $hashed;
    }
    
    /**
     * Generate random alphanumeric code
     * 
     * @param int $length Length of code
     * @return string Random code
     */
    private function generateRandomCode(int $length): string {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Remove ambiguous chars
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        // Format as XXXX-XXXX for readability
        if ($length === 8) {
            return substr($code, 0, 4) . '-' . substr($code, 4, 4);
        }
        
        return $code;
    }
    
    /**
     * Create new TOTP secret for customer setup
     * 
     * @return string TOTP secret
     */
    public function createTOTPSecret(): string {
        return $this->totp->createSecret(32);
    }
    
    /**
     * Generate QR code for TOTP setup
     * 
     * @param string $secret TOTP secret
     * @param string $accountName Account identifier (usually email)
     * @param string $issuer Issuer name (shop name)
     * @return string|false Base64 encoded QR code image or false
     */
    public function generateTOTPQRCode(string $secret, string $accountName, string $issuer = 'Modified Shop') {
        try {
            return $this->totp->getQrCodeBase64($secret, $accountName, $issuer);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get TOTP provisioning URI (for manual entry)
     * 
     * @param string $secret TOTP secret
     * @param string $accountName Account identifier
     * @param string $issuer Issuer name
     * @return string URI
     */
    public function getTOTPUri(string $secret, string $accountName, string $issuer = 'Modified Shop'): string {
        return $this->totp->getOtpauthUrl($secret, $accountName, $issuer);
    }
    
    /**
     * Send email code for email-based 2FA
     * 
     * @param int $customerId Customer ID
     * @return array ['success' => bool, 'message' => string, 'expires_in' => int]
     */
    public function sendEmailCode(int $customerId): array {
        return $this->email->sendCode($customerId);
    }
    
    /**
     * Get remaining time for current email code
     * 
     * @param int $customerId Customer ID
     * @return int Seconds remaining
     */
    public function getEmailCodeRemainingTime(int $customerId): int {
        return $this->email->getRemainingTime($customerId);
    }
    
    /**
     * Change 2FA method for customer
     * 
     * @param int $customerId Customer ID
     * @param string $newMethod New method ('totp' or 'email')
     * @param string|null $totpSecret TOTP secret (required if switching to totp)
     * @return array ['success' => bool, 'message' => string]
     */
    public function changeMethod(int $customerId, string $newMethod, ?string $totpSecret = null): array {
        if (!$this->isEnabled($customerId)) {
            return [
                'success' => false,
                'message' => '2FA ist nicht aktiviert.'
            ];
        }
        
        if (!in_array($newMethod, ['totp', 'email'])) {
            return [
                'success' => false,
                'message' => 'Ungültige Methode.'
            ];
        }
        
        if ($newMethod === 'totp' && empty($totpSecret)) {
            return [
                'success' => false,
                'message' => 'TOTP-Secret fehlt.'
            ];
        }
        
        $updateQuery = "UPDATE " . TABLE_CUSTOMERS . " 
                        SET two_factor_method = '" . xtc_db_input($newMethod) . "',
                            two_factor_totp_secret = '" . xtc_db_input($totpSecret ?? '') . "'
                        WHERE customers_id = '" . (int)$customerId . "'";
        
        xtc_db_query($updateQuery);
        
        return [
            'success' => true,
            'message' => 'Methode erfolgreich geändert.'
        ];
    }
}
