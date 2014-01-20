<?php 
defined( '_JEXEC' ) or die( 'Restricted access' ); 

if ( $this->show_debug_info )
{
echo 'club config<pre>',print_r($this->config,true),'</pre><br>';    
echo 'club address_string<pre>',print_r($this->address_string,true),'</pre><br>';
echo 'club teams<pre>',print_r($this->teams,true),'</pre><br>';
echo 'club extended<pre>',print_r($this->extended,true),'</pre><br>';
echo 'club <pre>',print_r($this->club,true),'</pre><br>';
echo 'club clubassoc<pre>',print_r($this->clubassoc,true),'</pre><br>';
}

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('projectheading', 'backbutton', 'footer', 'googlemap');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
$this->kmlpath = JURI::root().'tmp'.DS.$this->club->id.'-club.kml';
?>
<div class="joomleague">
	<?php 
	echo $this->loadTemplate('projectheading');

	if (($this->config['show_sectionheader'])==1)
	{ 
		echo $this->loadTemplate('sectionheader');
	}

	// Needs some changing &Mindh4nt3r
	echo $this->loadTemplate('clubinfo');
	
  // ################################################################
  // diddipoeler
  // aufbau der templates
  $output = array();
  
  if (($this->config['show_extra_fields'])==1)
	{
	$output['COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS'] = 'extra_fields';
	}
    
    if (($this->config['show_extended'])==1)
	{
	$output['COM_SPORTSMANAGEMENT_TABS_EXTENDED'] = 'extended';
	}
    
    if (($this->config['show_maps'])==1 && (JPluginHelper::isEnabled('system', 'plugin_googlemap2') || JPluginHelper::isEnabled('system', 'plugin_googlemap3')) )
	{ 
        $output['COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS'] = 'googlemap';
	}
    if (($this->config['show_teams_of_club'])==1)
	{ 
        $output['COM_SPORTSMANAGEMENT_CLUBINFO_TEAMS'] = 'teams';
	}
    if (($this->config['show_club_rssfeed']) == 1  )
	{
		if ( $this->rssfeeditems )
        {
        $output['COM_SPORTSMANAGEMENT_CLUBINFO_RSSFEED'] = 'rssfeed';  
        }
	}
    // ################################################################
  
    if ( $this->use_joomlaworks == 0 || $this->config['show_clubinfo_tabs'] == 'no_tabs' )
    {	
	echo "<div class='jl_defaultview_spacing'>";
	echo "&nbsp;";
	echo "</div>";


	//fix me
    if (($this->config['show_extra_fields'])==1)
	{
		echo $this->loadTemplate('extra_fields');
		echo "<div class='jl_defaultview_spacing'>";
		echo "&nbsp;";
		echo "</div>";	
	}
    
	if (($this->config['show_extended'])==1)
	{
		echo $this->loadTemplate('extended');
		echo "<div class='jl_defaultview_spacing'>";
		echo "&nbsp;";
		echo "</div>";	
	}

	if (($this->config['show_maps'])==1)
	{ 
		echo $this->loadTemplate('googlemap');
		
		echo "<div class='jl_defaultview_spacing'>";
		echo "&nbsp;";
		echo "</div>";
	}

		
	if (($this->config['show_teams_of_club'])==1)
	{ 
		echo $this->loadTemplate('teams');
			
		echo "<div class='jl_defaultview_spacing'>";
		echo "&nbsp;";
		echo "</div>";
	}
    
    if (($this->config['show_club_rssfeed']) == 1  )
	{

		if ( $this->rssfeeditems )
        {
        echo $this->loadTemplate('rssfeed');    
        }
        
	}
    
    }
    else
    {
    // diddipoeler
    // anzeige als tabs oder slider von joomlaworks
    $startoutput = '';
    $params = '';
    $params .= '<div class="">';
    if($this->config['show_clubinfo_tabs'] == "show_tabs") 
    {
    $startoutput = '{tab=';
    $endoutput = '{/tabs}';
        
    foreach ( $output as $key => $templ ) 
    {
    $params .= $startoutput.JText::_($key).'}';
    $params .= $this->loadTemplate($templ);    
    }    
    $params .= $endoutput;   
       
    }    
    else if($this->config['show_clubinfo_tabs'] == "show_slider") 
    {
    $startoutput = '{slider=';
    $endoutput = '{/slider}';
    foreach ( $output as $key => $templ ) 
    {
    $params .= $startoutput.JText::_($key).'}';
    $params .= $this->loadTemplate($templ);    
    $params .= $endoutput;
    }    
        
    }
    
    $params .= '</div>';    

    echo JHTML::_('content.prepare', $params); 
    
    
    }


	echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
