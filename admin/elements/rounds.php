<?php
/**
* @copyright	Copyright (C) 2007-2013 JoomLeague.net. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined('_JEXEC') or die('Restricted access');

class JFormFieldRounds extends JFormField
{

	protected $type = 'rounds';

	protected function getInput() {
		$required 	= $this->element['required'] == 'true' ? 'true' : 'false';
		$order 		= $this->element['order'] == 'DESC' ? 'DESC' : 'ASC';
		$db 		= JFactory::getDBO();
		$lang 		= JFactory::getLanguage();
		$extension 	= "com_joomleague";
		$source 	= JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $extension);
		$lang->load($extension, JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		
		$query = ' SELECT id as value '
		       . '      , CASE LENGTH(name) when 0 then CONCAT('.$db->Quote(JText::_('COM_JOOMLEAGUE_GLOBAL_MATCHDAY_NAME')). ', " ", id)	else name END as text '
		       . '      , id, name, round_date_first, round_date_last, roundcode '
		       . ' FROM #__joomleague_round '
		       . ' WHERE project_id= ' .$project_id
		       . ' ORDER BY roundcode '.$order;
		$db->setQuery( $query );
		$rounds = $db->loadObjectList();
		if($required == 'false') {
			$mitems = array(JHTML::_('select.option', '', JText::_('COM_JOOMLEAGUE_GLOBAL_SELECT')));
		}
		foreach ( $rounds as $round ) {
			$mitems[] = JHTML::_('select.option',  $round->id, '&nbsp;&nbsp;&nbsp;'.$round->name );
		}
		
		$output = JHTML::_('select.genericlist',  $mitems, $this->name.'[]', 'class="inputbox" style="width:90%;" multiple="multiple" size="10"', 'value', 'text', $this->value, $this->id );
		return $output;
	}
}
