<?php
class FormFieldGenerator
{
    private $session;
    private $get;
    private $server;
    private $config;

    public function __construct($session = [], $get = [], $server = [], $config = [])
    {
        $this->session = $session;
        $this->get = $get;
        $this->server = $server;
        $this->config = $config;
    }

    /**
     * Sanitize and validate input
     */
    private function sanitize($value, $type = 'string', $maxLength = 255)
    {
        if (empty($value)) {
            return '';
        }

        switch ($type) {
            case 'id':
                return preg_replace('/[^a-zA-Z0-9_-]/', '', substr($value, 0, $maxLength));

            case 'url':
                return filter_var($value, FILTER_SANITIZE_URL);

            case 'ip':
                return filter_var($value, FILTER_VALIDATE_IP) ? $value : '';

            case 'int':
                return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);

            case 'base64':
                // Only encode safe strings
                $safe = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                return base64_encode($safe);

            default:
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * Generate hidden input field
     */
    private function hiddenField($name, $value, $id = null)
    {
        $idAttr = $id ? sprintf('id="%s"', $this->sanitize($id, 'id')) : '';
        $nameAttr = $this->sanitize($name, 'id');
        $valueAttr = $this->sanitize($value);

        return sprintf(
            '<input type="hidden" name="%s" %s value="%s" />',
            $nameAttr,
            $idAttr,
            $valueAttr
        );
    }

    /**
     * Get value with fallback chain
     */
    private function getValue($keys, $default = '')
    {
        foreach ($keys as $source => $key) {
            $value = null;

            switch ($source) {
                case 'session':
                    $value = $this->session[$key] ?? null;
                    break;
                case 'get':
                    $value = $this->get[$key] ?? null;
                    break;
                case 'config':
                    $value = $this->config[$key] ?? null;
                    break;
            }

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Generate campaign tracking fields
     */
    public function generateCampaignFields()
    {
        $output = [];

        // Get campaign configuration
        $campaignConfig = TrackingConfig::getCampaignConfig($this->session);

        // TYPE field
        $output[] = $this->hiddenField('TYPE', $campaignConfig['type'], 'type');

        // SRC field - use the value from config (don't override with campaign name!)
        $srcValue = $campaignConfig['src'];
        // Remove this override logic:
        // if (isset($this->session['campaign'])) {
        //     $srcValue = $this->session['campaign'];
        // }
        $output[] = $this->hiddenField('SRC', $srcValue, 'src');

        return implode("\n", $output);
    }

    /**
     * Generate tracking pixel fields using enhanced TrackingConfig
     */
    public function generateTrackingFields()
    {
        // Use the new enhanced tracking from TrackingConfig
        return TrackingConfig::generateTrackingFields($this->session, $this->get);
    }

    /**
     * Generate user data fields
     */
    public function generateUserFields()
    {
        $output = [];

        // IP Address (consider privacy implications)
        $ip = $this->getClientIp();
        $output[] = $this->hiddenField('IP_Address', $ip);

        // Publisher ID - already handled in TrackingConfig but keeping for legacy
        $pubId = $this->getValue([
            'session' => 'Pub_ID',
            'get' => 'Pub_ID'
        ], '');
        if ($pubId && !isset($this->get['Pub_ID'])) {
            // Only add if not already handled by TrackingConfig
            $output[] = $this->hiddenField('Pub_ID', $pubId);
        }

        // Sub ID with peak status fallback
        $subId = $this->getValue([
            'session' => 'Sub_ID'
        ], $this->getPeakStatus());
        $output[] = $this->hiddenField('Sub_ID', $subId);

        // HID (Sub_ID2)
        if (isset($this->session['Sub_ID2'])) {
            $output[] = $this->hiddenField('hid', $this->session['Sub_ID2']);
        }

        // NOTE: ad_id and adset_id are now handled by TrackingConfig
        // with proper priority handling, so we don't duplicate them here

        return implode("\n", $output);
    }

    /**
     * Generate page data fields (without duplicate tracking)
     */
    public function generatePageFields()
    {
        $output = [];

        // Preexisting List
        $preexisting = $this->getValue([
            'get' => 'Preexisting_List'
        ], '');
        if ($preexisting) {
            $output[] = $this->hiddenField('Preexisting_List', $preexisting);
        }

        // Landing Page ID
        $lpId = $this->config['pivot_lpid'] ?? '';
        $output[] = $this->hiddenField('LandingPageId', $lpId, 'LandingPageId');

        // Static fields
        $output[] = $this->hiddenField('Redirect_URL', '/thank-you/thank-you-h3-b.php', 'Redirect_URL');
        $output[] = $this->hiddenField('LeadiD_URL', '', 'LeadiD_URL');

        // Note: Landing_Page, notes, Search_Engine, keyword, and other tracking fields 
        // are now handled in generateTrackingFields() by TrackingConfig

        return implode("\n", $output);
    }

    /**
     * Generate location fields
     */
    public function generateLocationFields()
    {
        $output = [];

        // Age (will be populated by JavaScript)
        $age = $this->getValue(['get' => 'Age'], '');
        $output[] = $this->hiddenField('Age', $age, 'age');

        // Address
        $address = $this->getValue(['get' => 'Address'], '-');
        $output[] = sprintf(
            '<input name="Address" type="hidden" maxlength="100" value="%s" />',
            $this->sanitize($address)
        );

        // City - check session location data first
        $city = $this->session['location']['city'] ??
            $this->getValue(['get' => 'city'], '');
        $output[] = sprintf(
            '<input name="City" id="City" type="hidden" maxlength="100" value="%s" />',
            $this->sanitize($city)
        );

        // State - check session location data first
        $state = $this->session['location']['state'] ??
            $this->getValue(['get' => 'state'], '');
        $output[] = sprintf(
            '<input name="State" id="State" type="hidden" maxlength="100" value="%s" />',
            $this->sanitize($state)
        );

        // Zip - check session location data first
        $zipValue = $this->session['location']['zip'] ??
            $this->session['zip'] ??
            $this->get['zip'] ?? '';

        if (!empty($zipValue)) {
            $output[] = sprintf(
                '<input name="Zip" id="Zip" type="hidden" value="%s" />',
                $this->sanitize($zipValue, 'id', 10)
            );
        }

        return implode("\n", $output);
    }

    /**
     * Get client IP address safely
     */
    private function getClientIp()
    {
        // Check for forwarded IP (be careful with this in production)
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR'];

        foreach ($ipKeys as $key) {
            if (!empty($this->server[$key])) {
                $ip = $this->server[$key];
                // Handle comma-separated IPs
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $this->server['REMOTE_ADDR'] ?? '';
    }

    /**
     * Determine peak status
     */
    private function getPeakStatus()
    {
        $hour = (int) date('G');
        $dayOfWeek = (int) date('N');

        // Monday-Friday, 9 AM - 7 PM
        if ($dayOfWeek >= 1 && $dayOfWeek <= 5 && $hour >= 9 && $hour < 19) {
            return 'peak';
        }

        return 'off-peak';
    }

    /**
     * Generate all fields
     */
    public function generateAllFields()
    {
        $sections = [];

        // Campaign fields (TYPE and SRC)
        $sections[] = "<!-- Campaign Tracking -->";
        $sections[] = $this->generateCampaignFields();

        // Enhanced tracking fields (includes all UTM, click IDs, etc.)
        $sections[] = "\n<!-- Click Tracking & UTM Parameters -->";
        $sections[] = $this->generateTrackingFields();

        // User data fields
        $sections[] = "\n<!-- User Data -->";
        $sections[] = $this->generateUserFields();

        // Page data fields (without duplicate tracking)
        $sections[] = "\n<!-- Page Data -->";
        $sections[] = $this->generatePageFields();

        // Location data fields
        $sections[] = "\n<!-- Location Data -->";
        $sections[] = $this->generateLocationFields();

        return implode("\n\n", $sections);
    }
}
