<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * JFormFieldSportsTypes
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldSportsTypes extends JFormField
{

	protected $type = 'sport_types';

	/**
	 * JFormFieldSportsTypes::getInput()
	 * 
	 * @return
	 */
	function getInput()
	{
		$result = array();
		$db = sportsmanagementHelper::getDBConnection();
        $app = Factory::getApplication();
		$lang = Factory::getLanguage();
        $option = Factory::getApplication()->input->getCmd('option');
        // welche tabelle soll genutzt werden
        $params = JComponentHelper::getParams( $option );
        //$database_table	= $params->get( 'cfg_which_database_table' );
        
		$extension = "COM_SPORTSMANAGEMENT";
		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		
		$query='SELECT id, name FROM #__sportsmanagement_sports_type ORDER BY name ASC ';
		$db->setQuery($query);
		if (!$result=$db->loadObjectList())
		{
			$app->enqueueMessage(Text::_('JFormFieldSportsTypes<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
      sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
			return false;
		}
		foreach ($result as $sportstype)
		{
			$sportstype->name=Text::_($sportstype->name);
		}
		if($this->required == false) {
			$mitems = array(JHtml::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));
		}
		
		foreach ( $result as $item )
		{
			$mitems[] = JHtml::_('select.option',  $item->id, '&nbsp;'.$item->name. ' ('.$item->id.')' );
		}
		return JHtml::_('select.genericlist',  $mitems, $this->name, 
				'class="inputbox" size="1"', 'value', 'text', $this->value, $this->id);
	}
}
 