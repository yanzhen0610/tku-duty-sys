<?php

namespace App;

class ReCAPTCHA
{
    public static function v2Available()
    {
        return static::v2SiteKey() && static::v2SecretKey();
    }

    public static function v2SiteKey()
    {
        return config('recaptcha.v2_site_key');
    }

    public static function v2SecretKey()
    {
        return config('recaptcha.v2_secret_key');
    }

    public static function v3Available()
    {
        return static::v3SiteKey() && static::v3SecretKey();
    }

    public static function v3SiteKey()
    {
        return config('recaptcha.v3_site_key');
    }

    public static function v3SecretKey()
    {
        return config('recaptcha.v3_secret_key');
    }

    public static function verify($secretKey, $token, $client_ip)
    {
        $client = new \GuzzleHttp\Client();

        try
        {
            $response = $client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => [
                    'secret' => $secretKey,
                    'response' => $token,
                    'remoteip' => $client_ip,
                ]
            ]);

            if ($response->getStatusCode() !== 200)
                return null;

            $body = $response->getBody();

            $content = $body->getContents();

            return json_decode($content);
        }
        catch (Exception $e)
        {
        }
    }

    public static function v3Verify($token, $client_ip = null)
    {
        return static::verify(static::v3SecretKey(), $token, $client_ip);
    }

    public static function v2Verify($token, $client_ip = null)
    {
        return static::verify(static::v2SecretKey(), $token, $client_ip);
    }
}
