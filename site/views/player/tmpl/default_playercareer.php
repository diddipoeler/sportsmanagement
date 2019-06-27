<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_playercareer.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage player
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
?>
<div class="<?php echo $this->divclassrow;?> table-responsive" id="player">
<?php
if (count($this->historyPlayer) > 0)
{
	?>
	<!-- Player history START -->
	<h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PLAYING_CAREER'); ?></h2>
	
				<table id="playerhistory" class="<?PHP echo $this->config['history_table_class']; ?> table-responsive" >
					<tr class="sectiontableheader">
						<th class="td_l">
                        <?php echo 
                        Text::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION');
							?>
                            </th>
						<th class="td_l">
                        <?php 
                        echo Text::_('COM_SPORTSMANAGEMENT_PERSON_SEASON');
							?>
                            </th>
                            
                             <?PHP
                if ( $this->config['show_plcareer_team'] )
	{
	   ?>
						<th class="td_l">
                        <?php 
                        echo Text::_('COM_SPORTSMANAGEMENT_PERSON_TEAM');
							?>
                            </th>
                            <?PHP
                            }
                            if ( $this->config['show_plcareer_ppicture'] )
	{
                            ?>
                        <th class="td_l">
                        <?php 
                        echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE');
							?>
                            </th>  
                             <?PHP
                            }
                            
                            ?>  
						<th class="td_l">
                        <?php 
                        echo Text::_('COM_SPORTSMANAGEMENT_PERSON_POSITION');
							?>
                            </th>
					</tr>
					<?php
					$k=0;
					foreach ($this->historyPlayer AS $station)
					{
					   $routeparameter = array();
       $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $station->project_slug;
       $routeparameter['tid'] = $station->team_slug;
       $routeparameter['pid'] = $station->person_slug;
            
                    $link1 = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter);
                     $routeparameter = array();
       $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $station->project_slug;
       $routeparameter['tid'] = $station->team_slug;
       $routeparameter['ptid'] = 0;
					$link2 = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
                    

						?>
						<tr class="">
							<td class="td_l">
							<?php
                            if ( $this->config['show_project_logo'] ) 
                            { 
//if ( !curl_init( $station->project_picture ) )
//				{
//					$station->project_picture = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");
//				}  
$station->project_picture = ($station->project_picture != '') ? $station->project_picture : sportsmanagementHelper::getDefaultPlaceholder("clublogobig");                
                                              
                            echo sportsmanagementHelperHtml::getBootstrapModalImage('playercareerproject'.$station->project_id.'-'.$station->team_id,
                            $station->project_picture,
                            $station->project_name,
                            '20',
            '',
            $this->modalwidth,
            $this->modalheight,
            $this->overallconfig['use_jquery_modal']); 
							
                            }	
                                echo HTMLHelper::link($link1,$station->project_name);
							?></td>
							<td class="td_l">
                            <?php 
                            echo $station->season_name;
								?>
                                </td>
                                 <?PHP
                if ( $this->config['show_plcareer_team'] )
	{
	   ?>
							<td class="td_l">
                            <?php 
                            if ( $this->config['show_team_logo'] ) 
                            { 
//if ( !curl_init( $station->club_picture ) )
//				{
//					$station->club_picture = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");
//				}               
$station->club_picture = ($station->club_picture != '') ? $station->club_picture : sportsmanagementHelper::getDefaultPlaceholder("clublogobig");                                   
echo sportsmanagementHelperHtml::getBootstrapModalImage('playercareerteam'.$station->project_id.'-'.$station->team_id,
$station->club_picture,
$station->team_name,
'20',
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);
                            }
                            
			if ( $this->config['show_team_picture'] ) 
                            { 
$station->team_picture = ($station->team_picture != '') ? $station->team_picture : sportsmanagementHelper::getDefaultPlaceholder("team");                                
echo sportsmanagementHelperHtml::getBootstrapModalImage('playercareerteampicture'.$station->project_id.'-'.$station->team_id,
$station->team_picture,
$station->team_name,
'40',
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);				
				
			}	
			
							if ( $this->config['show_playercareer_teamlink'] ) 
                            {
								echo HTMLHelper::link($link2,$station->team_name);
							} 
                            else 
                            {
								echo $station->team_name;
							}
							?>
                            </td>
                            <?PHP
                            }
                            if ( $this->config['show_plcareer_ppicture'] )
	{
                            ?>
                            <td>
                <?PHP
$station->season_picture = ($station->season_picture != '') ? $station->season_picture : sportsmanagementHelper::getDefaultPlaceholder("team");  
echo sportsmanagementHelperHtml::getBootstrapModalImage('playercareerperson'.$station->project_id.'-'.$station->team_id,
$station->season_picture,
$station->team_name,
'50',
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);
                ?>
                </td>
                 <?PHP
                            }
                            
                            ?>
							<td class="td_l"><?php echo Text::_($station->position_name);
								?></td>
						</tr>
						<?php
						$k=(1-$k);
					}
					?>
				</table>
			
	<!-- Player history END -->
	<?php
}
?>
</div>