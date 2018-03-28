<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_sectionheader.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage globalviews
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 

// Reference global application object
$app = JFactory::getApplication();
// JInput object
$jinput = $app->input;
$view = $jinput->getVar( "view") ;        
$modalheight = JComponentHelper::getParams($jinput->getCmd('option'))->get('modal_popup_height', 600);
$modalwidth = JComponentHelper::getParams($jinput->getCmd('option'))->get('modal_popup_width', 900);


switch ( $view )
{
    
	case 'matchreport':
	?>
	<table class="table">
	<tr>
		<td class="contentheading"><?php
		$pageTitle = 'COM_SPORTSMANAGEMENT_MATCHREPORT_TITLE';
        $timestamp = strtotime($this->match->match_date);
		if ( isset( $this->round->name ) && $timestamp )
		{
			$matchDate = sportsmanagementHelper::getTimestamp( $this->match->match_date, 1 );
			echo '&nbsp;' . JText::sprintf(	$pageTitle,
			$this->round->name,
			JHtml::date( $matchDate, JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_GAMES_DATE' ) ),
			sportsmanagementHelperHtml::showMatchTime($this->match, $this->config, $this->overallconfig, $this->project) );
		
		}
        elseif ( isset( $this->round->name ) && !$timestamp )
		{
		  echo '&nbsp;' . JText::sprintf( $pageTitle, $this->round->name, '', '' );
		}  
		else
		{
			echo '&nbsp;' . JText::sprintf( $pageTitle, '', '', '' );
		}
		?></td>
	</tr>
</table>
    
    <?PHP
    break;
    case 'player':
    ?>
    <table class="table">
	<tr>
		<td class="contentheading">
			<?php
	echo JText::sprintf('COM_SPORTSMANAGEMENT_PLAYER_INFORMATION', sportsmanagementHelper::formatName(null, $this->person->firstname, $this->person->nickname, $this->person->lastname, $this->config["name_format"]));
	
	if ( $this->showediticon )
	{
		
    // $link = sportsmanagementHelperRoute::getPlayerRoute( $this->project->id, $this->teamPlayer->team_id, $this->person->id, 'person.edit' );
    
    $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->id;
$routeparameter['tid'] = $this->teamPlayer->team_id;
$routeparameter['pid'] = $this->person->id;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter,'person.edit');
echo sportsmanagementHelperHtml::getBootstrapModalImage('personedit'.$this->person->id,
'administrator/components/com_sportsmanagement/assets/images/edit.png',
JText::_('COM_SPORTSMANAGEMENT_PERSON_EDIT'),
'20',
$link,
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);  	
                
  }

	if ( isset($this->teamPlayer->injury) && $this->teamPlayer->injury )
	{
		$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_INJURED' );
		echo "&nbsp;&nbsp;" . JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/injured.gif',
							$imageTitle,
							array( 'title' => $imageTitle ) );
	}

	if ( isset($this->teamPlayer->suspension) && $this->teamPlayer->suspension )
	{
		$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_SUSPENDED' );
		echo "&nbsp;&nbsp;" . JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/suspension.gif',
							$imageTitle,
							array( 'title' => $imageTitle ) );
	}


	if ( isset($this->teamPlayer->away) && $this->teamPlayer->away )
	{
		$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_AWAY' );
		echo "&nbsp;&nbsp;" . JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/away.gif',
							$imageTitle,
							array( 'title' => $imageTitle ) );
	}
			?>
		</td>
	</tr>
</table>
    
    <?PHP
    break;
    case 'staff':
    ?>
    <table class="table">
	<tr>
		<td class="contentheading">
			<?php
			echo $this->title;
			
			
			if ( $this->showediticon )
	{
$link = "index.php?option=com_sportsmanagement&tmpl=component&view=editperson&id=<?php echo $this->person->id; ?>";
echo sportsmanagementHelperHtml::getBootstrapModalImage('personedit'.$this->person->id,
'administrator/components/com_sportsmanagement/assets/images/edit.png',
JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMINFO_EDIT_DETAILS'),
'20',
$link,
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']); 		
	
	
                
  }
  ?>
		</td>
	</tr>
</table>
    
    <?PHP
    break;
    case 'results':
    ?>
    <!-- section header e.g. ranking, results etc. -->
<table class="table">
	<tr>
		<td class="contentheading">
		<?php
		if ( $this->roundid)
		{
			$title = JText::_( 'COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS' );
			if ( isset( $this->division ) )
			{
				$title = JText::sprintf( 'COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS2', '<i>' . $this->division->name . '</i>' );
			}
			sportsmanagementHelperHtml::showMatchdaysTitle(	$title, $this->roundid, $this->config );

			if ( $this->showediticon )
			{
			 $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = sportsmanagementModelProject::$projectslug;
$routeparameter['r'] = sportsmanagementModelProject::$roundslug;
$routeparameter['division'] = sportsmanagementModelResults::$divisionid;
$routeparameter['mode'] = sportsmanagementModelResults::$mode;
$routeparameter['order'] = sportsmanagementModelResults::$order;
$routeparameter['layout'] = $this->config['result_style_edit'];
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('results',$routeparameter);
				
				$imgTitle = JText::_( 'COM_SPORTSMANAGEMENT_RESULTS_ENTER_EDIT_RESULTS' );
				$desc = JHtml::image( 'media/com_sportsmanagement/jl_images/edit.png', $imgTitle, array( ' title' => $imgTitle ) );
				echo ' ';
				echo JHtml::link( $link, $desc );
			}
		}
		else
		{
			//1 request for current round
			// seems to be this shall show a plan of matches of a team???
			sportsmanagementHelperHtml::showMatchdaysTitle( JText::_( 'COM_SPORTSMANAGEMENT_RESULTS_PLAN' ) , 0, $this->config );
		}
		?>
		</td>
			<?php if ( $this->config['show_matchday_dropdown'] == 1 ) 
			{ 
			?>
		<form name='resultsRoundSelector' method='post'>
		<input type='hidden' name='option' value='com_sportsmanagement' />
		<td>
        <?php
		//echo JHtml::image(	'images/com_sportsmanagement/database/jl_images/arrow_left_small.png',$imgtitle, 'title= "' . $imgtitle . '"' );
        $imgtitle = JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_PREV' );
		echo JHtml::link(sportsmanagementModelPagination::$prevlink,JHtml::image(	'images/com_sportsmanagement/database/jl_images/arrow_left_small.png',$imgtitle, 'title= "' . $imgtitle . '"' ));
        //echo sportsmanagementModelPagination::$prevlink;
		?>
		</td>
	            <td class="contentheading" style="text-align:right; font-size: 100%;">
			<?php echo sportsmanagementHelperHtml::getRoundSelectNavigation(FALSE,JFactory::getApplication()->input->getInt('cfg_which_database',0)); ?>
				</td>
                <td>
        <?php
		//echo JHtml::image(	'images/com_sportsmanagement/database/jl_images/arrow_right_small.png',$imgtitle, 'title= "' . $imgtitle . '"' );
        $imgtitle = JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_NEXT' );
        echo JHtml::link(sportsmanagementModelPagination::$nextlink,JHtml::image(	'images/com_sportsmanagement/database/jl_images/arrow_right_small.png',$imgtitle, 'title= "' . $imgtitle . '"' ));
        //echo sportsmanagementModelPagination::$nextlink;
		?>
        </td>
                </form>
    	    <?php 
            } 
            ?>
		</tr>
</table>
    
    <?PHP
    break;
    case 'teamplan':
    ?>
    <table class="table">
	<tr>
		<td class="contentheading">
			<?php
			$output='';
			if ((isset($this->division)) && (is_a($this->division,'LeagueDivision')))
			{
				$output .= ' '.$this->division->name.' ';
			}
			if (!empty($this->ptid))
			{
				$output .= ' '.$this->teams[$this->ptid]->name;
			}
			else
			{
				$output .= ' '.$this->project->name;
			}
			echo JText::sprintf('COM_SPORTSMANAGEMENT_TEAMPLAN_PAGE_TITLE',$output);
			?>
		</td>
		<?php
		if($this->config['show_ical_link'])
		{
			?>
			<td class="contentheading" style="text-align: right;">
				<?php
				if (!is_null($this->ptid))
				{
				$link = sportsmanagementHelperRoute::getIcalRoute($this->project->id,$this->teams[$this->ptid]->team_id,null,null);
				$text = JHtml::_('image','administrator/components/com_sportsmanagement/assets/images/calendar.png', JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_ICAL_EXPORT'));
				$attribs = array('title' => JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_ICAL_EXPORT'));
				echo JHtml::_('link',$link,$text,$attribs);
				}
				?>
			</td>
			<?php
		}
		?>
	</tr>
</table><br />
    <?PHP
    break;
    case 'curve':
    ?>
    	<table class="table">
		<tr>
			<td class=""><a name="division<?php echo $this->divisions;?>"></a>
			
			<?php 
				echo JText::_('COM_SPORTSMANAGEMENT_CURVE_TITLE');
				if ($this->division) {
					echo ' '.$this->division->name;
				}
			?>
			</td>
		</tr>
	</table>
<br/>	
    <?PHP
    break;
    case 'matrix':
    ?>
    	<table class="table" >
		<tr>
			<td class="">
				<?php
				echo '&nbsp;' . JText::_( 'COM_SPORTSMANAGEMENT_MATRIX' );
				if ( $this->divisionid )
				{
					echo " " . $this->division->name;
				}
				if ( $this->roundid )
				{
					echo " - " . $this->round->name;
				}
				?>
			</td>
		</tr>
	</table>
<br />
    <?PHP
    break;
    case 'roster':
    ?>
    <table class="table">
	<tr>
		<td class="">
		<?php
		if ( $this->config['show_team_shortform'] == 1 && !empty($this->team->short_name))
		{
			echo '&nbsp;' . JText::sprintf( 'COM_SPORTSMANAGEMENT_ROSTER_TITLE2', $this->team->name, $this->team->short_name );
		}
		else
		{
			echo '&nbsp;' . JText::sprintf( 'COM_SPORTSMANAGEMENT_ROSTER_TITLE', $this->team->name );
		}
		?>
		</td>

		<form name='resultsRoundSelector' method='post'>
		<input type='hidden' name='option' value='com_sportsmanagement' />
        <?php
        if ( $this->config['show_drop_down_menue'] )
        {
        if ( $this->config['show_players'] )
        {
        echo "<td>".JHtml::_('select.genericlist', $this->lists['type'], 'type' , 'class="inputbox" size="1" onchange="this.form.submit();" ', 'value', 'text', $this->type )."</td>";
        }
        if ( $this->config['show_staff'] )
        {
        echo "<td>".JHtml::_('select.genericlist', $this->lists['typestaff'], 'typestaff' , 'class="inputbox" size="1" onchange="this.form.submit();" ', 'value', 'text', $this->typestaff )."</td>";
        }
        }
        ?>
        </form>
	</tr>
</table>
<br />
    <?PHP
    break;
    case 'nextmatch':
    ?>
    <table class="table">
	<tr>
		<td class="contentheading"><?php
		echo JHtml::date($this->match->match_date, JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_GAMES_DATE' ) ). " ".
		sportsmanagementHelperHtml::showMatchTime($this->match, $this->config, $this->overallconfig, $this->project); 
		?></td>
	</tr>
</table>
    <?PHP
    break;
    case 'teaminfo':
    ?>
<!-- START: Contentheading -->
<div class="">
	<h4>
		<?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_PAGE_TITLE') . " - " . $this->team->tname;
		if ( $this->showediticon )
		{
$link = "index.php?option=com_sportsmanagement&tmpl=component&view=editprojectteam&ptid=<?php echo $this->projectteamid; ?>&tid=<?php echo $this->teamid; ?>&p=<?php echo $this->project->id; ?>";			
echo sportsmanagementHelperHtml::getBootstrapModalImage('personedit'.$this->person->id,
'administrator/components/com_sportsmanagement/assets/images/edit.png',
JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMINFO_EDIT_DETAILS'),
'20',
$link,
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']); 			
            
		} else {
			//echo "no permission";
		}
		?>
	</h4>
</div>
<!-- END: Contentheading -->
<?PHP
    break;
    case 'clubinfo':
?>


		<h4>
			<?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_TITLE' ) . " " . $this->club->name;

	            if ( $this->showediticon )
	            {
	                
                    $link = sportsmanagementHelperRoute::getClubInfoRoute( $this->project->id, $this->club->id, "club.edit" );
                    
echo sportsmanagementHelperHtml::getBootstrapModalImage('personedit'.$this->person->id,
'administrator/components/com_sportsmanagement/assets/images/edit.png',
JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMINFO_EDIT_DETAILS'),
'20',
$link,
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);                  
                
                
                }
			?>
		</h4>
<!--	</div> -->
    <?PHP    
    break;
    default:
    ?>
    <table class="table" >
    <tr>
    <td class="contentheading">
    <h4>
    <?php
    echo $this->headertitle;
    ?>
    </h4>
    </td>
    </tr>
    </table>
    <br />
    <?PHP
    break;
}
?>
