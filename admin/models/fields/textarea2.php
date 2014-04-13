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

JFormHelper::loadFieldClass('textarea');

class JFormFieldTextarea2 extends JFormFieldTextarea{
	protected $type = 'Textarea2';

	public function getInput(){
		$buffer = parent::getInput();
		if(isset($this->element->description)){
			$buffer .= '<label></label>';
			$buffer .= '<div style="float:left;">'.JText::_($this->element->description).'</div>';
		}
		return $buffer;
	}

	public function setup(& $element, $value, $group = null){
		if(isset($element->content) && empty($value)){
			$value = $element->content;
		}
		return parent::setup($element,$value,$group);
	}
}