<?php 
defined( '_JEXEC' ) or die( 'Restricted access' );

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('projectheading', 'backbutton', 'footer', 'googlemap');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
$this->kmlpath = JURI::root().'tmp'.DS.$this->playground->id.'-playground.kml';
?>
<div class="joomleague">
	<?php 
	echo $this->loadTemplate('projectheading');

	if (($this->config['show_sectionheader'])==1)
	{ 
		echo $this->loadTemplate('sectionheader');
	}
		
	if (($this->config['show_playground'])==1)
	{ 
		echo $this->loadTemplate('playground');
	}
		
	if (($this->config['show_extended'])==1)
	{
		echo $this->loadTemplate('extended');
	}
		
	if (($this->config['show_picture'])==1)
	{ 
		echo $this->loadTemplate('picture');
	}
		
	if (($this->config['show_maps'])==1 && (JPluginHelper::isEnabled('system', 'plugin_googlemap2') || JPluginHelper::isEnabled('system', 'plugin_googlemap3')))
	{ 
		echo $this->loadTemplate('googlemap');
	}
		
	if (($this->config['show_description'])==1)
	{ 
		echo $this->loadTemplate('description');
	}

	if (($this->config['show_teams'])==1)
	{ 
		echo $this->loadTemplate('teams');
	}

	if (($this->config['show_matches'])==1)
	{ 
		echo $this->loadTemplate('matches');
	}	

	echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
