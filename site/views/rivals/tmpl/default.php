<?php 

defined('_JEXEC') or die;

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view

$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<div class="">
	<?php
	if ( $this->config['show_sectionheader'] )
	{
		echo $this->loadTemplate('sectionheader');
	}
	
	echo $this->loadTemplate( 'projectheading' );

	echo $this->loadTemplate('rivals');
	echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
