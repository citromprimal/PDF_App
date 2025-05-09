<?php

namespace PDF_App\backend\api\classes;
require_once __DIR__ . '/../includes/config.php';

class GeoLocation
{
    private $api_key;

    public function __construct()
    {
        $this->api_key = GEO_API_KEY;
    }

    public function getLocationByIp($ip = null)
    {
        if (isset($_GET['test_ip']) && filter_var($_GET['test_ip'], FILTER_VALIDATE_IP)) {
            $ip = $_GET['test_ip'];
        }
        if ($ip === null) {
            $ip = $this->getClientIp();
        }

        // Skip for localhost/development
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return [
                'city' => 'localhost',
                'country' => 'Development',
                'loc' => '0,0'
            ];
        }

        // Use ipinfo.io API
        $url = "https://ipinfo.io/{$ip}/json";

        if (!empty($this->api_key)) {
            $url .= "?token={$this->api_key}";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        error_log("GeoLocation URL: {$url}");
// Disable SSL verification - for local development only!
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log('GeoLocation cURL error: ' . curl_error($ch));
        }
        curl_close($ch);

        if ($response) {
            return json_decode($response, true);
        }

        // Fallback if API fails
        return [
            'city' => 'Unknown',
            'country' => 'Unknown',
            'loc' => '0,0'
        ];
    }

    public function getClientIp()
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }

        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }
}

?>