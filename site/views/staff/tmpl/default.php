<?php 

defined('_JEXEC') or die('Restricted access');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="joomleague">
	<?php
	echo $this->loadTemplate('projectheading');

	if ($this->config['show_sectionheader']==1)
	{
		echo $this->loadTemplate('sectionheader');
	}

	// General part of person view START
	if ($this->config['show_info']==1)
	{
		echo $this->loadTemplate('info');
	}

	if (($this->config['show_extended'])==1)
	{
		echo $this->loadTemplate('extended');
	}

	if ($this->config['show_status']==1)
	{
		echo $this->loadTemplate('status');
	}

	if ($this->config['show_description']==1)
	{
		echo $this->loadTemplate('description');
	}
	// General part of person view END

	if ($this->config['show_careerstats']==1)
	{
		echo $this->loadTemplate('careerstats');
	}

	if ($this->config['show_career']==1)
	{
		echo $this->loadTemplate('career');
	}

	echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
