<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version   1.0.05
 * @file      agegroup.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SportsManagement is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SportsManagement is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Diese Datei ist Teil von SportsManagement.
 *
 * SportsManagement ist Freie Software: Sie können es unter den Bedingungen
 * der GNU General Public License, wie von der Free Software Foundation,
 * Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
 * veröffentlichten Version, weiterverbreiten und/oder modifizieren.
 *
 * SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
 * OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
 * Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
 * Siehe die GNU General Public License für weitere Details.
 *
 * Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
 * Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
 *
 * Note : All ini files need to be saved as UTF-8 without BOM
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * JFormFieldMultiList
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldMultiList extends JFormField
{
	/**
	 * Element name
	 *
	 * @access protected
	 * @var    string
	 */
	protected $type = 'MultiList';

	/**
	 * JFormFieldMultiList::getInput()
	 *
	 * @return
	 */
	function getInput()
	{
		// Base name of the HTML control.
		$ctrl = $control_name . '[' . $name . ']';

		// Construct an array of the HTML OPTION statements.
		$options = array();

		foreach ($node->children() as $option)
		{
			$val       = $option->attributes('value');
			$text      = $option->data();
			$options[] = HTMLHelper::_('select.option', $val, Text::_($text));
		}

		// Construct the various argument calls that are supported.
		$attribs = ' ';

		if ($v = $node->attributes('size'))
		{
			$attribs .= 'size="' . $v . '"';
		}

		if ($v = $node->attributes('class'))
		{
			$attribs .= 'class="' . $v . '"';
		}
		else
		{
			$attribs .= 'class="inputbox"';
		}

		if ($m = $node->attributes('multiple'))
		{
			$attribs .= ' multiple="multiple"';
			$ctrl    .= '[]';
		}

		// Render the HTML SELECT list.
		// Return HTMLHelper::_('select.genericlist', $options, $ctrl, $attribs, 'value', 'text', $value, $control_name.$name );
		return HTMLHelper::_('select.genericlist', $options, $ctrl, $attribs, 'value', 'text', array_map('trim', explode(',', $value)), $control_name . $name);
	}
}
