<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      acteventtype.php
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
 * JFormFieldacteventtype
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldacteventtype extends JFormFieldList
{

	protected $type = 'acteventtype';
    
    protected function getOptions()
	{
		// Initialize variables.
		$options = array();
    $vartable = (string) $this->element['targettable'];
		$select_id = JFactory::getApplication()->input->getVar('id');
        
    $db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('s.id AS value, s.name AS text');
			$query->from('#__sportsmanagement_eventtype as s');
            $query->join('INNER','#__sportsmanagement_'.$vartable.' AS t on t.sports_type_id = s.sports_type_id');
            $query->where('t.id = '.$select_id);
			$query->order('s.name');
			$db->setQuery($query);
			$options = $db->loadObjectList();
    
    foreach ( $options as $row )
            {
                $row->text = JText::_($row->text);
            }
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
    

}
