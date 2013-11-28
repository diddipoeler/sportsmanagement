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
class JFormFieldseasoncheckbox extends JFormField
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
    
    $db = JFactory::getDbo();
			$query = $db->getQuery(true);
			// saisons selektieren
			$query->select('id AS value, name AS text');
			$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season');
			$query->order('name');
			$db->setQuery($query);
			$options = $db->loadObjectList();
    
    // teilnehmende saisons selektieren
    $query = $db->getQuery(true);
			// saisons selektieren
			$query->select('season_id');
			$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_'.$targettable);
			$query->where($targetid.'='.$select_id);
			$db->setQuery($query);
			$this->value = $db->loadColumn();
    
    //$mainframe->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput query<br><pre>'.print_r($query,true).'</pre>'),'');
    //$mainframe->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput value<br><pre>'.print_r($this->value,true).'</pre>'),'');
    //$mainframe->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput options<br><pre>'.print_r($options,true).'</pre>'),'');
   


// Initialize variables.
            $html = array();
    
            // Initialize some field attributes.
            $class = $this->element['class'] ? ' class="checkboxes ' . (string) $this->element['class'] . '"' : ' class="checkboxes"';
    
            // Start the checkbox field output.
            $html[] = '<fieldset id="' . $this->id . '"' . $class . '>';
    
            // Get the field options.
            //$options = $options;
    
            // Build the checkbox field output.
            $html[] = '<ul>';
            foreach ($options as $i => $option)
            {
    
                // Initialize some option attributes.
                $checked = (in_array((string) $option->value, (array) $this->value) ? ' checked="checked"' : '');
                $class = !empty($option->class) ? ' class="' . $option->class . '"' : '';
                $disabled = !empty($option->disable) ? ' disabled="disabled"' : '';
    
                // Initialize some JavaScript option attributes.
                $onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
    
                $html[] = '<li>';
                $html[] = '<input type="checkbox" id="' . $this->id . $i . '" name="' . $this->name . '[]"' . ' value="'
                    . htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick . $disabled . '/>';
    
                $html[] = '<label for="' . $this->id . $i . '"' . $class . '>' . JText::_($option->text) . '</label>';
                $html[] = '</li>';
            }
            $html[] = '</ul>';
    
            // End the checkbox field output.
            $html[] = '</fieldset>';
    
            return implode($html);    
    
    }
}
