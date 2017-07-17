<?php namespace Princealikhan\Mautic\Factories;

use Mautic\Auth\ApiAuth;
use Mautic\Auth\OAuthClient;
use Princealikhan\Mautic\Models\MauticConsumer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;


class MauticFactory
{

    /**
     * Make a new Mautic url.
     *
     * @param string $endpoints
     * @return url
     */
    protected function getMauticUrl($endpoints=null)
    {
        if(!empty($endpoints))
            return config('mautic.connections.main.baseUrl').'/'.$endpoints;
        else
            return config('mautic.connections.main.baseUrl').'/';

    }

    /**
     * Check AccessToken Expiration Time
     * @param $expireTimestamp
     * @return bool
     */
    public function checkExpirationTime($expireTimestamp)
    {
        $now = time();
        if($now > $expireTimestamp)
            return true;
        else
            return false;

    }
    /**
     * Make a new Mautic client.
     *
     * @param array $config
     * @return \Mautic\Config
     */
    public function make(array $config)
    {

        $config = $this->getConfig($config);
        return $this->getClient($config);
    }

    /**
     * Get the configuration data.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getConfig(array $config)
    {
        $keys = ['clientKey', 'clientSecret'];

        foreach ($keys as $key) {
            if (!array_key_exists($key, $config)) {
                throw new \InvalidArgumentException('The Mautic client requires configuration.');
            }
        }

        return array_only($config, ['version','baseUrl', 'clientKey', 'clientSecret','callback']);
    }

    /**
     * Get the Mautic client.
     *
     * @param array $setting
     *
     * @return \Mautic\MauticConsumer
     */
    protected function getClient(array $setting)
    {
//        session_name("mauticOAuth");
        session_start();

        // Initiate the auth object
        $initAuth = new ApiAuth();
        $auth     = $initAuth->newAuth($setting);


        try {
            if ($auth->validateAccessToken()) {

                // Obtain the access token returned; call accessTokenUpdated() to catch if the token was updated via a
                // refresh token

                // $accessTokenData will have the following keys:
                // For OAuth1.0a: access_token, access_token_secret, expires
                // For OAuth2: access_token, expires, token_type, refresh_token

                if ($auth->accessTokenUpdated()) {
                    $accessTokenData = $auth->getAccessTokenData();

                    //store access token data however you want
                }
            }
        } catch (Exception $e) {
            \Log::info($e);
            // Do Error handling
        }

    }


    /**
     * Call Mautic Api
     *
     * @throws \ClientException
     *
     * @param $method
     * @param $endpoints
     * @param $body
     * @param $token
     *
     * @return mixed
     */
    public function callMautic($method, $endpoints, $body, $token)
    {

        $mauticURL = $this->getMauticUrl('api/'.$endpoints);

        $params = array();
        if(!empty($body)){
            $params = array();
            foreach ($body as $key => $item){
                $params['form_params'][$key] = $item;
            }
        }

        $headers = array('headers' => ['Authorization' => 'Bearer '. $token]);
        $client = new Client($headers);
        try {

            $response = $client->request($method,$mauticURL,$params);
            $responseBodyAsString = $response->getBody();

            return json_decode($responseBodyAsString,true);
        }
        catch (ClientException $e) {
             $exceptionResponse = $e->getResponse();
             return $statusCode = $exceptionResponse->getStatusCode();
        }
    }


    /**
     * Generate new token once old one expire
     * and store in consumer table.
     *
     * @throws \ClientException
     *
     * @param $refreshToken
     *
     * @return MauticConsumer
     */
    public function refreshToken($refreshToken)
    {
        $mauticURL = $this->getMauticUrl('oauth/v2/token');
        $config = config('mautic.connections.main');

        $client = new Client();

        try {
            $response = $client->request('POST',$mauticURL,array(
                'form_params' => [
                    'client_id'     => $config['clientKey'],
                    'client_secret' => $config['clientSecret'],
                    'redirect_uri'  => $config['callback'],
                    'refresh_token' => $refreshToken,
                    'grant_type'    => 'refresh_token'
                ]));
            $responseBodyAsString = $response->getBody();
            $responseBodyAsString = json_decode($responseBodyAsString,true);

            return MauticConsumer::create([
                'access_token'  => $responseBodyAsString['access_token'],
                'expires'       => time() + $responseBodyAsString['expires_in'],
                'token_type'    => $responseBodyAsString['token_type'],
                'refresh_token' => $responseBodyAsString['refresh_token']
            ]);
        }
        catch (ClientException $e) {
           return $exceptionResponse = $e->getResponse();
        }
    }
}
