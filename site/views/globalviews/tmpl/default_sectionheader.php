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

// welche joomla version
if(version_compare(JVERSION,'3.0.0','ge')) 
{
JHtml::_('behavior.framework', true);
}
else
{
JHtml::_( 'behavior.mootools' );    
}

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
		if ( isset( $this->round->name ) )
		{
			$matchDate = sportsmanagementHelper::getTimestamp( $this->match->match_date, 1 );
			echo '&nbsp;' . JText::sprintf(	$pageTitle,
			$this->round->name,
			JHtml::date( $matchDate, JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_GAMES_DATE' ) ),
			sportsmanagementHelperHtml::showMatchTime($this->match, $this->config, $this->overallconfig, $this->project) );
		
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
		
    $link = sportsmanagementHelperRoute::getPlayerRoute( $this->project->id, $this->teamPlayer->team_id, $this->person->id, 'person.edit' );
    /*
		$desc = JHtml::image(
			"media/com_sportsmanagement/jl_images/edit.png",
			JText::_( 'COM_SPORTSMANAGEMENT_PERSON_EDIT' ),
			array( "title" => JText::_( "COM_SPORTSMANAGEMENT_PERSON_EDIT" ) )
		);
	    echo " ";
	    echo JHtml::_('link', $link, $desc );
	    */
	
	?>   
	             <a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									href="<?php echo $link; ?>"
									 class="modal">
									<?php
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													JText::_('COM_SPORTSMANAGEMENT_PERSON_EDIT'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_PERSON_EDIT').'"');
									?>
								</a>
                <?PHP
                
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
		/*
    $link = JoomleagueHelperRoute::getPlayerRoute( $this->project->id, $this->teamPlayer->team_id, $this->person->id, 'person.edit' );
		$desc = JHtml::image(
			"media/com_sportsmanagement/jl_images/edit.png",
			JText::_( 'COM_SPORTSMANAGEMENT_PERSON_EDIT' ),
			array( "title" => JText::_( "COM_SPORTSMANAGEMENT_PERSON_EDIT" ) )
		);
	    echo " ";
	    echo JHtml::_('link', $link, $desc );
	    */
	
	?>   
	             <a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									href="index.php?option=com_sportsmanagement&tmpl=component&view=editperson&id=<?php echo $this->person->id; ?>"
									 class="modal">
									<?php
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													JText::_('COM_SPORTSMANAGEMENT_PERSON_EDIT'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_PERSON_EDIT').'"');
									?>
								</a>
                <?PHP
                
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
<a id="jl_top"></a>

<table class="contentpaneopen">
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
				$link = sportsmanagementHelperRoute::getResultsRoute( $this->project->id, $this->roundid, $this->model->divisionid, $this->model->mode, $this->model->order, $this->config['result_style_edit'] );
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
			<?php echo sportsmanagementHelperHtml::getRoundSelectNavigation(FALSE,JRequest::getInt('cfg_which_database',0)); ?>
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
				$link=sportsmanagementHelperRoute::getIcalRoute($this->project->id,$this->teams[$this->ptid]->team_id,null,null);
				$text=JHtml::_('image','administrator/components/com_sportsmanagement/assets/images/calendar.png', JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_ICAL_EXPORT'));
				$attribs	= array('title' => JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_ICAL_EXPORT'));
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
			<td class="contentheading"><a name="division<?php echo $this->divisions;?>"></a>
			
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
			<td class="contentheading">
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
		<td class="contentheading">
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
        echo "<td>".JHtml::_('select.genericlist', $this->lists['type'], 'type' , 'class="inputbox" size="1" onchange="this.form.submit();" ', 'value', 'text', $this->type )."</td>";
        echo "<td>".JHtml::_('select.genericlist', $this->lists['typestaff'], 'typestaff' , 'class="inputbox" size="1" onchange="this.form.submit();" ', 'value', 'text', $this->typestaff )."</td>";
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
<div class="contentpaneopen">
	<div class="contentheading">
		<?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_PAGE_TITLE') . " - " . $this->team->tname;
		if ( $this->showediticon )
		{
			/*
            $link = JoomleagueHelperRoute::getProjectTeamInfoRoute( $this->project->id, $this->projectteamid, 'projectteam.edit' );
			$desc = JHtml::image(
					"media/com_sportsmanagement/jl_images/edit.png",
					JText::_( 'COM_SPORTSMANAGEMENT_PROJECTTEAM_EDIT' ),
					array( "title" => JText::_( "COM_SPORTSMANAGEMENT_PROJECTTEAMEDIT" ) )
			);
			echo " ";
			echo JHtml::_('link', $link, $desc );
            */
        ?>   
	             <a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									href="index.php?option=com_sportsmanagement&tmpl=component&view=editprojectteam&ptid=<?php echo $this->projectteamid; ?>&tid=<?php echo $this->teamid; ?>&p=<?php echo $this->project->id; ?>"
									 class="modal">
									<?php
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMINFO_EDIT_DETAILS'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMINFO_EDIT_DETAILS').'"');
									?>
								</a>
                <?PHP    
		} else {
			//echo "no permission";
		}
		?>
	</div>
</div>
<!-- END: Contentheading -->
<?PHP
    break;
    case 'clubinfo':
?>

	<div class="contentpaneopen">
		<div class="contentheading">
			<?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_TITLE' ) . " " . $this->club->name;

	            if ( $this->showediticon )
	            {
	                
                    $link = sportsmanagementHelperRoute::getClubInfoRoute( $this->project->id, $this->club->id, "club.edit" );
//	                $desc = JHtml::image(
//	                                      "media/COM_SPORTSMANAGEMENT/jl_images/edit.png",
//	                                      JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_EDIT' ),
//	                                      array( "title" => JText::_( "COM_SPORTSMANAGEMENT_CLUBINFO_EDIT" ) )
//	                                   );
//	                echo " ";
//	                echo JHtml::_('link', $link, $desc );
                    
                    
                 ?>   
	             <a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									href="<?php echo $link; ?>"
									 class="modal">
									<?php
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBINFO_EDIT_DETAILS'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBINFO_EDIT_DETAILS').'"');
									?>
								</a>
                <?PHP
                
                
                }
			?>
		</div>
	</div>
    <?PHP    
    break;
    default:
    ?>
    <table class="table" >
    <tr>
    <td class="contentheading">
    <?php
    echo $this->headertitle;
    ?>
    </td>
    </tr>
    </table>
    <br />
    <?PHP
    break;
}
?>
