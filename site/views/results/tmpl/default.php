<?php defined('_JEXEC') or die('Restricted access');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('projectheading', 'backbutton', 'footer');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="joomleague">
	<?php
	echo $this->loadTemplate('projectheading');

	if ($this->config['show_sectionheader'])
	{
		echo $this->loadTemplate('sectionheader');
	}
	
	if ($this->config['show_matchday_pagenav']==2 || $this->config['show_matchday_pagenav']==3)
	{
		echo $this->loadTemplate('pagnav');
	}

	echo $this->loadTemplate('results');

	if ($this->config['show_matchday_pagenav']==1 || $this->config['show_matchday_pagenav']==3)
	{
		echo $this->loadTemplate('pagnav');
	}
    
    if (($this->overallconfig['show_project_rss_feed']) == 1   )
	{
		//if ( !empty($this->rssfeedoutput) )
//       {
//       echo $this->loadTemplate('rssfeed-table'); 
//       }
		if ( $this->rssfeeditems )
        {
        echo $this->loadTemplate('rssfeed');    
        }
	}
    
	/*
	 if ($this->config['show_results_ranking'])
	 {
	$this->_addPath( 'template', JPATH_COMPONENT . DS . 'views' . DS . 'ranking' . DS . 'tmpl' );
	echo $this->loadTemplate('ranking');
	echo $this->loadTemplate('colorlegend');
	echo $this->loadTemplate('manipulations');
	}
	*/

	echo "<div>";
	echo $this->loadTemplate('backbutton');
	echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
