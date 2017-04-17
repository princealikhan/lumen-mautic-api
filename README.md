## Mautic API in Laravel/Lumen.
Free and Open Source Marketing Automation API

## Requirements
* PHP 5.5.* or newer
* cURL support

## Mautic Setup
The API must be enabled in Mautic. Within Mautic, go to the Configuration page (located in the Settings menu) and under API Settings enable
Mautic's API.  You can also choose which OAuth2 protocol to use here.  After saving the configuration, go to the API Credentials page
(located in the Settings menu) and create a new client.  Enter the callback/redirect URI that the request will be sent from.  Click Apply
then copy the Client ID and Client Secret to the application that will be using the API.

## Registering Application
In order to register you application with mautic ping this url this is one time registration.
```url
http://your-app/mautic/application/register
```