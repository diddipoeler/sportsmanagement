<?php defined( '_JEXEC' ) or die( 'Restricted access' );
JHtml::_('behavior.tooltip');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

$hasMatchPlayerStats = false;
$hasMatchStaffStats = false;

if (!empty($this->matchplayerpositions ))
{
	$hasMatchPlayerStats = false;
	$hasMatchStaffStats = false;
	foreach ( $this->matchplayerpositions as $pos )
	{
		if(isset($this->stats[$pos->position_id]) && count($this->stats[$pos->position_id])>0) {
			foreach ($this->stats[$pos->position_id] as $stat) {
				if ($stat->showInSingleMatchReports() && $stat->showInMatchReport()) {
					$hasMatchPlayerStats = true;
					break;
				}
			}
		}
	}	
	foreach ( $this->matchstaffpositions as $pos )
	{
		if(isset($this->stats[$pos->position_id]) && count($this->stats[$pos->position_id])>0) {
			foreach ($this->stats[$pos->position_id] as $stat) {
				if ($stat->showInSingleMatchReports() && $stat->showInMatchReport()) {
					$hasMatchStaffStats = true;
				}
			}
		}
	}
}    


?>
<div class="joomleague"><?php
	echo $this->loadTemplate('projectheading');

	if (($this->config['show_sectionheader'])==1)
	{
		echo $this->loadTemplate('sectionheader');
	}

	if (($this->config['show_result'])==1)
	{
		echo $this->loadTemplate('result');
	}
    
    if ( !empty( $this->matchevents ) )
	{
    if (($this->config['show_timeline_under_results'])==1)
	{
		echo $this->loadTemplate('timeline');
	}
    }
    
  // ################################################################
  // diddipoeler
  // aufbau der templates
  $output = array();
  if (($this->config['show_details'])==1)
	{
		$output['COM_SPORTSMANAGEMENT_MATCHREPORT_DETAILS'] = 'details';
	}
  if (($this->config['show_extended'])==1 && $this->extended )
	{
        $output['COM_SPORTSMANAGEMENT_TABS_EXTENDED'] = 'extended';
	}
	if (($this->config['show_roster'])==1)
	{
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_LINE_UP_PLAYER'] = 'roster';
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_LINE_UP_STAFF'] = 'staff';
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTES'] = 'subst';
	}
    if (($this->config['show_roster_playground'])==1)
	{
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_PLAYGROUND'] = 'rosterplayground';
	}
    if ( !empty( $this->matchevents ) )
	{
		if (($this->config['show_timeline'])==1 && $this->config['show_timeline_under_results']==0 )
		{
            $output['COM_SPORTSMANAGEMENT_MATCHREPORT_TIMELINE'] = 'timeline';
		}
        if (($this->config['show_events'])==1)
		{
					if ( !empty( $this->eventtypes ) ) 
                    {
                        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'] = 'events';
					}
        }
        
    }    
    if (($this->config['show_stats'])==1 && ( $hasMatchPlayerStats || $hasMatchStaffStats ) )
	{
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_STATISTICS'] = 'stats';
	}

	if (($this->config['show_summary'])==1 && $this->match->summary )
	{
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_SUMMARY'] = 'summary';
	}
    
    if (($this->config['show_commentary'])==1 && $this->matchcommentary )
	{
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_COMMENTARY'] = 'commentary';
	}
	
	if (($this->config['show_pictures'])==1 && $this->matchimages )
	{
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_PICTURES'] = 'pictures';
	}    

/*
  // ################################################################
  if ( $this->use_joomlaworks == 0 || $this->config['show_result_tabs'] == "no_tabs" )
    {
  // anzeige mit tabs ?
  if ( ($this->config['show_result_tabs']) == "no_tabs" )
	{

	if (($this->config['show_details'])==1)
	{
		echo $this->loadTemplate('details');
	}

	if (($this->config['show_extended'])==1 && $this->extended )
	{
		echo $this->loadTemplate('extended');
	}

	if (($this->config['show_roster'])==1)
	{
		echo $this->loadTemplate('roster');
		echo $this->loadTemplate('staff');
		echo $this->loadTemplate('subst');
		
	}
  
  if (($this->config['show_roster_playground'])==1)
	{
		echo $this->loadTemplate('rosterplayground');
	}

	if ( !empty( $this->matchevents ) )
	{
		if (($this->config['show_timeline'])==1 && $this->config['show_timeline_under_results']==0)
		{
			echo $this->loadTemplate('timeline');
		}

		if (($this->config['show_events'])==1)
		{
			switch ($this->config['use_tabs_events'])
			{
				case 0:
//					/** No tabs
					if ( !empty( $this->eventtypes ) ) {
						echo $this->loadTemplate('events');
					}
					break;
				case 1:
//					/** Tabs 
					if ( !empty( $this->eventtypes ) ) {
						echo $this->loadTemplate('events_tabs');
					}
					break;
				case 2:
//					/** Table/Ticker layout 
					echo $this->loadTemplate('events_ticker');
					break;
			}
		}
	}

	if (($this->config['show_stats'])==1 && ( $hasMatchPlayerStats || $hasMatchStaffStats ) )
	{
		echo $this->loadTemplate('stats');
	}

	if (($this->config['show_summary'])==1 && $this->match->summary )
	{
		echo $this->loadTemplate('summary');
	}
    
    if (($this->config['show_commentary'])==1 && $this->matchcommentary )
	{
        echo $this->loadTemplate('commentary');
	}
	
	if (($this->config['show_pictures'])==1 && $this->matchimages )
	{
		echo $this->loadTemplate('pictures');
	}

  }
  else if ( ($this->config['show_result_tabs']) == "show_tabs" )
  {
  // tabs anzeigen
  $idxTab = 1;
  echo JHtml::_('tabs.start','tabs_matchreport', array('useCookie'=>1));
  
  	if (($this->config['show_details'])==1)
	{
	echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_DETAILS'), 'panel'.($idxTab++));
		echo $this->loadTemplate('details');
	}

	if (($this->config['show_extended'])==1 && $this->extended )
	{
	echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED'), 'panel'.($idxTab++));
		echo $this->loadTemplate('extended');
	}

	if (($this->config['show_roster'])==1)
	{
	echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING'), 'panel'.($idxTab++));
		echo $this->loadTemplate('roster');
		echo $this->loadTemplate('staff');
		echo $this->loadTemplate('subst');
	}

  if (($this->config['show_roster_playground'])==1)
	{
  echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_PLAYGROUND'), 'panel'.($idxTab++));
		echo $this->loadTemplate('rosterplayground');
	}
  
	if ( !empty( $this->matchevents ) )
	{
		if (($this->config['show_timeline'])==1 && $this->config['show_timeline_under_results']==0 )
		{
		echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TIMELINE'), 'panel'.($idxTab++));
			echo $this->loadTemplate('timeline');
		}

		if (($this->config['show_events'])==1)
		{
			switch ($this->config['use_tabs_events'])
			{
				case 0:
//					/** No tabs 
					if ( !empty( $this->eventtypes ) ) {
					echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'), 'panel'.($idxTab++));
						echo $this->loadTemplate('events');
					}
					break;
				case 1:
//					/** Tabs 
					if ( !empty( $this->eventtypes ) ) {
					echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'), 'panel'.($idxTab++));
						echo $this->loadTemplate('events_tabs');
					}
					break;
				case 2:
//					/** Table/Ticker layout 
					echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'), 'panel'.($idxTab++));
					echo $this->loadTemplate('events_ticker');
					break;
			}
		}
	}

	if (($this->config['show_stats'])==1 && ( $hasMatchPlayerStats || $hasMatchStaffStats ) )
	{
	echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_STATISTICS'), 'panel'.($idxTab++));
		echo $this->loadTemplate('stats');
	}

	if (($this->config['show_summary'])==1  && $this->match->summary  )
	{
	echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_SUMMARY'), 'panel'.($idxTab++));
		echo $this->loadTemplate('summary');
	}
    
    if (($this->config['show_commentary'])==1 && $this->matchcommentary )
	{
	   echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_COMMENTARY'), 'panel'.($idxTab++));
        echo $this->loadTemplate('commentary');
	}
  
  if (($this->config['show_pictures'])==1  && $this->matchimages )
	{
	echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_PICTURES'), 'panel'.($idxTab++));
  echo $this->loadTemplate('pictures');
  }
  
  echo JHtml::_('tabs.end');
  }
  else if ( ($this->config['show_result_tabs']) == "show_slider" )
  {
  // slider anzeigen
  $idxTab = 1;
  echo JHtml::_('sliders.start','slider_matchreport', array('useCookie'=>1));
  
  	if (($this->config['show_details'])==1)
	{
	echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_DETAILS'), 'panel'.($idxTab++));
		echo $this->loadTemplate('details');
	}

	if (($this->config['show_extended'])==1)
	{
	echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED'), 'panel'.($idxTab++));
		echo $this->loadTemplate('extended');
	}

	if (($this->config['show_roster'])==1)
	{
	echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING'), 'panel'.($idxTab++));
		echo $this->loadTemplate('roster');
		echo $this->loadTemplate('staff');
		echo $this->loadTemplate('subst');
	}

	if ( !empty( $this->matchevents ) )
	{
		if (($this->config['show_timeline'])==1 && $this->config['show_timeline_under_results']==0)
		{
		echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TIMELINE'), 'panel'.($idxTab++));
			echo $this->loadTemplate('timeline');
		}

		if (($this->config['show_events'])==1)
		{
			switch ($this->config['use_tabs_events'])
			{
				case 0:
//					/** No tabs 
					if ( !empty( $this->eventtypes ) ) {
					echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'), 'panel'.($idxTab++));
						echo $this->loadTemplate('events');
					}
					break;
				case 1:
//					/** Tabs 
					if ( !empty( $this->eventtypes ) ) {
					echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'), 'panel'.($idxTab++));
						echo $this->loadTemplate('events_tabs');
					}
					break;
				case 2:
//					/** Table/Ticker layout 
					echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'), 'panel'.($idxTab++));
					echo $this->loadTemplate('events_ticker');
					break;
			}
		}
	}

	if (($this->config['show_stats'])==1)
	{
	echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_STATISTICS'), 'panel'.($idxTab++));
		echo $this->loadTemplate('stats');
	}

	if (($this->config['show_summary'])==1 && $this->match->summary )
	{
	echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_SUMMARY'), 'panel'.($idxTab++));
		echo $this->loadTemplate('summary');
	}
    
    if (($this->config['show_commentary'])==1 && $this->matchcommentary )
	{
	   echo JHtml::_('tabs.panel', JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_COMMENTARY'), 'panel'.($idxTab++));
        echo $this->loadTemplate('commentary');
	}
 
  echo JHtml::_('sliders.end');
  }

  }
  else
  {
*/
    
  // diddipoeler
  // anzeige als tabs oder slider von joomlaworks
  $startoutput = '';
    $params = '';
    if($this->config['show_result_tabs'] == "show_tabs") 
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
    else if($this->config['show_result_tabs'] == "show_slider") 
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

    echo JHtml::_('content.prepare', $params); 
    
  //}
  
	echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
