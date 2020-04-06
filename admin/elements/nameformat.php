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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

/**
 * JFormFieldNameFormat
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldNameFormat extends JFormField
{
    protected $type = 'nameformat';

    /**
     * JFormFieldNameFormat::getInput()
     * 
     * @return
     */
    function getInput() 
    {
        $lang = Factory::getLanguage();
        $extension = "com_sportsmanagement";
        $source = JPATH_ADMINISTRATOR . '/components/' . $extension;
        $lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
        ||    $lang->load($extension, $source, null, false, false)
        ||    $lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
        ||    $lang->load($extension, $source, $lang->getDefault(), false, false);
        
        $mitems = array();
        $mitems[] = HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_NICK_LAST'));
        $mitems[] = HTMLHelper::_('select.option', 1, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_NICK_FIRST'));
        $mitems[] = HTMLHelper::_('select.option', 2, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRST_NICK'));
        $mitems[] = HTMLHelper::_('select.option', 3, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_LAST'));
        $mitems[] = HTMLHelper::_('select.option', 4, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRST'));
        $mitems[] = HTMLHelper::_('select.option', 5, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_NICK_FIRST_LAST'));
        $mitems[] = HTMLHelper::_('select.option', 6, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_NICK_LAST_FIRST'));
        $mitems[] = HTMLHelper::_('select.option', 7, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_LAST_NICK'));
        $mitems[] = HTMLHelper::_('select.option', 8, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_LAST2'));
        $mitems[] = HTMLHelper::_('select.option', 9, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRST2'));
        $mitems[] = HTMLHelper::_('select.option', 10, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST'));
        $mitems[] = HTMLHelper::_('select.option', 11, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_NICK_LAST2'));
        $mitems[] = HTMLHelper::_('select.option', 12, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_NICK'));
        $mitems[] = HTMLHelper::_('select.option', 13, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_LAST3'));
        $mitems[] = HTMLHelper::_('select.option', 14, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST2_FIRST'));
        $mitems[] = HTMLHelper::_('select.option', 15, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_NEWLINE_FIRST'));
        $mitems[] = HTMLHelper::_('select.option', 16, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_NEWLINE_LAST'));
        
        $output= HTMLHelper::_(
            'select.genericlist',  $mitems,
            $this->name,
            'class="inputbox" size="1"', 
            'value', 'text', $this->value, $this->id
        );
        return $output;
    }
}
