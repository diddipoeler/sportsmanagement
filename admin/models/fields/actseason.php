<?php


defined('_JEXEC') or die('Restricted access');


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
    
    protected function getOptions()
	{
		// Initialize variables.
		$options = array();
    
    $db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('s.id AS value, s.name AS text');
			$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season as s');
			$query->order('s.name');
			$db->setQuery($query);
			$options = $db->loadObjectList();
    
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
    
/*
	protected function getInput() 
    {
		$db = JFactory::getDBO();
		$lang = JFactory::getLanguage();
        $option = JRequest::getCmd('option');
        // welche tabelle soll genutzt werden
        $params = JComponentHelper::getParams( 'COM_SPORTSMANAGEMENT' );
        $database_table	= $params->get( 'cfg_which_database_table' );
         
		$extension = "com_sportsmanagement";
		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		
		$query = 'SELECT s.id, s.name as name 
					FROM #__'.$database_table.'_season AS s 
					ORDER BY s.name DESC';
		$db->setQuery( $query );
		$projects = $db->loadObjectList();
//		if($this->required == false) {
//			$mitems = array(JHtml::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));
//		}
		foreach ( $projects as $project ) {
			$mitems[] = JHtml::_('select.option',  $project->id, '&nbsp;&nbsp;&nbsp;'.$project->name );
		}
		
		$output= JHtml::_('select.genericlist',  $mitems, $this->name.'[]', 'class="inputbox" style="width:90%;" ', 'value', 'text', $this->value, $this->id );
		return $output;
	}
*/    
}
