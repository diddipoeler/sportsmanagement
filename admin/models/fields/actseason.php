<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      actseason.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');

/**
 * FormFieldactseason
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldactseason extends \JFormFieldList
{

	protected $type = 'actseason';
    
    /**
     * FormFieldactseason::getOptions()
     * 
     * @return
     */
    protected function getOptions()
	{
		// Initialize variables.
		$options = array();
    
    $db = Factory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('s.id AS value, s.name AS text');
			$query->from('#__sportsmanagement_season as s');
			$query->order('s.name');
			$db->setQuery($query);
			$options = $db->loadObjectList();
    
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
    

}
