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

class JFormFieldAvatarFromComponent extends JFormField
{
	protected $type = 'avatarfromcomponent';

	function getInput() {
		$db = JFactory::getDBO();
        $sel_component = array();
        $sel_component['com_kunena'] = 'COM_SPORTSMANAGEMENT_GLOBAL_AVATAR_FROM_KUNENA';
        $sel_component['com_cbe'] = 'COM_SPORTSMANAGEMENT_GLOBAL_AVATAR_FROM_JOOMLA_CBE';
        $sel_component['com_comprofiler'] = 'COM_SPORTSMANAGEMENT_GLOBAL_AVATAR_FROM_CB_ENHANCED';
        
        
        
		$mitems = array();
		$mitems[] = JHTML::_('select.option', 'com_users', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_AVATAR_FROM_JOOMLA'));
		
        foreach( $sel_component as $key => $value )
        {
            $query = "SELECT extension_id FROM #__extensions where type LIKE 'component' ";
            $query .= " and element like '".$key."'";
            $db->setQuery($query);
            if ( $result = $db->loadResult() )
            {
		$mitems[] = JHTML::_('select.option', $key , JText::_($value));
	       }
        
        }

		$output= JHTML::_('select.genericlist',  $mitems,
				$this->name,
				'class="inputbox" size="1"',
				'value', 'text', $this->value, $this->id);
		return $output;
	}
}