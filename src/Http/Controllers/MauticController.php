<?php namespace Princealikhan\Mautic\Http\Controllers;

use App\Http\Controllers\Controller;
use Princealikhan\Mautic\Models\MauticConsumer;
use Princealikhan\Mautic\Facades\Mautic;

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

        public function callback(Request $request)
        {
            $mauticURL = config('mautic.connections.main.baseUrl').'oauth/v2/token';
            $config = config('mautic.connections.main');

                $client = new Client();

                try {
                    $response = $client->request('POST',$mauticURL,array(
                        'form_params' => [
                            'client_id'     => $config['clientKey'],
                            'client_secret' => $config['clientSecret'],
                            'redirect_uri'  => $config['callback'],
                            'grant_type'    => 'authorization_code',
                            'code'          =>  $request->input('code')
                        ]));
                    $responseBodyAsString = $response->getBody();
                    $responseBodyAsString = json_decode($responseBodyAsString,true);
                    return  MauticConsumer::create($accessTokenData);
                }
                catch (ClientException $e) {
                    var_dump($e);
                   return $exceptionResponse = $e->getResponse();
                }
        }

    }