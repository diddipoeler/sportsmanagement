<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      clublist.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage fields
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');


/**
 * FormFieldClublist
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldClublist extends \JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	public $type = 'Clublist';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
    
    $db = Factory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('id AS value, name AS text');
			$query->from('#__sportsmanagement_club');
			$query->order('name');
			$db->setQuery($query);
			$options = $db->loadObjectList();
    
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}
