<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage elements
 * @file       teamnameformat.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

/**
 * JFormFieldTeamNameFormat
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldTeamNameFormat extends JFormField
{
	protected $type = 'teamnameformat';

	/**
	 * JFormFieldTeamNameFormat::getInput()
	 *
	 * @return
	 */
	function getInput()
	{
		$lang      = Factory::getLanguage();
		$extension = "com_sportsmanagement";
		$source    = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		|| $lang->load($extension, $source, null, false, false)
		|| $lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		|| $lang->load($extension, $source, $lang->getDefault(), false, false);
		$mitems   = array();
		$mitems[] = HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TEAM_NAME_FORMAT_SHORT'));
		$mitems[] = HTMLHelper::_('select.option', 1, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TEAM_NAME_FORMAT_MEDIUM'));
		$mitems[] = HTMLHelper::_('select.option', 2, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TEAM_NAME_FORMAT_FULL'));

		$output = HTMLHelper::_(
			'select.genericlist', $mitems,
			$this->name,
			'class="inputbox" size="1"',
			'value', 'text', $this->value, $this->id
		);

		return $output;
	}
}
