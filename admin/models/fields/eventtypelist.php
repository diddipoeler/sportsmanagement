<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      eventtypelist.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fields
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');


/**
 * JFormFieldeventtypelist
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldeventtypelist extends JFormFieldList
{
    
	/**
	 * field type
	 * @var string
	 */
	public $type = 'eventtypelist';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        // Initialize variables.
		$options = array();
    //$vartable = (string) $this->element['targettable'];
//		$select_id = JFactory::getApplication()->input->getVar('id');
    $db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('pos.id AS value, pos.name AS text');
			$query->from('#__sportsmanagement_eventtype as pos');
			$query->where('pos.published = 1');
			$query->order('pos.ordering,pos.name');
			$db->setQuery($query);
            		
        	try { 
			$options = $db->loadObjectList();
            }
catch (Exception $e) {
//    // catch any database errors.
//    $db->transactionRollback();
//    JErrorPage::render($e);

}
            
            foreach ( $options as $row )
            {
                $row->text = JText::_($row->text);
            }
    
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}
