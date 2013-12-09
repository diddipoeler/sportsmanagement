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

class JFormFieldPredictiongame extends JFormField
{

	protected $type = 'predictiongame';
	
	function getInput() {
		$db = &JFactory::getDBO();
		$lang = JFactory::getLanguage();
        // welche tabelle soll genutzt werden
        $params =& JComponentHelper::getParams( 'com_sportsmanagement' );
        $database_table	= $params->get( 'cfg_which_database_table' );
        
		$extension = "com_sportsmanagement";
// 		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
// 		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
// 		||	$lang->load($extension, $source, null, false, false)
// 		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
// 		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		
		$query = 'SELECT pg.id, pg.name FROM #__'.$database_table.'_prediction_game pg WHERE pg.published=1 ORDER BY pg.name';
		$db->setQuery( $query );
		$clubs = $db->loadObjectList();
		$mitems = array(JHtml::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));

		foreach ( $clubs as $club ) {
			$mitems[] = JHtml::_('select.option',  $club->id, '&nbsp;'.$club->name. ' ('.$club->id.')' );
		}
		
		$output= JHtml::_('select.genericlist',  $mitems, $this->name, 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $this->value, $this->id );
		return $output;
	}
}
 