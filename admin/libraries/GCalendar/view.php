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
 * @package		GCalendar
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;

class GCalendarView extends JViewLegacy {

	protected $icon = 'calendar';
	protected $title = 'COM_GCALENDAR_MANAGER_GCALENDAR';
	protected $extension = 'com_gcalendar';

	public function display($tpl = null) {
		if (jsmGCalendarUtil::isJoomlaVersion('2.5')) {
			$this->setLayout($this->getLayout().'_25');
		} else if (jsmGCalendarUtil::isJoomlaVersion('3')) {
			$this->setLayout($this->getLayout().'_3');
		}

		$this->init();
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
		$canDo	= jsmGCalendarUtil::getActions();

		if (empty($this->title)) {
			$this->title = 'COM_GCALENDAR_MANAGER_'.strtoupper($this->getName());
		}
		if (empty($this->icon)) {
			$this->icon = strtolower($this->getName());
		}
		ToolbarHelper::title(JText::_($this->title), $this->icon);
		$document = Factory::getDocument();
		$document->addStyleDeclaration('.icon-48-'.$this->icon.' {background-image: url(../media/com_gcalendar/images/admin/48-'.$this->icon.'.png);background-repeat: no-repeat;}');

		if ($canDo->get('core.admin')) {
			ToolbarHelper::preferences($this->extension, 550);
			ToolbarHelper::divider();
		}
	}

	protected function init() {
	}
}