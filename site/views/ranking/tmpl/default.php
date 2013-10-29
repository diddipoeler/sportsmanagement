<?php defined('_JEXEC') or die('Restricted access');

if ( $this->show_debug_info )
{
echo 'default allteams<pre>',print_r($this->allteams,true),'</pre><br>';
}

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('projectheading', 'backbutton', 'footer');
JoomleagueHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="joomleague">
	<?php
	echo $this->loadTemplate('projectheading');

	if ($this->config['show_sectionheader'])
	{
		echo $this->loadTemplate('sectionheader');
	}

	if ($this->config['show_rankingnav']==1)
	{
		echo $this->loadTemplate('rankingnav');
	}

	if ($this->config['show_ranking']==1)
	{
	   // tabs anzeigen
       //echo 'currentRanking<pre>',print_r($this->currentRanking ,true),'</pre>';
       //echo 'homeRank<pre>',print_r($this->homeRank ,true),'</pre>';
       //echo 'awayRank<pre>',print_r($this->awayRank ,true),'</pre>';
    
   
    if ( JPluginHelper::isEnabled('content', 'jw_ts') )
    {
    $params = '';
    $startoutput = '{tab=';
    $endoutput = '{/tabs}';
    $params .= $startoutput.JText::_($this->config['table_text_1']).'}';
    $params .= $this->loadTemplate('ranking');
    $params .= $startoutput.JText::_($this->config['table_text_2']).'}';
    $params .= $this->loadTemplate('ranking_home'); 
    $params .= $startoutput.JText::_($this->config['table_text_3']).'}';
    $params .= $this->loadTemplate('ranking_away');  
    
    if ($this->config['show_half_of_season']==1)
	{
	$params .= $startoutput.JText::_($this->config['table_text_4']).'}';
    $params .= $this->loadTemplate('ranking_first'); 
    $params .= $startoutput.JText::_($this->config['table_text_5']).'}';
    $params .= $this->loadTemplate('ranking_second'); 
    }
    
    $params .= $endoutput;
    echo JHTML::_('content.prepare', $params);
    }
    else
    {
    $idxTab = 1;
  echo JHTML::_('tabs.start','tabs_ranking', array('useCookie'=>1));
  echo JHTML::_('tabs.panel', JText::_($this->config['table_text_1']), 'panel'.($idxTab++));
		echo $this->loadTemplate('ranking');
        echo JHTML::_('tabs.panel', JText::_($this->config['table_text_2']), 'panel'.($idxTab++));
		echo $this->loadTemplate('ranking_home');
        echo JHTML::_('tabs.panel', JText::_($this->config['table_text_3']), 'panel'.($idxTab++));
		echo $this->loadTemplate('ranking_away');
    
    if ($this->config['show_half_of_season']==1)
	{
	echo JHTML::_('tabs.panel', JText::_($this->config['table_text_4']), 'panel'.($idxTab++));
	echo $this->loadTemplate('ranking_first');
    echo JHTML::_('tabs.panel', JText::_($this->config['table_text_5']), 'panel'.($idxTab++));
	echo $this->loadTemplate('ranking_second');
    }   
        
echo JHTML::_('tabs.end');    
    }
    
    }

	if ($this->config['show_colorlegend']==1)
	{
		echo $this->loadTemplate('colorlegend');
	}
	
	if ($this->config['show_explanation']==1)
	{
		echo $this->loadTemplate('explanation');
	}
	
	if ($this->config['show_pagnav']==1)
	{
		echo $this->loadTemplate('pagnav');
	}
	
	if ($this->config['show_notes'] == 1)
	{
		echo $this->loadTemplate('notes');
	}
	
	if (($this->config['show_ranking_maps'])==1)
	{ 
		echo $this->loadTemplate('maps');
	}
	
	if ($this->config['show_help'] == "1")
	{
		echo $this->loadTemplate('hint');
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

	echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
