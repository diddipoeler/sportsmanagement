<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
* @version   1.0.05
* @file      agegroup.php
* @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license   This file is part of SportsManagement.
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

/**
*
 * ensure this file is being included by a parent file
*/
if (! ( defined('_VALID_CB') || defined('_JEXEC') || defined('_VALID_MOS') ) ) {    die('Direct Access to this location is not allowed.');
}


/**
 * Basic tab extender. Any plugin that needs to display a tab in the user profile
 * needs to have such a class. Also, currently, even plugins that do not display tabs (e.g., auto-welcome plugin)
 * need to have such a class if they are to access plugin parameters (see $this->params statement).
 */
class getjsmusertipresultsTab extends cbTabHandler
{
    /**
     * Construnctor
     */
    function getjsmusertipresultsTab()
    {
        $this->cbTabHandler();
    }
  
    /**
    * Generates the HTML to display the user profile tab
     *
    * @param   object tab reflecting the tab database entry
    * @param   object mosUser reflecting the user being displayed
    * @param   int 1 for front-end, 2 for back-end
    * @returns mixed : either string HTML for tab content, or false if ErrorMSG generated
    */
    function getDisplayTab($tab,$user,$ui)
    {
        $return = null;
      
        $params = $this->params; // get parameters (plugin and related tab)
      
        $is_helloworld_plug_enabled = $params->get('hwPlugEnabled', "1");
        $helloworld_tab_message = $params->get('hwTabMessage', "JSM SportsManagement TipResults!");
      
        if ($is_helloworld_plug_enabled != "0") {
            if($tab->description != null) {
                $return .= "\t\t<div class=\"tab_Description\">"
                 . $tab->description    // html content is allowed in descriptions
                 . "</div>\n";
            }
            $return .= "\t\t<div>\n"
             . "<p>"
             . htmlspecialchars($helloworld_tab_message) // make all other output html-safe
             . "</p>"
             . "</div>\n";
        }
      
        return $return;
    } // end or getDisplayTab function
} // end of getjsmusertipresultsTab class
?>
