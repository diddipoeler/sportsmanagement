<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       playgrounds.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * FormFieldPlaygrounds
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldPlaygrounds extends FormField
{
	protected $type = 'playgrounds';

	/**
	 * FormFieldPlaygrounds::getInput()
	 *
	 * @return
	 */
	function getInput()
	{
		$db   = sportsmanagementHelper::getDBConnection();
		$lang = Factory::getLanguage();

		// Welche tabelle soll genutzt werden
		$params         = ComponentHelper::getParams('com_sportsmanagement');
		$database_table = $params->get('cfg_which_database_table');

		$extension = "com_sportsmanagement";
		$source    = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		|| $lang->load($extension, $source, null, false, false)
		|| $lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		|| $lang->load($extension, $source, $lang->getDefault(), false, false);

		$query = 'SELECT pl.id, pl.name FROM #__' . $database_table . '_playground pl ORDER BY name';
		$db->setQuery($query);
		$playgrounds = $db->loadObjectList();
		$mitems      = array(HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));

		foreach ($playgrounds as $playground)
		{
			$mitems[] = HTMLHelper::_('select.option', $playground->id, '&nbsp;' . $playground->name . ' (' . $playground->id . ')');
		}

		$output = HTMLHelper::_('select.genericlist', $mitems, $this->name, 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $this->value, $this->id);

		return $output;
	}
}

