<?php
/**
 * BX Two-Factor Authentication - E-Mail Code Handler
 * 
 * Handles generation, sending, and verification of email-based 2FA codes.
 * 
 * @package    BX Two-Factor Auth
 * @version    1.0.0
 * @author     benax
 * @copyright  2026
 * @license    GPL
 */

class bx_two_factor_email_handler {
    
    /**
     * Code validity in minutes
     */
    private const CODE_VALIDITY_MINUTES = 5;
    
    /**
     * Maximum codes per hour (rate limiting)
     */
    private const MAX_CODES_PER_HOUR = 5;
    
    /**
     * Code length
     */
    private const CODE_LENGTH = 6;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Cleanup expired codes on instantiation
        $this->cleanExpiredCodes();
    }
    
    /**
     * Generate and send a new code to customer
     * 
     * @param int $customerId Customer ID
     * @return array ['success' => bool, 'message' => string, 'expires_in' => int]
     */
    public function sendCode(int $customerId): array {
        // Check rate limiting
        $rateLimitCheck = $this->checkRateLimit($customerId);
        if (!$rateLimitCheck['allowed']) {
            return [
                'success' => false,
                'message' => 'Zu viele Anfragen. Bitte warten Sie ' . $rateLimitCheck['wait_minutes'] . ' Minuten.',
                'expires_in' => 0
            ];
        }
        
        // Get customer email
        $customer = $this->getCustomerData($customerId);
        if (!$customer) {
            return [
                'success' => false,
                'message' => 'Kunde nicht gefunden.',
                'expires_in' => 0
            ];
        }
        
        // Generate code
        $code = $this->generateCode();
        
        // Calculate expiry
        $createdAt = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::CODE_VALIDITY_MINUTES . ' minutes'));
        
        // Save to database
        $insertQuery = "INSERT INTO two_factor_email_codes 
                        (customers_id, code, created_at, expires_at, used) 
                        VALUES (
                            '" . (int)$customerId . "',
                            '" . xtc_db_input($code) . "',
                            '" . xtc_db_input($createdAt) . "',
                            '" . xtc_db_input($expiresAt) . "',
                            0
                        )";
        
        xtc_db_query($insertQuery);
        
        // Send email
        $emailSent = $this->sendEmail($customer, $code);
        
        if (!$emailSent) {
            return [
                'success' => false,
                'message' => 'E-Mail konnte nicht versendet werden.',
                'expires_in' => 0
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Code wurde an ' . $this->maskEmail($customer['customers_email_address']) . ' gesendet.',
            'expires_in' => self::CODE_VALIDITY_MINUTES * 60 // in Sekunden
        ];
    }
    
    /**
     * Verify a code for a customer
     * 
     * @param int $customerId Customer ID
     * @param string $code The code to verify
     * @return array ['success' => bool, 'message' => string]
     */
    public function verifyCode(int $customerId, string $code): array {
        // Clean input
        $code = preg_replace('/[^0-9]/', '', $code);
        
        if (strlen($code) !== self::CODE_LENGTH) {
            return [
                'success' => false,
                'message' => 'Ungültiger Code-Format.'
            ];
        }
        
        // Get latest unused code for customer
        $query = "SELECT * FROM two_factor_email_codes 
                  WHERE customers_id = '" . (int)$customerId . "' 
                  AND code = '" . xtc_db_input($code) . "' 
                  AND used = 0 
                  AND expires_at > NOW() 
                  ORDER BY created_at DESC 
                  LIMIT 1";
        
        $result = xtc_db_query($query);
        
        if (xtc_db_num_rows($result) === 0) {
            return [
                'success' => false,
                'message' => 'Ungültiger oder abgelaufener Code.'
            ];
        }
        
        $codeData = xtc_db_fetch_array($result);
        
        // Mark code as used
        $updateQuery = "UPDATE two_factor_email_codes 
                        SET used = 1 
                        WHERE id = '" . (int)$codeData['id'] . "'";
        xtc_db_query($updateQuery);
        
        return [
            'success' => true,
            'message' => 'Code erfolgreich verifiziert.'
        ];
    }
    
    /**
     * Generate a random 6-digit code
     * 
     * @return string 6-digit code
     */
    private function generateCode(): string {
        return str_pad((string)random_int(0, 999999), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }
    
    /**
     * Check if customer has exceeded rate limit
     * 
     * @param int $customerId Customer ID
     * @return array ['allowed' => bool, 'wait_minutes' => int]
     */
    private function checkRateLimit(int $customerId): array {
        $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        $query = "SELECT COUNT(*) as code_count, MIN(created_at) as oldest_code 
                  FROM two_factor_email_codes 
                  WHERE customers_id = '" . (int)$customerId . "' 
                  AND created_at > '" . xtc_db_input($oneHourAgo) . "'
                  AND used = 0";
        
        $result = xtc_db_query($query);
        $data = xtc_db_fetch_array($result);
        
        if ($data['code_count'] >= self::MAX_CODES_PER_HOUR) {
            $waitUntil = strtotime($data['oldest_code']) + 3600; // 1 hour
            $waitMinutes = max(1, ceil(($waitUntil - time()) / 60));
            
            return [
                'allowed' => false,
                'wait_minutes' => $waitMinutes
            ];
        }
        
        return [
            'allowed' => true,
            'wait_minutes' => 0
        ];
    }
    
    /**
     * Get customer data
     * 
     * @param int $customerId Customer ID
     * @return array|false Customer data or false
     */
    private function getCustomerData(int $customerId) {
        $query = "SELECT customers_id, customers_gender, customers_firstname, customers_lastname, customers_email_address 
                  FROM " . TABLE_CUSTOMERS . " 
                  WHERE customers_id = '" . (int)$customerId . "'";
        
        $result = xtc_db_query($query);
        
        if (xtc_db_num_rows($result) === 0) {
            return false;
        }
        
        return xtc_db_fetch_array($result);
    }
    
    /**
     * Send email with code to customer
     * 
     * @param array $customer Customer data
     * @param string $code The code
     * @return bool Success
     */
    private function sendEmail(array $customer, string $code): bool {
        $shopName      = STORE_NAME;
        $customerName  = $customer['customers_firstname'] . ' ' . $customer['customers_lastname'];
        $customerEmail = $customer['customers_email_address'];
        
        // Smarty für E-Mail-Template
        $smarty = new Smarty();
        
        // Get language from session or default
        $language = isset($_SESSION['language']) ? $_SESSION['language'] : 'german';
        
        // Variablen für Template
        $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
        $smarty->assign('shop_name', $shopName);
        $smarty->assign('customer_firstname', $customer['customers_firstname']);
        $smarty->assign('customer_lastname', $customer['customers_lastname']);
        $smarty->assign('customer_name', $customerName);
        $smarty->assign('customer_gender', $customer['customers_gender']);
        $smarty->assign('code', $code);
        $smarty->assign('validity_minutes', self::CODE_VALIDITY_MINUTES);
        
        // Email subject
        $subject = $shopName . ' - Ihr Zwei-Faktor-Authentifizierungs-Code';
        
        // Template paths
        $templatePath = CURRENT_TEMPLATE . '/admin/mail/' . $language . '/';
        $htmlTemplate = $templatePath . 'bx_two_factor_code_email.html';
        $txtTemplate  = $templatePath . 'bx_two_factor_code_email.txt';
        
        // Check if templates exist, otherwise use fallback
        if (file_exists(DIR_FS_CATALOG . 'templates/' . $htmlTemplate)) {
            $message_html = $smarty->fetch($htmlTemplate);
        } else {
            // Fallback HTML
            $message_html = "<html><body>";
            $message_html .= "<p>Hallo " . $customerName . ",</p>";
            $message_html .= "<p>Sie haben einen Code für die Zwei-Faktor-Authentifizierung angefordert.</p>";
            $message_html .= "<p style='font-size: 24px; font-weight: bold; letter-spacing: 3px; color: #0066cc;'>" . $code . "</p>";
            $message_html .= "<p>Dieser Code ist " . self::CODE_VALIDITY_MINUTES . " Minuten gültig.</p>";
            $message_html .= "<p>Falls Sie diesen Code nicht angefordert haben, ignorieren Sie diese E-Mail bitte.</p>";
            $message_html .= "<p>Mit freundlichen Grüßen<br>Ihr " . $shopName . " Team</p>";
            $message_html .= "</body></html>";
        }
        
        if (file_exists(DIR_FS_CATALOG . 'templates/' . $txtTemplate)) {
            $message_plain = $smarty->fetch($txtTemplate);
        } else {
            // Fallback Plain Text
            $message_plain = "Hallo " . $customerName . ",\n\n";
            $message_plain .= "Sie haben einen Code für die Zwei-Faktor-Authentifizierung angefordert.\n\n";
            $message_plain .= "Ihr Code lautet: " . $code . "\n\n";
            $message_plain .= "Dieser Code ist " . self::CODE_VALIDITY_MINUTES . " Minuten gültig.\n\n";
            $message_plain .= "Falls Sie diesen Code nicht angefordert haben, ignorieren Sie diese E-Mail bitte.\n\n";
            $message_plain .= "Mit freundlichen Grüßen\n";
            $message_plain .= "Ihr " . $shopName . " Team";
        }
        
        // Send email using Modified's mail function
        return xtc_php_mail(
            EMAIL_BILLING_ADDRESS,
            EMAIL_BILLING_ADDRESS,
            $customerEmail,
            $customerName,
            '',
            '',
            '',
            '',
            '',
            $subject,
            $message_html,
            $message_plain
            );
    }
    
    /**
     * Mask email address for display (e.g., j***@example.com)
     * 
     * @param string $email Email address
     * @return string Masked email
     */
    private function maskEmail(string $email): string {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }
        
        $local = $parts[0];
        $domain = $parts[1];
        
        if (strlen($local) <= 2) {
            return $local[0] . '***@' . $domain;
        }
        
        return $local[0] . str_repeat('*', min(3, strlen($local) - 1)) . '@' . $domain;
    }
    
    /**
     * Clean up expired codes from database
     * Removes codes older than 24 hours
     * 
     * @return int Number of deleted codes
     */
    private function cleanExpiredCodes(): int {
        $twentyFourHoursAgo = date('Y-m-d H:i:s', strtotime('-24 hours'));
        
        $deleteQuery = "DELETE FROM two_factor_email_codes 
                        WHERE expires_at < '" . xtc_db_input($twentyFourHoursAgo) . "'";
        
        xtc_db_query($deleteQuery);
        
        return xtc_db_affected_rows();
    }
    
    /**
     * Get remaining valid time for last code (for display purposes)
     * 
     * @param int $customerId Customer ID
     * @return int Remaining seconds (0 if no valid code)
     */
    public function getRemainingTime(int $customerId): int {
        $query = "SELECT expires_at FROM two_factor_email_codes 
                  WHERE customers_id = '" . (int)$customerId . "' 
                  AND used = 0 
                  AND expires_at > NOW() 
                  ORDER BY created_at DESC 
                  LIMIT 1";
        
        $result = xtc_db_query($query);
        
        if (xtc_db_num_rows($result) === 0) {
            return 0;
        }
        
        $data = xtc_db_fetch_array($result);
        $expiresAt = strtotime($data['expires_at']);
        $now = time();
        
        return max(0, $expiresAt - $now);
    }
}
