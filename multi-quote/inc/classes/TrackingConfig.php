<?php /*
class TrackingConfig {
    private static $campaigns = [
        'fb' => ['type' => 19, 'src' => 'Infinix-KFB'],
        'search_partners' => ['type' => 29, 'src' => 'InfinixMedia-Ksp'],
        'default' => ['type' => 24, 'src' => 'Infinix-K-Ping']
    ];
    
    private static $trackingParams = [
        'gclid' => 'Google Click ID',
        'msclkid' => 'Microsoft Click ID',
        'fbclid' => 'Facebook Click ID'
    ];
    
    public static function getCampaignConfig($session) {
        if (isset($session['fb']) && $session['fb'] === 'true') {
            return self::$campaigns['fb'];
        }
        
        if (isset($session['search_partners']) && $session['search_partners'] === 'Search_partners') {
            return self::$campaigns['search_partners'];
        }
        
        return self::$campaigns['default'];
    }
    
    public static function getTrackingParams() {
        return self::$trackingParams;
    }
}
    */

class TrackingConfig
{
    private static $campaigns = [
        'fb' => ['type' => 19, 'src' => 'Infinix-KFB'],
        'search_partners' => ['type' => 29, 'src' => 'InfinixMedia-Ksp'],
        'usha' => ['type' => 24, 'src' => 'Infinix-K-PingU'],
        'default' => ['type' => 24, 'src' => 'Infinix-K-Ping']
    ];

    // Primary tracking parameter mapping
    private static $trackingMapping = [
        'gclid' => 'gclid',
        'msclkid' => 'msclkid',
        'utm_source' => 'Search_Engine',
        'utm_campaign' => 'adset_id',
        'utm_content' => 'ad_id',
        'utm_term' => 'Keyword',
        'AdGroupId' => 'adset_id',
        'AdId' => 'ad_id',
        // Taboola
        't_clickid' => 'taboola_click_id',
        't_click' => 'taboola_click_id'
    ];

    // Priority mappings - later items override earlier ones
    private static $priorityMappings = [
        'adset_id' => ['utm_campaign', 'AdGroupId'],  // AdGroupId takes priority
        'ad_id' => ['utm_content', 'AdId']            // AdId takes priority
    ];

    // Additional tracking parameters to capture
    private static $additionalParams = [
        'fbclid' => 'Facebook Click ID',
        'ttclid' => 'TikTok Click ID',
        'gbraid' => 'Google Brand ID',
        'wbraid' => 'Google Web Brand ID',
        'utm_medium' => 'UTM Medium',
        'utm_id' => 'UTM ID',
        'referrer' => 'Referrer URL'
    ];

    // Taboola detection keys
    private static $taboolaKeys = ['t_clickid', 't_click'];

    // Legacy tracking params for backward compatibility
    private static $legacyTrackingParams = [
        'gclid' => 'Google Click ID',
        'msclkid' => 'Microsoft Click ID',
        'fbclid' => 'Facebook Click ID'
    ];

    /**
     * Get campaign configuration
     */
    public static function getCampaignConfig($session)
    {
        // If Taboola flag present, keep campaign mapping but allow downstream tracking to mark is_taboola

        // Priority 1: Check for specific campaign in session
        if (isset($session['campaign'])) {
            $campaign = strtolower($session['campaign']);

            // Check if this campaign exists in our configuration
            if (isset(self::$campaigns[$campaign])) {
                return self::$campaigns[$campaign];
            }

            // Handle special campaign mappings
            switch ($campaign) {
                case 'usha':
                    return self::$campaigns['usha'];
                case 'fb':
                case 'facebook':
                    return self::$campaigns['fb'];
                case 'search_partners':
                case 'sp':
                    return self::$campaigns['search_partners'];
            }
        }

        // Priority 2: Check legacy parameters
        if (isset($session['fb']) && $session['fb'] === 'true') {
            return self::$campaigns['fb'];
        }

        if (isset($session['search_partners']) && $session['search_partners'] === 'Search_partners') {
            return self::$campaigns['search_partners'];
        }

        // Priority 3: Check for usha as separate parameter
        if (isset($session['usha']) && in_array($session['usha'], ['true', '1', 'yes', 'usha'])) {
            return self::$campaigns['usha'];
        }

        // Default fallback
        return self::$campaigns['default'];
    }

    /**
     * Detect Taboola click
     */
    private static function isTaboola($session = [], $get = [])
    {
        $source = strtolower($get['utm_source'] ?? $session['utm_source'] ?? '');
        $hasSource = ($source === 'taboola');

        // Check click id keys
        foreach (self::$taboolaKeys as $key) {
            if (!empty($get[$key]) || !empty($session[$key])) {
                return true;
            }
        }

        return $hasSource;
    }

    /**
     * Public helper to detect Taboola requests
     */
    public static function isTaboolaRequest($session = [], $get = [])
    {
        return self::isTaboola($session, $get);
    }

    /**
     * Parse UTM parameters from the 'page' parameter
     */
    private static function parsePageParameter($pageUrl)
    {
        $utmParams = [];

        if (empty($pageUrl)) {
            return $utmParams;
        }

        // Decode the URL
        $decodedUrl = urldecode($pageUrl);

        // Parse the URL to get query string
        $urlParts = parse_url($decodedUrl);

        if (isset($urlParts['query'])) {
            parse_str($urlParts['query'], $queryParams);

            // Extract UTM and other tracking parameters
            foreach ($queryParams as $key => $value) {
                if (strpos($key, 'utm_') === 0 || in_array($key, ['gclid', 'msclkid', 'fbclid'])) {
                    $utmParams[$key] = $value;
                }
            }
        }

        return $utmParams;
    }

    /**
     * Get tracking value from session, GET, or page parameter
     */
    public static function getTrackingValue($key, $session = [], $get = [])
    {
        // First check direct GET parameters (highest priority for ad_id and adset_id)
        if (in_array($key, ['ad_id', 'AdId']) && isset($get['ad_id']) && !empty($get['ad_id'])) {
            return $get['ad_id'];
        }
        if (in_array($key, ['adset_id', 'AdGroupId']) && isset($get['adset_id']) && !empty($get['adset_id'])) {
            return $get['adset_id'];
        }

        // Check session
        if (isset($session[$key]) && !empty($session[$key])) {
            return $session[$key];
        }

        // Check direct GET parameters
        if (isset($get[$key]) && !empty($get[$key])) {
            return $get[$key];
        }

        // Parse UTM parameters from 'page' parameter if present
        if (isset($get['page'])) {
            $utmParams = self::parsePageParameter($get['page']);
            if (isset($utmParams[$key]) && !empty($utmParams[$key])) {
                return $utmParams[$key];
            }
        }

        // Check alternative session keys (for backward compatibility)
        $altKeys = [
            'AdGroupId' => 'utm_agid',
            'AdId' => 'ad_id'
        ];

        if (isset($altKeys[$key])) {
            if (isset($session[$altKeys[$key]]) && !empty($session[$altKeys[$key]])) {
                return $session[$altKeys[$key]];
            }
            if (isset($get[$altKeys[$key]]) && !empty($get[$altKeys[$key]])) {
                return $get[$altKeys[$key]];
            }
        }

        return '';
    }

    /**
     * Generate all tracking fields with priority handling
     */
    public static function generateTrackingFields($session = [], $get = [])
    {
        $output = [];
        $outputFields = [];
        $debugData = [];
        $emitted = [];

        // Parse UTM parameters from 'page' parameter first
        $pageUtmParams = [];
        if (isset($get['page'])) {
            $pageUtmParams = self::parsePageParameter($get['page']);
            $debugData['parsed_from_page'] = $pageUtmParams;
        }

        // Merge page UTM params into tracking data (lower priority than direct params)
        $mergedGet = array_merge($pageUtmParams, $get);

        // Taboola flag
        $isTaboola = self::isTaboola($session, $mergedGet);
        $outputFields['is_taboola'] = $isTaboola ? '1' : '0';
        $output[] = self::createHiddenField('is_taboola', $outputFields['is_taboola']);
        $emitted['is_taboola'] = true;
        $debugData['is_taboola'] = $outputFields['is_taboola'];

        // First pass - capture base mappings (UTM parameters have lowest priority)
        foreach (self::$trackingMapping as $sourceKey => $targetName) {
            // Skip if this is a field that has priority handling
            if (array_key_exists($targetName, self::$priorityMappings)) {
                continue;
            }

            $value = self::getTrackingValue($sourceKey, $session, $mergedGet);

            if (!empty($value) && !isset($outputFields[$targetName])) {
                $outputFields[$targetName] = $value;
                $output[] = self::createHiddenField($targetName, $value);
                $emitted[$targetName] = true;
                $debugData[$targetName] = $value;
            }
        }

        // Second pass - handle fields with priority mappings
        foreach (self::$priorityMappings as $fieldName => $sourceKeys) {
            $finalValue = '';
            $valueSource = '';

            foreach ($sourceKeys as $key) {
                if ($fieldName === 'adset_id') {
                    if (!empty($get['adset_id'])) {
                        $finalValue = $get['adset_id'];
                        $valueSource = 'direct_adset_id';
                        break;
                    } elseif ($key === 'AdGroupId' && !empty($get['AdGroupId'])) {
                        $finalValue = $get['AdGroupId'];
                        $valueSource = 'AdGroupId';
                        break;
                    } elseif ($key === 'utm_campaign' && !empty($pageUtmParams['utm_campaign'])) {
                        $finalValue = $pageUtmParams['utm_campaign'];
                        $valueSource = 'utm_campaign_from_page';
                        break;
                    } elseif ($key === 'utm_campaign' && !empty($mergedGet['utm_campaign'])) {
                        $finalValue = $mergedGet['utm_campaign'];
                        $valueSource = 'utm_campaign';
                        break;
                    }
                }

                if ($fieldName === 'ad_id') {
                    if (!empty($get['ad_id'])) {
                        $finalValue = $get['ad_id'];
                        $valueSource = 'direct_ad_id';
                        break;
                    } elseif ($key === 'AdId' && !empty($get['AdId'])) {
                        $finalValue = $get['AdId'];
                        $valueSource = 'AdId';
                        break;
                    } elseif ($key === 'utm_content' && !empty($pageUtmParams['utm_content'])) {
                        $finalValue = $pageUtmParams['utm_content'];
                        $valueSource = 'utm_content_from_page';
                        break;
                    } elseif ($key === 'utm_content' && !empty($mergedGet['utm_content'])) {
                        $finalValue = $mergedGet['utm_content'];
                        $valueSource = 'utm_content';
                        break;
                    }
                }
            }

            if (!empty($finalValue)) {
                $outputFields[$fieldName] = $finalValue;
                $output[] = self::createHiddenField($fieldName, $finalValue);
                $emitted[$fieldName] = true;
                $debugData[$fieldName] = $finalValue;
                $debugData[$fieldName . '_source'] = $valueSource;
            }
        }

        // Emit any captured fields not yet emitted
        foreach ($outputFields as $name => $val) {
            if ($val === '' || isset($emitted[$name])) {
                continue;
            }
            $output[] = self::createHiddenField($name, $val);
            $emitted[$name] = true;
        }

        // Handle other standard tracking fields
        // Search Engine (utm_source)
        $searchEngine = '';
        if (!empty($pageUtmParams['utm_source'])) {
            $searchEngine = $pageUtmParams['utm_source'];
        } elseif (!empty($mergedGet['utm_source'])) {
            $searchEngine = $mergedGet['utm_source'];
        } elseif (!empty($get['engine'])) {
            $searchEngine = $get['engine'];
        }
        if (!empty($searchEngine)) {
            $output[] = self::createHiddenField('Search_Engine', $searchEngine);
            $debugData['Search_Engine'] = $searchEngine;
        }

        // Keyword (utm_term)
        $keyword = '';
        if (!empty($pageUtmParams['utm_term'])) {
            $keyword = $pageUtmParams['utm_term'];
        } elseif (!empty($mergedGet['utm_term'])) {
            $keyword = $mergedGet['utm_term'];
        } elseif (!empty($get['Keyword'])) {
            $keyword = $get['Keyword'];
        }
        if (!empty($keyword)) {
            $output[] = self::createHiddenField('Keyword', $keyword);
            $debugData['Keyword'] = $keyword;
        }

        // Click IDs
        $clickIds = ['gclid', 'msclkid', 'fbclid', 'ttclid', 'gbraid', 'wbraid'];
        foreach ($clickIds as $clickId) {
            $value = '';
            if (!empty($pageUtmParams[$clickId])) {
                $value = $pageUtmParams[$clickId];
            } elseif (!empty($mergedGet[$clickId])) {
                $value = $mergedGet[$clickId];
            }
            if (!empty($value)) {
                $output[] = self::createHiddenField($clickId, $value);
                $debugData[$clickId] = $value;
            }
        }

        // Pub_ID
        if (!empty($get['Pub_ID'])) {
            $output[] = self::createHiddenField('Pub_ID', $get['Pub_ID']);
            $debugData['Pub_ID'] = $get['Pub_ID'];
        }

        // Notes field
        if (isset($get['notes'])) {
            $output[] = self::createHiddenField('notes', $get['notes']);
            $debugData['notes'] = $get['notes'];
        }

        // Page URL (cleaned)
        if (!empty($get['page'])) {
            $output[] = self::createHiddenField('Landing_Page', urldecode($get['page']));
            $debugData['Landing_Page'] = urldecode($get['page']);
        }

        // Add session tracking
        if (session_id()) {
            $debugData['session_hash'] = substr(md5(session_id()), 0, 8);
        }

        // Add timestamp
        $debugData['tracking_timestamp'] = date('Y-m-d H:i:s');

        // Add tracking source
        $trackingSource = self::getTrackingSource($session, $mergedGet);
        $output[] = self::createHiddenField('tracking_source', $trackingSource);
        $debugData['tracking_source'] = $trackingSource;

        // Add debug field
        $output[] = self::createHiddenField('tracking_debug', json_encode($debugData));

        return implode("\n", $output);
    }

    /**
     * Create a hidden input field
     */
    private static function createHiddenField($name, $value)
    {
        return sprintf(
            '<input type="hidden" name="%s" value="%s" />',
            htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Create JavaScript to update existing field or create new one
     */
    private static function createFieldUpdateScript($fieldName, $value)
    {
        $safeFieldName = htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8');
        $safeValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<script>
            (function() {
                var field = document.querySelector(\'input[name="%s"]\');
                if (field) {
                    field.value = "%s";
                } else {
                    var input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "%s";
                    input.value = "%s";
                    var form = document.getElementById("msform") || document.querySelector("form");
                    if (form) form.appendChild(input);
                }
            })();
            </script>',
            $safeFieldName,
            $safeValue,
            $safeFieldName,
            $safeValue
        );
    }

    /**
     * Get legacy tracking params (for backward compatibility)
     */
    public static function getTrackingParams()
    {
        return self::$legacyTrackingParams;
    }

    /**
     * Get all tracking parameters for validation
     */
    public static function getAllTrackingParams()
    {
        return array_merge(
            self::$trackingMapping,
            self::$additionalParams
        );
    }

    /**
     * Determine tracking source from parameters
     */
    public static function getTrackingSource($session = [], $get = [])
    {
        // Check for platform-specific IDs
        if (!empty(self::getTrackingValue('gclid', $session, $get))) {
            return 'google_ads';
        }
        if (!empty(self::getTrackingValue('msclkid', $session, $get))) {
            return 'microsoft_ads';
        }
        if (!empty(self::getTrackingValue('fbclid', $session, $get))) {
            return 'facebook_ads';
        }
        if (!empty(self::getTrackingValue('ttclid', $session, $get))) {
            return 'tiktok_ads';
        }

        // Check UTM source
        $utmSource = self::getTrackingValue('utm_source', $session, $get);
        if (!empty($utmSource)) {
            return strtolower($utmSource);
        }

        return 'direct';
    }

    /**
     * Validate tracking parameters
     */
    public static function validateTrackingParams($params = [])
    {
        $validated = [];

        foreach ($params as $key => $value) {
            // Sanitize the value
            $cleanValue = trim($value);

            // Remove any potentially harmful characters
            $cleanValue = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $cleanValue);

            // Limit length
            if (strlen($cleanValue) > 100) {
                $cleanValue = substr($cleanValue, 0, 100);
            }

            if (!empty($cleanValue)) {
                $validated[$key] = $cleanValue;
            }
        }

        return $validated;
    }
}
