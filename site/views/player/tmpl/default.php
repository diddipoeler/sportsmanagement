<?php 
defined('_JEXEC') or die('Restricted access');


if ( $this->show_debug_info )
{
echo 'player view games<pre>',print_r($this->games,true),'</pre><br>'; 
echo 'player view teams<pre>',print_r($this->teams,true),'</pre><br>';  
echo 'player view person_position<pre>',print_r($this->person_position,true),'</pre><br>'; 
echo 'player view person_parent_positions<pre>',print_r($this->person_parent_positions,true),'</pre><br>';   
}


// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
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

    if ( $this->use_joomlaworks == 0 )
    {
    // diddipoeler
    // joomlaworks nicht anwenden und die playerinfo in´s array    
	if ($this->config['show_plinfo'] == 1)
	{
		$output[intval($this->config['show_order_plinfo'])] = 'info';
	}
	}
    
    else
    {
    if($this->config['show_players_layout'] == "player_standard")
    {
    if ($this->config['show_plinfo'] == 1)
	{
		$output[intval($this->config['show_order_plinfo'])] = 'info';
	}    
    }
    }
    
    
    if ($this->config['show_playfield'] == 1)
	{
		$output[intval($this->config['show_order_playfield'])] = 'playfield';
	}
    
    if ($this->config['show_extra_fields'] == 1)
	{
		$output[intval($this->config['show_order_extra_fields'])] = 'extrafields';
	}
    
    if ($this->config['show_extended'] == 1 && $this->hasExtendedData )
	{
		$output[intval($this->config['show_order_extended'])] = 'extended';
	}
	if ($this->config['show_plstatus'] == 1 && $this->hasStatus )
	{
		$output[intval($this->config['show_order_plstatus'])] = 'status';
	}
	if ($this->config['show_description'] == 1 && !empty($this->hasDescription) )
	{
		$output[intval($this->config['show_order_description'])] = 'description';
	}
	if ($this->config['show_gameshistory'] == 1 && count($this->games) )
	{
		$output[intval($this->config['show_order_gameshistory'])] = 'gameshistory';
	}
	if ($this->config['show_plstats'] == 1 )
	{
		$output[intval($this->config['show_order_plstats'])] = 'playerstats';
	}
	if ($this->config['show_plcareer'] == 1 && count($this->historyPlayer) > 0 )
	{
		$output[intval($this->config['show_order_plcareer'])] = 'playercareer';
	}
	if ($this->config['show_stcareer'] == 1 && count($this->historyPlayerStaff) > 0 )
	{
		$output[intval($this->config['show_order_stcareer'])] = 'playerstaffcareer';
	}
    
    if ( $this->use_joomlaworks == 0 )
    {
	// diddipoeler
    // joomlaworks nicht anwenden
    if($this->config['show_players_layout'] == "player_tabbed") {
		//$document = JFactory::getDocument();
		//$css = 'components/com_joomleague/assets/css/tabs.css';
		//$document->addStyleSheet($css);
		$idxTab = 1;
		echo JHTML::_('tabs.start','playertabs', array('useCookie'=>1));
		foreach ($output as $templ) {
			echo JHTML::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_PLAYER_TAB_LABEL_'.strtoupper($templ)), 'panel'.($idxTab++));
			echo $this->loadTemplate($templ);
		}
		echo JHTML::_('tabs.end');
	}
    else if($this->config['show_players_layout'] == "player_slider") {
		//$document = JFactory::getDocument();
		//$css = 'components/com_joomleague/assets/css/tabs.css';
		//$document->addStyleSheet($css);
		$idxTab = 1;
        echo JHtml::_('sliders.start','playerslider', array('useCookie'=>1, 'show'=>-1, 'display'=>-1, 'startOffset'=>-1));
		
		foreach ($output as $templ) {
			echo JHTML::_('sliders.panel', JText::_('COM_SPORTSMANAGEMENT_PLAYER_TAB_LABEL_'.strtoupper($templ)), 'panel'.($idxTab++));
			echo $this->loadTemplate($templ);
		}
		echo JHTML::_('sliders.end');
	}  
    
    else {
		foreach ($output as $templ)
		{
			echo $this->loadTemplate($templ);
		}
	}
    
    }
    else
    {
    // diddipoeler
    // anzeige als tabs oder slider von joomlaworks
    // und die spielerinfo immer als erstes
    $startoutput = '';
    $params = '';
    $params .= $this->loadTemplate('INFO'); 
    if( $this->config['show_players_layout'] == "player_tabbed" ) 
    {
    $startoutput = '{tab=';
    $endoutput = '{/tabs}';
        
    foreach ($output as $templ) 
    {
    $params .= $startoutput.JText::_('COM_SPORTSMANAGEMENT_PLAYER_TAB_LABEL_'.strtoupper($templ)).'}';
    $params .= $this->loadTemplate($templ);    
    }    
    $params .= $endoutput;   
    echo JHTML::_('content.prepare', $params);   
    }    
    else if($this->config['show_players_layout'] == "player_slider" ) 
    {
    $startoutput = '{slider=';
    $endoutput = '{/slider}';
    foreach ($output as $templ) 
    {
    $params .= $startoutput.JText::_('COM_SPORTSMANAGEMENT_PLAYER_TAB_LABEL_'.strtoupper($templ)).'}';
    $params .= $this->loadTemplate($templ);    
    $params .= $endoutput;
    }    
    echo JHTML::_('content.prepare', $params);    
    }    
    else 
    {
//  if ($this->config['show_plinfo'] == 1)
//	{
//		$output[intval($this->config['show_order_plinfo'])] = 'info';
//	}     
	foreach ($output as $templ)
	{
	echo $this->loadTemplate($templ);
	}
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