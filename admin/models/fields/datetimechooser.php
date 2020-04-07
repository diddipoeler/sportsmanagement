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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldDatetimechooser extends FormField
{
	public $type = 'Datetimechooser';

	protected function getInput()
	{
		// Initialize some field attributes.
		$format = $this->element['format'] ? (string) $this->element['format'] : '%Y-%m-%d';

		// Build the attributes array.
		$attributes = array();

		if ($this->element['size'])
		{
			$attributes['size'] = (int) $this->element['size'];
		}

		if ($this->element['maxlength'])
		{
			$attributes['maxlength'] = (int) $this->element['maxlength'];
		}

		if ($this->element['class'])
		{
			$attributes['class'] = (string) $this->element['class'];
		}

		if ((string) $this->element['readonly'] == 'true')
		{
			$attributes['readonly'] = 'readonly';
		}

		if ((string) $this->element['disabled'] == 'true')
		{
			$attributes['disabled'] = 'disabled';
		}

		if ($this->element['onchange'])
		{
			$attributes['onchange'] = (string) $this->element['onchange'];
		}

		// Handle the special case for "now".
		if (strtoupper($this->value) == 'NOW')
		{
			$date = Factory::getDate();
			$date->setTime($date->format('H', true), 0, 0);
			$this->value = $date->format('U');
		}

		if (strtoupper($this->value) == '+1 HOUR' || strtoupper($this->value) == '+2 MONTH')
		{
			$date = Factory::getDate();
			$date->setTime($date->format('H', true), 0, 0);
			$date->modify($this->value);
			$this->value = $date->format('U');
		}

		// Get some system objects.
		$config = Factory::getConfig();
		$user = Factory::getUser();

		// If a known filter is given use it.
		switch (strtoupper((string) $this->element['filter']))
		{
			case 'SERVER_UTC':
				// Convert a date to UTC based on the server timezone.
				if (intval($this->value))
				{
					// Get a date object based on the correct timezone.
					$date = Factory::getDate($this->value, 'UTC');
					$date->setTimezone(new DateTimeZone($config->get('offset')));

					// Transform the date string.
					$this->value = $date->format('Y-m-d H:i:s', true, false);
				}
			break;

			case 'USER_UTC':
				// Convert a date to UTC based on the user timezone.
				if (intval($this->value))
				{
					// Get a date object based on the correct timezone.
					$date = Factory::getDate($this->value, 'UTC');
					$date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

					// Transform the date string.
					$this->value = $date->format('Y-m-d H:i:s', true, false);

					if ($this->element['all_day'] == '1')
					{
						$this->value = $date->format('Y-m-d', true, false);
					}
				}
			break;
		}

		return HTMLHelper::_('calendar', $this->value, $this->name, $this->id, $format, $attributes);
	}
}
