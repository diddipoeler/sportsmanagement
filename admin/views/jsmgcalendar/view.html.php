<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage jsmgcalendar
 * http://blog.mateuszzbylut.com/2018/01/19/fetching-data-google-calendar-without-user-authorization/
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

//require_once('administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_sportsmanagement'.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'google-php'.DIRECTORY_SEPARATOR.'google-api-php-client'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');

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
public function init ()
	{

        // bei neuanlage user und passwort aus der konfiguration der komponente nehmen
        if ($this->item->id < 1) 
        {
            $this->form->setValue('username', null, ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('google_mail_account',''));
            $this->form->setValue('password', null, ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('google_mail_password',''));
        }
       
     }   

	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
    protected function addToolbar() 
    {

		parent::addToolbar();
	}

}
