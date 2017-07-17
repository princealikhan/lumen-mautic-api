<?php namespace Princealikhan\Mautic\Http\Controllers;

use App\Http\Controllers\Controller;
use Princealikhan\Mautic\Models\MauticConsumer;
use Princealikhan\Mautic\Facades\Mautic;
use GuzzleHttp\Client;

/**
     * Created by PhpStorm.
     * User: prince
     * Date: 26/11/16
     * Time: 4:12 PM
     */
    class MauticController extends Controller
    {

        /**
         * Setup Applicaion.
         */
        public function initiateApplication()
        {
            $consumer = MauticConsumer::count();

            if($consumer == 0){
                Mautic::connection('main');
            }else{
                echo '<h1>Mautic App Already Register</h1>';
            }

        }

        public function callback()
        {
            $mauticURL = config('mautic.connections.main.baseUrl').'/oauth/v2/token';
            $config = config('mautic.connections.main');

            if(isset($_GET['code'])) {

                $client = new Client();

                try {
                    $response = $client->request('POST', $mauticURL, array(
                        'form_params' => [
                            'client_id' => $config['clientKey'],
                            'client_secret' => $config['clientSecret'],
                            'grant_type' => 'authorization_code',
                            'redirect_uri' => $config['callback'],
                            'code' => $_GET['code']
                        ]));
                    $responseBodyAsString = $response->getBody();
                    $responseBodyAsString = json_decode($responseBodyAsString, true);

                    return MauticConsumer::create([
                        'access_token' => $responseBodyAsString['access_token'],
                        'expires' => time() + $responseBodyAsString['expires_in'],
                        'token_type' => $responseBodyAsString['token_type'],
                        'refresh_token' => $responseBodyAsString['refresh_token']
                    ]);

                } catch (ClientException $e) {
                    return $exceptionResponse = $e->getResponse();
                }

            }else{
                return 'Response Code is missing';
            }
        }

    }