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

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');
jimport('joomla.html.html');
jimport('joomla.form.formfield');
/**
 * Session form field class
 */
class JFormFieldseasonteamperson extends JFormField
{
	/**
	 * field type
	 * @var string
	 */
	public $type = 'checkboxes';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $select_id = JRequest::getVar('id');
        $this->value = explode(",", $this->value);
        $targettable = $this->element['targettable'];
        $targetid = $this->element['targetid'];
        
        
        //$mainframe->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput targettable<br><pre>'.print_r($targettable,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput targetid<br><pre>'.print_r($targetid,true).'</pre>'),'');
    
    
        // Initialize variables.
		//$options = array();
    
    // teilnehmende saisons selektieren
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
			// saisons selektieren
			$query->select('stp.season_id,stp.team_id, t.name as teamname, s.name as seasonname, c.logo_big as clublogo');
			$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_'.$targettable.' as stp');
            $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = stp.team_id');
            $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS c ON c.id = t.club_id');
            $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s ON s.id = stp.season_id');
            
			$query->where($targetid.'='.$select_id);
            $query->order('s.name');
			$db->setQuery($query);
            $options = $db->loadObjectList();
			
    
    //$mainframe->enqueueMessage(JText::_('JFormFieldseasonteamperson getInput query<br><pre>'.print_r($query,true).'</pre>'),'');
    //$mainframe->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput value<br><pre>'.print_r($this->value,true).'</pre>'),'');
    $mainframe->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput options<br><pre>'.print_r($options,true).'</pre>'),'');
   


// Initialize variables.
            $html = array();
            $attribs['width'] = '25px';
            $html[] = '<ul>';
            foreach ($options as $i => $option)
            {
            $html[] = '<li>';
            $html[] = $option->seasonname.' - '.JHtml::_('image', $option->clublogo, '', $attribs).' - '.$option->teamname;
            $html[] = '</li>';    
            }   
            $html[] = '</ul>';
             
    
            return implode($html);    
    
    }
}
