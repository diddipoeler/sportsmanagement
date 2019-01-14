<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jsmgcalendar
 * http://blog.mateuszzbylut.com/2018/01/19/fetching-data-google-calendar-without-user-authorization/
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

require_once('administrator'.DS.'components'.DS.'com_sportsmanagement'.DS.'libraries'.DS.'google-php'.DS.'google-api-php-client'.DS.'vendor'.DS.'autoload.php');

//JLoader::import('components.com_gcalendar.libraries.GCalendar.view', JPATH_ADMINISTRATOR);

//class GCalendarViewGCalendar extends GCalendarView
/**
 * sportsmanagementViewjsmgcalendar
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewjsmgcalendar extends sportsmanagementView  
{


/**
 * sportsmanagementViewjsmgcalendar::init()
 * 
 * @param mixed $tpl
 * @return void
 */
function init( $tpl = null )
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db	= sportsmanagementHelper::getDBConnection();
		$uri = Factory::getURI();
		$user = Factory::getUser();
		$model = $this->getModel();
        $starttime = microtime(); 
        
$client = new Google_Client(); 
$client->setApprovalPrompt('force');
$client->setClientId(ComponentHelper::getParams($option)->get('google_api_clientid',''));
$client->setClientSecret(ComponentHelper::getParams($option)->get('google_api_clientsecret',''));
$client->setAccessType("offline");
$client->addScope("https://www.googleapis.com/auth/calendar");
$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']; 
$url = $url . '?' . http_build_query ($_GET); 
$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' url <br><pre>'.print_r($url ,true).'</pre>'),'');
$client->setRedirectUri($url );
$uri = $client->createAuthUrl();
$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' uri<br><pre>'.print_r($uri,true).'</pre>'),'Notice');
if (! $app->input->get('code'))
{
$app->redirect($client->createAuthUrl());
$app->close();
}

/*	
$client = new Google_Client();
$client->setAccessType('online'); // default: offline
$client->setClientId(ComponentHelper::getParams($option)->get('google_api_clientid',''));
$client->setClientSecret(ComponentHelper::getParams($option)->get('google_api_clientsecret',''));
$client->addScope("https://www.googleapis.com/auth/calendar");
$client->setApprovalPrompt("force");
//$client->setApplicationName('Webclient6');
$client->setDeveloperKey(ComponentHelper::getParams($option)->get('google_api_developerkey',''));
$client->setRedirectUri($_SERVER['HTTP_REFERER']);

try {
$service = new Google_Service_Calendar($client);
$calendarList = $service->calendarList->listCalendarList();
} catch(Exception $e){
$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' getMessage<br><pre>'.print_r($e->getMessage() ,true).'</pre>'),'');			
$auth_url = $client->createAuthUrl();
header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
		}
*/	
$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' code <br><pre>'.print_r($_GET['code'] ,true).'</pre>'),'');
$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' scriptUri <br><pre>'.print_r($scriptUri ,true).'</pre>'),'');
$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' googleclient <br><pre>'.print_r($client ,true).'</pre>'),'');
$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' service <br><pre>'.print_r($service ,true).'</pre>'),'');

        // get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
        
        // Assign the Data
		$this->form = $form;
		$this->gcalendar = $item;
        
        // bei neuanlage user und passwort aus der konfiguration der komponente nehmen
        if ($this->gcalendar->id < 1) 
        {
            $this->form->setValue('username', null, ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('google_mail_account',''));
            $this->form->setValue('password', null, ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('google_mail_password',''));
        }
            
        //$this->addToolbar();
        
        //parent::display($tpl);
        
     }   
//	protected $gcalendar = null;
//	protected $form = null;
//
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
    protected function addToolbar() 
    {
        $app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$canDo = jsmGCalendarUtil::getActions($this->gcalendar->id);
		if ($this->gcalendar->id < 1) 
        {
            ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_INSERT_NEW_GOOGLE'),'gcalendar');
            
			if ($canDo->get('core.create')) 
            {
                $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_INSERT_ON_GOOGLE'),'Notice');
                
                $this->gcalendar->username = ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('google_mail_account','');
                $this->gcalendar->password = ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('google_mail_password','');
            
				ToolbarHelper::apply('jsmgcalendar.apply', 'JTOOLBAR_APPLY');
				ToolbarHelper::save('jsmgcalendar.save', 'JTOOLBAR_SAVE');
				ToolbarHelper::custom('jsmgcalendar.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			ToolbarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CANCEL');
		} 
        else 
        {
            ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_EDIT_NEW_GOOGLE'),'gcalendar');
            
			if ($canDo->get('core.edit')) 
            {
				ToolbarHelper::publish('jsmgcalendar.insertgooglecalendar', 'JLIB_HTML_CALENDAR');
                
                ToolbarHelper::apply('jsmgcalendar.apply', 'JTOOLBAR_APPLY');
				ToolbarHelper::save('jsmgcalendar.save', 'JTOOLBAR_SAVE');

				if ($canDo->get('core.create')) 
                {
					ToolbarHelper::custom('jsmgcalendar.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
            {
				ToolbarHelper::custom('jsmgcalendar.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			ToolbarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CLOSE');
		}
        
        ToolbarHelper::divider();
        sportsmanagementHelper::ToolbarButtonOnlineHelp();
		ToolbarHelper::preferences($option);

		parent::addToolbar();
	}

}
