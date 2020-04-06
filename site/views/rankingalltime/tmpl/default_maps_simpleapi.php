<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_maps_simpleapi.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage rankingalltime
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
?>


<script type="text/javascript">
function initialize()
{
  //if (GBrowserIsCompatible())
  //{
    map = document.getElementById("gmap_canvas");
  
    //map.setCenter(new GLatLng(52.521653, 13.41091), 14);
    // Add resize control PResizeControl with default settings.
    //map.addControl(new PResizeControl());
    showmap();
    google.maps.event.trigger(map, "resize");
    //showmap();
    //map.setUIToDefault();
  //}
}
</script>
<div class="contentpaneopen">
<div class="contentheading">
    <?php echo Text::_('JL_GMAP_DIRECTIONS'); ?>
        </div>

              
                <?php
                // create div for the map canvas
                echo "\n<!-- DIV container for the map -->";
                echo "\n<div id=\"gmap_canvas\" style=\"width: ".$this->mapconfig['width']."px; height: ".$this->mapconfig['height']."px;\">\n</div>\n";              
                //$map->printGMapsJS();
                ?>
              
                <?PHP
                // showMap with auto zoom enabled
                //$map->showMap(true);
                //$this->map->showMap(false);
                ?>
      
            <script type="text/javascript">
        showmap();
      
        </script>
        
</div>
