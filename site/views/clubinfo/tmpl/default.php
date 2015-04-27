<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 

//echo 'club config<pre>',print_r($this->config,true),'</pre><br>'; 

//if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
//{
//echo 'club config<pre>',print_r($this->config,true),'</pre><br>';    
//echo 'club address_string<pre>',print_r($this->address_string,true),'</pre><br>';
//echo 'club teams<pre>',print_r($this->teams,true),'</pre><br>';
//echo 'club extended<pre>',print_r($this->extended,true),'</pre><br>';
//echo 'club <pre>',print_r($this->club,true),'</pre><br>';
//echo 'club clubassoc<pre>',print_r($this->clubassoc,true),'</pre><br>';
//}

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
$this->kmlpath = JURI::root().'tmp'.DS.$this->club->id.'-club.kml';
$this->kmlfile = $this->club->id.'-club.kml';

// container
?>
<div class="">

	<?php 
    if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo $this->loadTemplate('debug');
}
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
	$output['COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS'] = 'extrafields';
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
  
    //if ( $this->use_joomlaworks == 0 || $this->config['show_clubinfo_tabs'] == 'no_tabs' )
    if ( !JPluginHelper::isEnabled('content', 'jw_ts') || $this->config['show_clubinfo_tabs'] == 'no_tabs' )
    {	
	echo "<div class='jl_defaultview_spacing'>";
	echo "&nbsp;";
	echo "</div>";


	//fix me
    if (($this->config['show_extra_fields'])==1)
	{
		echo $this->loadTemplate('extrafields');
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
        
    if(version_compare(JVERSION,'3.0.0','ge'))
    //if(version_compare(JVERSION,'2.5.0','ge'))  
        {
            
    //echo 'output<pre>',print_r($output,true),'</pre><br>';  
            
        $count = 0;
    foreach ($output as $templ)
    {
    
    if ( !$count )
    {
    // Define slides options
        $slidesOptions = array(
            "active" => "slide".$count."_id" // It is the ID of the active tab.
        );    
    // Define tabs options for version of Joomla! 3.0
        $tabsOptions = array(
            "active" => "tab".$count."_id" // It is the ID of the active tab.
        );      
    }    
    $count++;	   
    }    
    
    if( $this->config['show_clubinfo_tabs'] == "show_tabs" ) 
    {
    $count = 0;    
    ?>
        <!-- This is a list with tabs names. -->
    	<ul class="nav nav-tabs" id="ID-Tabs-Group">
        <?PHP
        foreach ($output as $key => $templ)
        {
        $active = '';    
        if ( $count == 0 )
        {
            $active = 'active';
        }    
        ?>
        <li class="<?php echo $active; ?>">
        <a data-toggle="tab" href="#tab<?php echo $count; ?>_id"><?php echo JText::_($key); ?>
        </a>
       	</li>
        <?PHP
        $count++;
        }
        ?>
        </ul>
            
    <?PHP    
    echo JHtml::_('bootstrap.startPane', 'ID-Tabs-Group', $tabsOptions);
    $count = 0;  
    foreach ($output as $key => $templ)
    {
    
    //echo 'templ<pre>',print_r($templ,true),'</pre><br>';
        
    echo JHtml::_('bootstrap.addPanel', 'ID-Tabs-Group', 'tab'.$count.'_id');
    echo $this->loadTemplate($templ);
    echo JHtml::_('bootstrap.endPanel'); 
    $count++;
    }
    echo JHtml::_('bootstrap.endPane', 'ID-Tabs-Group');    
    }
    else if($this->config['show_clubinfo_tabs'] == "show_slider" ) 
    {
    // This renders the beginning of the slides code.
    echo JHtml::_('bootstrap.startAccordion', 'slide-group-id', $slidesOptions);  
    $count = 0;  
    foreach ($output as $key => $templ)
    {
        // Open the first slide
        echo JHtml::_('bootstrap.addSlide', 'slide-group-id', JText::_($key), 'slide'.$count.'_id');
        echo $this->loadTemplate($templ);
        // This is the closing tag of the first slide
        echo JHtml::_('bootstrap.endSlide');  
        $count++;
    } 
    // This renders the end part of the slides code.	
    echo JHtml::_('bootstrap.endAccordion');

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

    echo JHtml::_('content.prepare', $params); 
    
    }
    }


	echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
