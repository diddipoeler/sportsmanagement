<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       leaguelevel.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * FormFieldLeagueLevel
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class JFormFieldLeagueLevel extends ListField
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'leaguelevel';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
		$options = array();

		for ($level = 1; $level < 21; $level++)
		{
			$options[] = HTMLHelper::_('select.option', $level, Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_LEVEL') . ' - ' . $level);
		}

		$cupLevel = 1;
		for ($level = 21; $level < 41; $level++)
		{
			$options[] = HTMLHelper::_('select.option', $level, Text::_('COM_SPORTSMANAGEMENT_ADMIN_POKAL_LEVEL') . ' - ' . $cupLevel);
			$cupLevel++;
		}

		$tournamentLevel = 1;
		for ($level = 41; $level < 51; $level++)
		{
			$options[] = HTMLHelper::_('select.option', $level, Text::_('COM_SPORTSMANAGEMENT_ADMIN_TOURNEMENT_LEVEL') . ' - ' . $tournamentLevel);
			$tournamentLevel++;
		}

		return array_merge(parent::getOptions(), $options);
	}
}
