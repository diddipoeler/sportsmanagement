<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_events_tabs.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<!-- START of match events -->

<h2>
<?php 
echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'); 	
?>
</h2>	
<?php
if ( $this->config['show_timeline'] && !$this->config['show_timeline_under_results'] )
{
echo $this->loadTemplate('timeline');
}
	
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
$visible = 'text';    
echo '<br />eventtypes<pre>~' . print_r($this->eventtypes,true) . '~</pre><br />';
}
else
{
$visible = 'hidden';
}

if(version_compare(JVERSION,'3.0.0','ge'))  
{ 
// Joomla! 3.0 code here 
$idxTab = 0; 

?>
<!-- This is a list with tabs names. anfang --> 
<div class="panel with-nav-tabs panel-default"> 
<!-- Tabs-heading anfang --> 
<div class="panel-heading"> 
<!-- Tabs-Navs anfang --> 
<ul class="nav nav-tabs" role="tablist"> 
<?PHP
foreach ($this->eventtypes AS $event)
{
$active = ($idxTab==0) ? 'in active' : ''; 

$pic_tab = $event->icon;
if ($pic_tab == '/events/event.gif')
{
$text_bild = '';
$text = JText::_($event->name);
}
else
{
$imgTitle = JText::_($event->name);
$imgTitle2 = array(' title' => $imgTitle, ' alt' => $imgTitle, ' style' => 'max-height:40px;');
$text_bild = JHtml::image(JURI::root().$pic_tab,$imgTitle,$imgTitle2);
$text = JText::_($event->name);
}


?>
<li role="presentation" class="<?PHP echo $active; ?>"><a href="#event<?PHP echo $event->id; ?>" role="tab" data-toggle="tab"><?PHP echo $text_bild.$text; ?></a>
</li>

<?PHP
$idxTab++;
}
?>
<!-- Tabs-Navs ende --> 
</ul> 
<!-- Tabs-heading ende --> 
</div> 


<!-- Tab-Inhalte anfang-->
<div class="panel-body">
<!-- Tab-content anfang-->
<div class="tab-content">
<?PHP	
$idxTab = 0;
foreach ($this->eventtypes AS $event)
{
$active = ($idxTab==0) ? 'in active' : '';   
$text = JText::_($event->name);

?>
<!-- Tab-event anfang-->
<div role="tabpanel" class="tab-pane fade <?PHP echo $active; ?>" id="event<?PHP echo $event->id; ?>">
<?PHP
$idxTab++;
foreach ($this->matchevents AS $me)
				{
					if ( $me->event_type_id == $event->id && 
					   ( $me->ptid == $this->match->projectteam1_id or $me->ptid == $this->match->projectteam2_id )
					   )
					{
						
						if ($this->config['show_event_minute'] == 1 && $me->event_time > 0)
						{
						    $prefix = str_pad($me->event_time, 2 ,'0', STR_PAD_LEFT)."' ";
						} else {
						    $prefix = null;
						} 
						
						$match_player = sportsmanagementHelper::formatName($prefix, $me->firstname1, $me->nickname1, $me->lastname1, $this->config["name_format"]);
                        if ($this->config['event_link_player'] == 1 && $me->playerid != 0)
                        {

$routeparameter = array(); 
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0); 
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0); 
$routeparameter['p'] = $this->project->slug; 
$routeparameter['tid'] = $me->team_id; 
$routeparameter['pid'] = $me->playerid; 
$player_link = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter); 
                        
                            //$player_link=sportsmanagementHelperRoute::getPlayerRoute($this->project->slug,$me->team_id,$me->playerid);
                            $match_player = JHtml::link($player_link,$match_player);
                        }
                        echo $match_player;

						// only show event sum and match notice when set to on in template cofig
						$sum_notice = "";
						if($this->config['show_event_sum'] == 1 || $this->config['show_event_notice'] == 1)
						{
						    if (($this->config['show_event_sum'] == 1 && $me->event_sum > 0) || ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0))
							{
								$sum_notice .= ' (';
									if ($this->config['show_event_sum'] == 1 && $me->event_sum > 0)
									{
										$sum_notice .= $me->event_sum;
									}
									if (($this->config['show_event_sum'] == 1 && $me->event_sum > 0) && ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0))
									{
										$sum_notice .= ' | ';
									}
									if ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0)
									{
										$sum_notice .= $me->notice;
									}
								$sum_notice .= ')';
							}
						}
						echo $sum_notice;

						echo '<br>';
					}
				}
?>
<!-- Tab-event ende-->
</div>	

<?php
}
?>

<!-- Tab-content ende-->
</div>
<!-- Tab-Inhalte ende-->
</div>
<!-- This is a list with tabs names. ende --> 
</div> 

<?PHP
}
else
{
?>

<table class="table" border="0">
    <tr>
        <td>
            <?php 
            $result='';
		    $txt_tab='';
            // Make event tabs with JPane integrated function in Joomla 1.5 API
			$result = JPane::getInstance('tabs',array('startOffset'=>0));
			echo $result->startPane('pane');
			foreach ($this->eventtypes AS $event)
			{
				$pic_tab = $event->icon;
				if ($pic_tab == '/events/event.gif')
				{
					$txt_tab = JText::_($event->name);
				}
				else
				{
					$imgTitle=JText::_($event->name);
					$imgTitle2=array(' title' => $imgTitle, ' alt' => $imgTitle, ' style' => 'max-height:40px;');
					$txt_tab=JHtml::image(JURI::root().$pic_tab,$imgTitle,$imgTitle2);
				}

				echo $result->startPanel($txt_tab,$event->id);
				echo '<table class="table">';
				echo '<tr>';
				echo '<td class="list">';
				echo '<ul>';
				foreach ($this->matchevents AS $me)
				{
					if ($me->event_type_id==$event->id && $me->ptid==$this->match->projectteam1_id)
					{
						echo '<li class="list">';
						
						if ($this->config['show_event_minute'] == 1 && $me->event_time > 0)
						{
						    $prefix = str_pad($me->event_time, 2 ,'0', STR_PAD_LEFT)."' ";
						} else {
						    $prefix = null;
						} 
						
						$match_player = sportsmanagementHelper::formatName($prefix, $me->firstname1, $me->nickname1, $me->lastname1, $this->config["name_format"]);
                        if ($this->config['event_link_player'] == 1 && $me->playerid != 0)
                        {
                            $player_link=sportsmanagementHelperRoute::getPlayerRoute($this->project->slug,$me->team_id,$me->playerid);
                            $match_player = JHtml::link($player_link,$match_player);
                        }
                        echo $match_player;

						// only show event sum and match notice when set to on in template cofig
						$sum_notice = "";
						if($this->config['show_event_sum'] == 1 || $this->config['show_event_notice'] == 1)
						{
						    if (($this->config['show_event_sum'] == 1 && $me->event_sum > 0) || ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0))
							{
								$sum_notice .= ' (';
									if ($this->config['show_event_sum'] == 1 && $me->event_sum > 0)
									{
										$sum_notice .= $me->event_sum;
									}
									if (($this->config['show_event_sum'] == 1 && $me->event_sum > 0) && ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0))
									{
										$sum_notice .= ' | ';
									}
									if ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0)
									{
										$sum_notice .= $me->notice;
									}
								$sum_notice .= ')';
							}
						}
						echo $sum_notice;

						echo '</li>';
					}
				}
				echo '</ul>';
				echo '</td>';
				echo '<td class="list">';
				echo '<ul>';
				foreach ($this->matchevents AS $me)
				{
					if ($me->event_type_id==$event->id && $me->ptid==$this->match->projectteam2_id)
					{
						echo '<li class="list">';

						if ($this->config['show_event_minute'] == 1 && $me->event_time > 0)
						{
						    $prefix = str_pad($me->event_time, 2 ,'0', STR_PAD_LEFT)."' ";
						} else {
						    $prefix = null;
						}

						$match_player = sportsmanagementHelper::formatName($prefix, $me->firstname1, $me->nickname1, $me->lastname1, $this->config["name_format"]);
						if ($this->config['event_link_player'] == 1 && $me->playerid != 0)
						{
							$player_link=sportsmanagementHelperRoute::getPlayerRoute($this->project->slug,$me->team_id,$me->playerid);
							$match_player = JHtml::link($player_link,$match_player);
						}
						echo $match_player;

						// only show event sum and match notice when set to on in template cofig
						$sum_notice = "";
						if($this->config['show_event_sum'] == 1 || $this->config['show_event_notice'] == 1)
						{
						    if (($this->config['show_event_sum'] == 1 && $me->event_sum > 0) || ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0))
							{
								$sum_notice .= ' (';
									if ($this->config['show_event_sum'] == 1 && $me->event_sum > 0)
									{
										$sum_notice .= $me->event_sum;
									}
									if (($this->config['show_event_sum'] == 1 && $me->event_sum > 0) && ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0))
									{
										$sum_notice .= ' | ';
									}
									if ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0)
									{
										$sum_notice .= $me->notice;
									}
								$sum_notice .= ')';
							}
						}
						echo $sum_notice;

						echo '</li>';
					}
				}
				echo '</ul>';
				echo '</td>';
				echo '</tr>';
				echo '</table>';
				echo $result->endPanel();
			}
			echo $result->endPane();
            ?>
        </td>
    </tr>
</table>
<!-- END of match events -->
<br />
<?PHP
}

