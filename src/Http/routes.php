<?php

/*
|--------------------------------------------------------------------------
| Mautic Application Register
|--------------------------------------------------------------------------
|
*/


$this->app->get('mautic/application/register','MauticController@initiateApplication');
$this->app->post('mautic/application/callback','MauticController@callback');
