<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   GCalendar
 * @author    Digital Peak http://www.digital-peak.com
 * @copyright Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;


// JLoader::import('components.com_gcalendar.libraries.GCalendar.view', JPATH_ADMINISTRATOR);

/**
 * sportsmanagementViewjsmgcalendarImport
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewjsmgcalendarImport extends sportsmanagementView
{

	/**
	 * sportsmanagementViewjsmgcalendarImport::init()
	 *
	 * @param   mixed  $tpl
	 *
	 * @return void
	 */
	function init($tpl = null)
	{

		if (strpos($this->getLayout(), 'login') === false)
		{
			$this->onlineItems = $this->get('OnlineData');
			$this->dbItems     = $this->get('DBData');
		}

		$this->setLayout('login');

	}

	//	protected $onlineItems = null;
	//	protected $dbItems = null;

	/**
	 * sportsmanagementViewjsmgcalendarImport::addToolbar()
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		$jinput = Factory::getApplication()->input;
		$option = $jinput->getCmd('option');

		if (strpos($this->getLayout(), 'login') === false)
		{
			ToolbarHelper::custom('jsmgcalendarimport.save', 'new.png', 'new.png', 'COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_IMPORT_BUTTON_ADD', false);
			ToolbarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			ToolbarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CANCEL');
		}

		parent::addToolbar();
	}

}

