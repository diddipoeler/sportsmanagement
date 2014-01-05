<?php
/**
* @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined('_JEXEC') or die('Restricted access');

class JFormFieldSportsTypes extends JFormField
{

	protected $type = 'sport_types';

	function getInput()
	{
		$result = array();
		$db = JFactory::getDBO();
        $mainframe = JFactory::getApplication();
		$lang = JFactory::getLanguage();
        $option = JRequest::getCmd('option');
        // welche tabelle soll genutzt werden
        $params = JComponentHelper::getParams( $option );
        $database_table	= $params->get( 'cfg_which_database_table' );
        
		$extension = "COM_SPORTSMANAGEMENT_sport_types";
		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		
		$query='SELECT id, name FROM #__'.$database_table.'_sports_type ORDER BY name ASC ';
		$db->setQuery($query);
		if (!$result=$db->loadObjectList())
		{
			$mainframe->enqueueMessage(JText::_('JFormFieldSportsTypes<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
      sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		foreach ($result as $sportstype)
		{
			$sportstype->name=JText::_($sportstype->name);
		}
		if($this->required == false) {
			$mitems = array(JHtml::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));
		}
		
		foreach ( $result as $item )
		{
			$mitems[] = JHtml::_('select.option',  $item->id, '&nbsp;'.$item->name. ' ('.$item->id.')' );
		}
		return JHtml::_('select.genericlist',  $mitems, $this->name, 
				'class="inputbox" size="1"', 'value', 'text', $this->value, $this->id);
	}
}
 