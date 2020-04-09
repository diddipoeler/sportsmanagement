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
 * @package        GCalendar
 * @author         Digital Peak http://www.digital-peak.com
 * @copyright      Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license        http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

class jsmFullcalendar
{
	public static function convertFromPHPDate($format)
	{
		// Php date to fullcalendar date conversion
		$dateFormat = array(
			'd' => 'dd',
			'D' => 'ddd',
			'j' => 'd',
			'l' => 'dddd',
			'N' => '',
			'w' => '',
			'z' => '',
			'W' => '',
			'S' => 'S',
			'F' => 'MMMM',
			'm' => 'MM',
			'M' => 'MMM',
			'n' => 'M',
			't' => '',
			'L' => '',
			'o' => 'yyyy',
			'Y' => 'yyyy',
			'y' => 'yy',
			'a' => 'tt',
			'A' => 'TT',
			'B' => '',
			'g' => 'h',
			'G' => 'H',
			'h' => 'hh',
			'H' => 'HH',
			'i' => 'mm',
			's' => 'ss',
			'u' => '',
			'e' => '',
			'I' => '',
			'O' => '',
			'P' => '',
			'T' => '',
			'Z' => '',
			'c' => 'u',
			'r' => '',
			'U' => '');

		$newFormat = "";
		$isText    = false;
		$i         = 0;

		while ($i < strlen($format))
		{
			$chr = $format[$i];

			if ($chr == '"' || $chr == "'")
			{
				$isText = !$isText;
			}

			$replaced = false;

			if ($isText == false)
			{
				foreach ($dateFormat as $zl => $jql)
				{
					if (substr($format, $i, strlen($zl)) == $zl)
					{
						$chr      = $jql;
						$i        += strlen($zl);
						$replaced = true;
					}
				}
			}

			if ($replaced == false)
			{
				$i++;
			}

			$newFormat .= $chr;
		}

		return $newFormat;
	}
}
