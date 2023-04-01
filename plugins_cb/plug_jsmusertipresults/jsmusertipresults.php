<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage plug_jsmusertipresults
 * @file       jsmusertipresults.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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
