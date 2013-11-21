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

/**
 * Session form field class
 */
class JFormFieldpersonagegroup extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	public $type = 'personagegroup';
	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$personquery = $db->getQuery(true);
			$personquery2 = $db->getQuery(true);
			
			$person_age_range = $db->getQuery(true);
			
    // Initialize variables.
		$options = array();

    $varname = (string) $this->element['varname'];
    $vartable = (string) $this->element['targettable'];
    // Get some field values from the form.
    $select_id	= (int) $this->form->getValue('id');
	$agegroup_id	= (int) $this->form->getValue('agegroup_id');
    
    //$mainframe->enqueueMessage(JText::_('personagegroup select_id<br><pre>'.print_r($select_id,true).'</pre>'   ),'');
    //$mainframe->enqueueMessage(JText::_('personagegroup agegroup_id<br><pre>'.print_r($agegroup_id,true).'</pre>'   ),'');
 
      $query->select('id AS value, concat(name, \' von: \',age_from,\' bis: \',age_to,\' Stichtag: \',deadline_day) AS text');
			$query->from('#__sportsmanagement_agegroup');
			$query->order('name');
			$db->setQuery($query);
			$options_select = $db->loadObjectList();
			
      //$mainframe->enqueueMessage(JText::_('personagegroup options<br><pre>'.print_r($options_select,true).'</pre>'   ),'');
			
      foreach($options_select as $row)
			{
     
      $options[] = JHtml::_('select.option', $row->value, $row->text);
     
      }
			
    
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		
		//return JHTML::_('select.genericlist', $options, 'month', 'class="inputbox"', 'value', 'text', $person_range);
		return $options;
	}
	
}



?>	