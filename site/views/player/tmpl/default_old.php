<?php defined('_JEXEC') or die('Restricted access');


if ( $this->show_debug_info )
{
echo 'player view teams<pre>',print_r($this->teams,true),'</pre><br>';    
}


// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('projectheading', 'backbutton', 'footer');
JoomleagueHelper::addTemplatePaths($templatesToLoad, $this);
if (isset($this->person))
{
	?>
<div class="joomleague">
	<?php
	echo $this->loadTemplate('projectheading');

	if ($this->config['show_sectionheader']==1)
	{
		echo $this->loadTemplate('sectionheader');
	}

	// Person view START
	$output = array();

	if ($this->config['show_plinfo']==1)
	{
		$output[intval($this->config['show_order_plinfo'])] = 'info';
	}
	
    if ($this->config['show_playfield']==1)
	{
		$output[intval($this->config['show_order_playfield'])] = 'playfield';
	}
    
    
    if ($this->config['show_extended']==1)
	{
		$output[intval($this->config['show_order_extended'])] = 'extended';
	}
	if ($this->config['show_plstatus']==1)
	{
		$output[intval($this->config['show_order_plstatus'])] = 'status';
	}
	if ($this->config['show_description']==1)
	{
		$output[intval($this->config['show_order_description'])] = 'description';
	}
	if ($this->config['show_gameshistory']==1)
	{
		$output[intval($this->config['show_order_gameshistory'])] = 'gameshistory';
	}
	if ($this->config['show_plstats']==1)
	{
		$output[intval($this->config['show_order_plstats'])] = 'playerstats';
	}
	if ($this->config['show_plcareer']==1)
	{
		$output[intval($this->config['show_order_plcareer'])] = 'playercareer';
	}
	if ($this->config['show_stcareer']==1)
	{
		$output[intval($this->config['show_order_stcareer'])] = 'playerstaffcareer';
	}
	if($this->config['show_players_layout'] == "player_tabbed") {
		//$document = JFactory::getDocument();
		//$css = 'components/com_joomleague/assets/css/tabs.css';
		//$document->addStyleSheet($css);
		$idxTab = 1;
		echo JHTML::_('tabs.start','tabs', array('useCookie'=>1));
		foreach ($output as $templ) {
			echo JHTML::_('tabs.panel', JText::_('COM_JOOMLEAGUE_PLAYER_TAB_LABEL_'.strtoupper($templ)), 'panel'.($idxTab++));
			echo $this->loadTemplate($templ);
		}
		echo JHTML::_('tabs.end');
	} else {
		foreach ($output as $templ)
		{
			echo $this->loadTemplate($templ);
		}
	}
	// Person view END

	echo "<div>";
	echo $this->loadTemplate('backbutton');
	echo $this->loadTemplate('footer');
	echo "</div>";

	//fixxme: had a domready Calendar.setup error on my local site
	echo "<script>";
	echo "Calendar={};";
	echo "</script>";
	?>
</div>
<?php
}
else
{
	?>
<p>No person selected</p>
<?php
}
?>