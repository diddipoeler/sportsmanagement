<?php 

defined('_JEXEC') or die('Restricted access');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="joomleague">
	<?php
	if (!empty($this->project->id))
	{
		echo $this->loadTemplate('projectheading');

		if ($this->config['show_sectionheader']==1)
		{
			echo $this->loadTemplate('sectionheader');
		}

		if ($this->config['show_plan_layout']=='plan_default')
		{
			echo $this->loadTemplate('plan');
		} else if($this->config['show_plan_layout']=='plan_sorted_by_date') {
			echo $this->loadTemplate('plan_sorted_by_date');
		}
		
		echo "<div>";
	}
	else
	{
		echo "<div>";
			echo '<p>'.JText::_('At least you need to submit a project-id to get a teamplan of SportsManagement!').'</p>';
	}

		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo '</div>';
	?>
</div>
