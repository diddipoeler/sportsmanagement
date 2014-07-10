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

/**
 * JFormFieldColor
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class JFormFieldColor extends JFormFieldText
{
	protected $type = 'Color';

	/**
	 * JFormFieldColor::getInput()
	 * 
	 * @return
	 */
	public function getInput()
	{
		$document = &JFactory::getDocument();
		$document->addScript(JUri::base(). 'components/com_gcalendar/libraries/jscolor/jscolor.js' );
		return parent::getInput();
	}

	/**
	 * JFormFieldColor::setup()
	 * 
	 * @param mixed $element
	 * @param mixed $value
	 * @param mixed $group
	 * @return
	 */
	public function setup(& $element, $value, $group = null)
	{
		$return= parent::setup($element, $value, $group);
		$this->element['class'] = $this->element['class'].' color';
		return $return;
	}
}
?>