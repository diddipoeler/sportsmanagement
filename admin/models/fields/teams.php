<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      teams.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormField;

/**
 * FormFieldTeams
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldTeams extends FormField
{

	protected $type = 'teams';

	/**
	 * FormFieldTeams::getInput()
	 * 
	 * @return
	 */
	protected function getInput() {
		$db = sportsmanagementHelper::getDBConnection();
		$lang = Factory::getLanguage();
        // welche tabelle soll genutzt werden
        $params = ComponentHelper::getParams( 'com_sportsmanagement' );
        //$database_table	= $params->get( 'cfg_which_database_table' );
        
		$extension = "com_sportsmanagement";
		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);

		$query = 'SELECT t.id, t.name FROM #__sportsmanagement_team t ORDER BY name';
		$db->setQuery( $query );
		$teams = $db->loadObjectList();
		$mitems = array(HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));

		foreach ( $teams as $team ) {
			$mitems[] = HTMLHelper::_('select.option',  $team->id, '&nbsp;'.$team->name. ' ('.$team->id.')' );
		}

		$output= HTMLHelper::_('select.genericlist',  $mitems, $this->name.'[]', 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $this->value, $this->id );
		return $output;
	}
}
