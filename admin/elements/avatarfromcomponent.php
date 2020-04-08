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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * JFormFieldAvatarFromComponent
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldAvatarFromComponent extends JFormField
{
	protected $type = 'avatarfromcomponent';

	/**
	 * JFormFieldAvatarFromComponent::getInput()
	 *
	 * @return
	 */
	function getInput()
	{
		$db = sportsmanagementHelper::getDBConnection();
		$sel_component = array();
		$sel_component['com_kunena'] = 'COM_SPORTSMANAGEMENT_GLOBAL_AVATAR_FROM_KUNENA';
		$sel_component['com_cbe'] = 'COM_SPORTSMANAGEMENT_GLOBAL_AVATAR_FROM_JOOMLA_CBE';
		$sel_component['com_comprofiler'] = 'COM_SPORTSMANAGEMENT_GLOBAL_AVATAR_FROM_CB_ENHANCED';

			  $mitems = array();
		$mitems[] = HTMLHelper::_('select.option', 'com_users', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_AVATAR_FROM_JOOMLA'));

		foreach ($sel_component as $key => $value)
		{
			$query = "SELECT extension_id FROM #__extensions where type LIKE 'component' ";
			$query .= " and element like '" . $key . "'";
			$db->setQuery($query);

			if ($result = $db->loadResult())
			{
				$mitems[] = HTMLHelper::_('select.option', $key, Text::_($value));
			}
		}

			$output = HTMLHelper::_(
				'select.genericlist',  $mitems,
				$this->name,
				'class="inputbox" size="1"',
				'value', 'text', $this->value, $this->id
			);

		return $output;
	}
}
