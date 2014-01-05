<?php 
defined( '_JEXEC' ) or die( 'Restricted access' ); 

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
    //showmap();
    google.maps.event.trigger(map, "resize");
    //showmap();
    //map.setUIToDefault();
  //}
}

window.addEvent('load', function() {
    initialize();
    $('ul.jwts_tabbernav a').filterByAttribute('title','=','Google Maps').each(function(el) {
        el.addEvent('click',function() {
            initialize();
            //map.checkResize();
        });
    });
});

</script>
<div class="no-column">
<div class="contentpaneopen">
<div class="contentheading">
			<?php echo JText::_('JL_GMAP_DIRECTIONS'); ?>
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
</div>