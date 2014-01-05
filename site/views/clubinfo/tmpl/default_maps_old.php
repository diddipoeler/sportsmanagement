<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<?php
if ( $this->gmap )
{  
?>
<div style="width: 100%; float: left">
	<div class="contentpaneopen">
		<div class="contentheading">
			<?php echo JText::_('COM_JOOMLEAGUE_GMAP_DIRECTIONS'); ?>
		</div>
	</div>

	<?php
		// create the container for map
		echo $this->gmap->MapHolder();

		// write a link to center location in map if is configured
		if ( $this->mapconfig['center_link'] == 1 ){
			echo $this->gmap->GetSideClick();
		}
		if ( $this->config['show_route'] == 1 ){
			$this->gmap->RouteForm();
		}
	?>


	<?php
	    // insert script tag for google maps api
	    echo $this->gmap->GmapsKey();

	    // write javascript for map creation
	    echo $this->gmap->InitJs();
	    if ( $this->config['show_route'] == 1 )
	    {
	        $this->gmap->InitRouteJs();
	    }

	    // write a javascript to properly unload map when leaving site
	    echo $this->gmap->UnloadMap();
	?>
</div>
<?php	
}
?>
