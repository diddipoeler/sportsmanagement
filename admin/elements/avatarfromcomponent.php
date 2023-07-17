<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage elements
 * @file       avatarfromcomponent.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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
		$db                               = sportsmanagementHelper::getDBConnection();
		$sel_component                    = array();
		$sel_component['com_kunena']      = 'COM_SPORTSMANAGEMENT_GLOBAL_AVATAR_FROM_KUNENA';
		$sel_component['com_cbe']         = 'COM_SPORTSMANAGEMENT_GLOBAL_AVATAR_FROM_JOOMLA_CBE';
		$sel_component['com_comprofiler'] = 'COM_SPORTSMANAGEMENT_GLOBAL_AVATAR_FROM_CB_ENHANCED';

		$mitems   = array();
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
			'select.genericlist', $mitems,
			$this->name,
			'class="inputbox" size="1"',
			'value', 'text', $this->value, $this->id
		);

		return $output;
	}
}
