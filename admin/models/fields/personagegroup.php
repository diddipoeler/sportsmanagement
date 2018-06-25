<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');


/**
 * JFormFieldpersonagegroup
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
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
		$app = JFactory::getApplication();
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
    
    //$app->enqueueMessage(JText::_('personagegroup select_id<br><pre>'.print_r($select_id,true).'</pre>'   ),'');
    //$app->enqueueMessage(JText::_('personagegroup agegroup_id<br><pre>'.print_r($agegroup_id,true).'</pre>'   ),'');
 
      $query->select('a.id AS value, concat(a.name, \' von: \',a.age_from,\' bis: \',a.age_to,\' Stichtag: \',a.deadline_day) AS text');
			$query->from('#__sportsmanagement_agegroup as a');
            $query->join('INNER','#__sportsmanagement_'.$vartable.' AS t on t.sports_type_id = a.sportstype_id');
            $query->where('t.id = '.$select_id);
			$query->order('name');
			$db->setQuery($query);
			$options_select = $db->loadObjectList();
			
      //$app->enqueueMessage(JText::_('personagegroup options<br><pre>'.print_r($options_select,true).'</pre>'   ),'');
			
      foreach($options_select as $row)
			{
     
      $options[] = JHtml::_('select.option', $row->value, $row->text);
     
      }
			
    
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		
		//return JHtml::_('select.genericlist', $options, 'month', 'class="inputbox"', 'value', 'text', $person_range);
		return $options;
	}
	
}



?>	