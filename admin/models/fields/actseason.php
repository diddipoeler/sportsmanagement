<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      actseason.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');

/**
 * JFormFieldactseason
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldactseason extends JFormFieldList
{

	protected $type = 'actseason';
    
    /**
     * JFormFieldactseason::getOptions()
     * 
     * @return
     */
    protected function getOptions()
	{
		// Initialize variables.
		$options = array();
    
    $db = JFactory::getDbo();
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
