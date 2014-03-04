<?php 


defined('_JEXEC') or die('Restricted access');

//echo 'project_id<pre>'.print_r($this->model->predictionProject->project_id, true).'</pre><br>';

//$this->_addPath( 'template', JPATH_COMPONENT . DS .'views' . DS . 'predictionheading' . DS . 'tmpl' );
//$this->_addPath( 'template', JPATH_COMPONENT . DS . 'views' . DS . 'backbutton' . DS . 'tmpl' );
//$this->_addPath( 'template', JPATH_COMPONENT . DS . 'views' . DS . 'footer' . DS . 'tmpl' );

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews','predictionheading');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?><div class='joomleague'><?php

	echo $this->loadTemplate('predictionheading');
	echo $this->loadTemplate('sectionheader');

	echo $this->loadTemplate('results');

	if ($this->config['show_matchday_pagenav']){echo $this->loadTemplate('matchday_nav');}

	if ($this->config['show_help']){echo $this->loadTemplate('show_help');}

	echo '<div>';
		//backbutton
		echo $this->loadTemplate('backbutton');
		// footer
		echo $this->loadTemplate('footer');
	echo '</div>';
	?></div>