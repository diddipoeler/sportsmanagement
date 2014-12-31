<?php 



defined( '_JEXEC' ) or die( 'Restricted access' );

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="joomleague">
	<?php 
	if ($this->config['show_sectionheader'] == 1)
	{ 
		echo $this->loadTemplate('sectionheader');
	}
		
	echo $this->loadTemplate('projectheading');

	echo $this->loadTemplate('stats');
		
	echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
