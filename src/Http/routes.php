<?php

/*
|--------------------------------------------------------------------------
| Mautic Application Register
|--------------------------------------------------------------------------
|
*/


$this->app->get('application/register','MauticController@initiateApplication');
$this->app->get('application/callback','MauticController@callback');
