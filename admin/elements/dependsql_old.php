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

defined( '_JEXEC' ) or die( 'Restricted access' ); // Check to ensure this file is included in Joomla!

//// welche joomla version
//if(version_compare(JVERSION,'3.0.0','ge')) 
//{
//JHtml::_('behavior.framework', true);
//}
//else
//{
//JHtml::_( 'behavior.mootools' );    
//}



/**
 * Renders a Dynamic SQL field
 *
 * in the xml field, the following fields must be defined:
 * - depends: list of fields name this field depends on, separated by comma (e.g: "p, tid")
 * - task: the task used to return the query, using defined depends field names as parameters for query (=> 'index.php?option=com_sportsmanagement&controller=ajax&task=<task>&p=1&tid=34')
 * @package Joomleague
 * @subpackageParameter
 * @since1.5
 */
class JFormFieldDependSQL extends JFormField
{
	/**
	 * field name
	 *
	 * @access protected
	 * @var string
	 */
	protected $type = 'dependsql';

	/**
	 * JFormFieldDependSQL::getInput()
	 * 
	 * @return
	 */
	function getInput()
	{
		$required = $this->element['required'] == "true" ? 'true' : 'false';
		$key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
		$val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
		$task = $this->element['task'];
		$depends = $this->element['depends'];

		$ctrl = $this->name;
		
		// Construct the various argument calls that are supported.
		$attribs	 = ' task="'.$task.'"';
		$attribs	.= ' required="'.$required.'"';
		if ($v = $this->element['size'])
		{
			$attribs .= ' size="'.$v.'"';
		}
		if ($depends)
		{
			$attribs	.= ' depends="'.$depends.'"';
		}
		$attribs	.= ' class="inputbox';
		// Optionally add "depend" to the class attribute
		if ($depends)
		{
			$attribs	.= ' depend"';
		}
		else
		{
			$attribs	.= '"';
		}
		$attribs	.= ' current="'.$this->value.'"';
		
		$lang = JFactory::getLanguage();
		$lang->load("com_sportsmanagement", JPATH_ADMINISTRATOR);
		if ($required) {
			$options = array();
		}
		else {
			$options = array(JHtml::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'), $key, JText::_($val)));
		}

		$query = $this->element['query'];
		if ($query!='')
		{
			$db = sportsmanagementHelper::getDBConnection();
			$db->setQuery($query);
			$options = array_merge($options, $db->loadObjectList());
		}
		
		if ($depends)
		{
			$doc = JFactory::getDocument();
			$doc->addScript(JURI::base() . 'components/com_sportsmanagement/assets/js/depend.js' );
      //$doc->addScript(JURI::base() . 'components/com_sportsmanagement/assets/js/depend_3.js' );
		}

		return JHtml::_('select.genericlist',  $options, $ctrl, $attribs, $key, $val, $this->value, $this->id);
	}
}
