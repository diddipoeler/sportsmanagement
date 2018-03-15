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
 * JFormFieldagegroups
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldagegroups extends JFormField
{

	protected $type = 'agegroups';

	/**
	 * JFormFieldagegroups::getInput()
	 * 
	 * @return
	 */
	function getInput()
	{
		$result = array();
		$db = sportsmanagementHelper::getDBConnection();
        $app = JFactory::getApplication();
		$lang = JFactory::getLanguage();
        $option = JFactory::getApplication()->input->getCmd('option');
        // welche tabelle soll genutzt werden
        $params = JComponentHelper::getParams( $option );
        $database_table	= $params->get( 'cfg_which_database_table' );
        $select_id = JFactory::getApplication()->input->getVar('id');
        
        if ($select_id)
		{
        $db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('sports_type_id');		
		$query->from('#__sportsmanagement_team AS t');
		$query->where('t.id = '.$select_id);
		$db->setQuery($query);
		$sports_type = $db->loadResult();
        
        /*
		$extension = "COM_SPORTSMANAGEMENT_agegroups";
		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		*/
        
        if ($sports_type)
		{
		$query='SELECT id, name FROM #__'.$database_table.'_agegroup where sportstype_id = '.$sports_type.' ORDER BY name ASC ';
		$db->setQuery($query);
        //$app->enqueueMessage(JText::_('JFormFieldSportsTypes<br><pre>'.print_r($query,true).'</pre>'),'');
		if (!$result = $db->loadObjectList())
		{
			//$app->enqueueMessage(JText::_('JFormFieldSportsTypes<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
      sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
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
                
	}
}
 