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

defined('_JEXEC') or die('Restricted access');

/**
 * JFormFieldRounds
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldRounds extends JFormField
{

	protected $type = 'rounds';

	/**
	 * JFormFieldRounds::getInput()
	 * 
	 * @return
	 */
	protected function getInput() 
    {
		$required 	= $this->element['required'] == 'true' ? 'true' : 'false';
		$order 		= $this->element['order'] == 'DESC' ? 'DESC' : 'ASC';
		$db 		= sportsmanagementHelper::getDBConnection();
		$lang 		= JFactory::getLanguage();
        // welche tabelle soll genutzt werden
        $params = JComponentHelper::getParams( 'com_sportsmanagement' );
        $database_table	= $params->get( 'cfg_which_database_table' );
        
		$extension 	= "com_sportsmanagement";
		$source 	= JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $extension);
		$lang->load($extension, JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		
		$query = ' SELECT id as value '
		       . '      , CASE LENGTH(name) when 0 then CONCAT('.$db->Quote(JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME')). ', " ", id)	else name END as text '
		       . '      , id, name, round_date_first, round_date_last, roundcode '
		       . ' FROM #__'.$database_table.'_round '
		       . ' WHERE project_id= ' .$project_id
		       . ' ORDER BY roundcode '.$order;
		$db->setQuery( $query );
		$rounds = $db->loadObjectList();
		if($required == 'false') {
			$mitems = array(JHtml::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));
		}
		foreach ( $rounds as $round ) {
			$mitems[] = JHtml::_('select.option',  $round->id, '&nbsp;&nbsp;&nbsp;'.$round->name );
		}
		
		$output = JHtml::_('select.genericlist',  $mitems, $this->name.'[]', 'class="inputbox" style="width:90%;" multiple="multiple" size="10"', 'value', 'text', $this->value, $this->id );
		return $output;
	}
}
