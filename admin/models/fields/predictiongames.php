<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       predictiongames.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Form\FormHelper;

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');

/**
if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

// Prüft vor Benutzung ob die gewünschte Klasse definiert ist
if (!class_exists('sportsmanagementHelper'))
{
	// Add the classes for handling
	$classpath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
	JLoader::register('sportsmanagementHelper', $classpath);
	BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}
*/


/**
 * FormFieldPredictiongame
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldPredictiongames extends \JFormFieldList
{
	protected $type = 'predictiongames';

	protected function getOptions()
	{
		$options = array();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('s.id AS value, s.name AS text');
		$query->from('#__sportsmanagement_prediction_game as s');
		$query->order('s.name');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		/** Merge any additional options in the XML definition. */
		$options = array_merge(parent::getOptions(), $options);
// Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>'.print_r($options,true).'</pre>' , 'Error');
		return $options;

		
		
	}
	
	
}
