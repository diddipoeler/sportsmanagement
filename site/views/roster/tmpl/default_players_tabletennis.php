<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage roster
 * @file       default_player_tabletennis.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

if ( Factory::getConfig()->get('debug') )
{
echo __METHOD__ . ' ' . ' ' . __LINE__ . ' ' . ' rows <pre>'.print_r($this->rows,true).'</pre>';
}

?>
<div class="container" id="roster_tabletennis">
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="defaultplayers_tabletennis" itemscope itemtype="http://schema.org/SportsTeam">

 <?php
        foreach ( $this->projectpositions as $positions => $position ) if( $position->persontype == 1 )
{
        ?>
        <table class="<?php echo $this->config['table_class']; ?> table-sm nowrap " id="tableplayer_tabletennis<?php echo $position->id;?>" width="100%">
			<?php
			/**
			 *
			 * jetzt kommt die schleife über die positionen
			 */
			foreach ($this->rows as $position_id => $players) if ( $position_id == $position->id )
			{
			$row      = current($players); 
foreach ($players as $row)
				{
					?>
                    <tr class="" width="" onMouseOver="this.bgColor='#CCCCFF'" onMouseOut="this.bgColor='#ffffff'" itemprop="member" itemscope="" itemtype="http://schema.org/Person">
						<?php
                 $playerName = sportsmanagementHelper::formatName(
							null, $row->firstname,
							$row->nickname,
							$row->lastname,
							$this->config["name_format"]
						); 
                        $picture = $row->ppic ? $row->ppic : $row->picture;      
?>
                            <td class="" width="" nowrap="nowrap">
                              <span itemprop="name" content="<?php echo $playerName;?>"></span> 
                              <span itemprop="birthDate" content="<?php echo $row->birthday;?>"></span>
				   <span itemprop="deathDate" content="<?php echo $row->deathday;?>"></span>
				    <span itemprop="nationality" content="<?php echo JSMCountries::getCountryName($row->country);?>"></span>
                              
								<?PHP
								echo sportsmanagementHelperHtml::getBootstrapModalImage(
									'player' . $row->playerid,
									$picture,
									$playerName,
									$this->config['player_picture_height'],
									'',
									$this->modalwidth,
									$this->modalheight,
									$this->overallconfig['use_jquery_modal'],
                                  'itemprop',
                                  'image'
								);
								?>
                            </td>
                            <td width=""  class=""nowrap="nowrap" style="text-align:center; ">
							<?php echo JSMCountries::getCountryFlag($row->country); ?>
                            </td>
                            <td class="" width=""><?php
							if ($this->config['link_player'] == 1)
							{
								$routeparameter                       = array();
								$routeparameter['cfg_which_database'] = Factory::getApplication()->input->get('cfg_which_database', 0);
								$routeparameter['s']                  = Factory::getApplication()->input->get('s', '');
								$routeparameter['p']                  = $this->project->slug;
								$routeparameter['tid']                = $this->team->slug;
								$routeparameter['pid']                = $row->person_slug;

								$link = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);
								//echo HTMLHelper::link($link, '<span class="playername">' . $playerName . '</span>');
                                echo HTMLHelper::link($link, $playerName);
							}
							else
							{
								//echo '<span class="playername">' . $playerName . '</span>';
                                echo $playerName;
							}
							?>
                            </td>
                            	<?php
						if ($this->config['show_birthday'] > 0)
						{
							?>
                            <td class="" width="" nowrap="nowrap" style="text-align: center;"><?php
							if ($row->birthday != "0000-00-00")
							{
								switch ($this->config['show_birthday'])
								{
									case 1:     // Show Birthday and Age
										$birthdateStr = HTMLHelper::date($row->birthday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'));
										$birthdateStr .= "&nbsp;(" . sportsmanagementHelper::getAge($row->birthday, $row->deathday) . ")";
										break;

									case 2:     // Show Only Birthday
										$birthdateStr = HTMLHelper::date($row->birthday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'));
										break;

									case 3:     // Show Only Age
										$birthdateStr = "(" . sportsmanagementHelper::getAge($row->birthday, $row->deathday) . ")";
										break;

									case 4:     // Show Only Year of birth
										$birthdateStr = HTMLHelper::date($row->birthday, 'Y');
										break;
									default:
										$birthdateStr = "";
										break;
								}

								/**
								 * das alter berechnen zur weiterberechnung des durchschnittsalters
								 * nicht das alter normal berechnen, sonder das alter des spielers in der saison
								 */
								$age += sportsmanagementHelper::getAge($row->birthday, $this->lastseasondate);
								$countplayer++;
							}
							else
							{
								$birthdateStr = "-";
							}

							/**
							 *
							 * deathday
							 */
							if ($row->deathday != "0000-00-00")
							{
								$birthdateStr .= ' [&dagger; ' . HTMLHelper::date($row->deathday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')) . ']';
							}

							echo $birthdateStr;
							?>
                            </td>
                            <?php
						}
						/**
                         * jetzt die statistik match_single
                         * season_team_person_id
                         * projectteam_id 
                         * project_id
                        */
                        $single_matches_home = sportsmanagementModeljlextindividualsport::getmatchsingle_rowshome($row->project_id , $row->projectteam_id , $row->season_team_person_id, 'SINGLE', 'HOME');                            
                        $single_matches_away = sportsmanagementModeljlextindividualsport::getmatchsingle_rowshome($row->project_id , $row->projectteam_id , $row->season_team_person_id, 'SINGLE', 'AWAY');
if ( Factory::getConfig()->get('debug') )
{
echo __METHOD__ . ' ' . ' ' . __LINE__ . ' ' . ' matches home <pre>'.print_r($single_matches_home,true).'</pre>';
echo __METHOD__ . ' ' . ' ' . __LINE__ . ' ' . ' matches away <pre>'.print_r($single_matches_away,true).'</pre>';
}                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
      }             
             
            }
            ?> 
</table>
<?php
}
?>
</div>
</div>