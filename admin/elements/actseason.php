<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
* @version   1.0.05
* @file      agegroup.php
* @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license   GNU General Public License version 2 or later; see LICENSE.txt
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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * JFormFieldactseason
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldactseason extends JFormField
{

    protected $type = 'actseason';

    /**
     * JFormFieldactseason::getInput()
     * 
     * @return
     */
    protected function getInput() 
    {
        $db = sportsmanagementHelper::getDBConnection();
        $lang = Factory::getLanguage();
        $option = Factory::getApplication()->input->getCmd('option');
        // welche tabelle soll genutzt werden
        $params = ComponentHelper::getParams('COM_SPORTSMANAGEMENT');
        $database_table    = $params->get('cfg_which_database_table');
         
        $extension = "com_sportsmanagement";
        $source = JPATH_ADMINISTRATOR . '/components/' . $extension;
        $lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
        ||    $lang->load($extension, $source, null, false, false)
        ||    $lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
        ||    $lang->load($extension, $source, $lang->getDefault(), false, false);
        
        $query = 'SELECT s.id, s.name as name 
					FROM #__'.$database_table.'_season AS s 
					ORDER BY s.name DESC';
        $db->setQuery($query);
        $projects = $db->loadObjectList();

        foreach ( $projects as $project ) {
            $mitems[] = HTMLHelper::_('select.option',  $project->id, '&nbsp;&nbsp;&nbsp;'.$project->name);
        }
        
        $output= HTMLHelper::_('select.genericlist',  $mitems, $this->name.'[]', 'class="inputbox" style="width:90%;" ', 'value', 'text', $this->value, $this->id);
        return $output;
    }
}
