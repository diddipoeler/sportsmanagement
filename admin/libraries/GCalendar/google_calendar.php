<?php


defined('_JEXEC') or die();
use Joomla\CMS\Factory;
JLoader::import('joomla.filesystem.file');

//JLoader::import('components.com_sportsmanagement.libraries.google-php.Google.autoload', JPATH_ADMINISTRATOR);
//JLoader::import('components.com_sportsmanagement.libraries.google-php.Google.Logger.Psr', JPATH_ADMINISTRATOR);

//JLoader::import('components.com_sportsmanagement.libraries.google-php.Google.Service.Calendar', JPATH_ADMINISTRATOR);

class jsmGoogleCalendarHelper 
{
    

 function getClient ($clientId, $clientSecret)
	{
		//JLoader::import('dpcalendar.dpcalendar_google.libraries.google-php.Google.autoload', JPATH_PLUGINS);
        //JLoader::import('components.com_sportsmanagement.libraries.google-php.Google.autoload', JPATH_ADMINISTRATOR);

		$client = new Google_Client(
				array(
						'ioFileCache_directory' => Factory::getConfig()->get('tmp_path') . '/plg_dpcalendar_google/Google_Client'
				));
		$client->setApplicationName("sportsmanagement");
		$client->setClientId($clientId);
		$client->setClientSecret($clientSecret);
		$client->setScopes(array(
				'https://www.googleapis.com/auth/calendar'
		));
		$client->setAccessType('offline');

		$uri = Factory::getURI();
		if (filter_var($uri->getHost(), FILTER_VALIDATE_IP))
		{
			$uri->setHost('localhost');
		}
		$client->setRedirectUri(
				$uri->toString(array(
						'scheme',
						'host',
						'port',
						'path'
				)) . '?option=com_sportsmanagement&task=jsmgcalendarimport.import');

		return $client;
	}
        
    
}
    
?>