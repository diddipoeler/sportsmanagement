<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_roster.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage matchreport
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

HTMLHelper::_('behavior.modal');


?>
<!-- START: game roster -->
<div class="<?php echo $this->divclassrow;?> table-responsive" id="matchreport">
<!-- Show Match players -->
<?php
if (!empty($this->matchplayerpositions)) {
?>

<h2><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_LINE-UP'); ?></h2>        
<table class="table ">
<?php
foreach ($this->matchplayerpositions as $pos)
        {
    $personCount=0;
    foreach ($this->matchplayers as $player)
    {
        //if ($player->pposid == $pos->pposid)
        if ($player->position_id == $pos->position_id) {
            $personCount++;
        }
    }

    if ($personCount > 0) {
        ?>
      <tr><td colspan="2" class="positionid"><?php echo Text::_($pos->name); ?></td></tr>
     <tr>
      <!-- list of home-team -->
      <td class="list">
       <div style="text-align: right; ">
        <ul style="list-style-type: none;">
                                <?php
                                foreach ($this->matchplayers as $player)
                                {
                                    if ($player->trikot_number != 0 ) {
                                          $player->jerseynumber = $player->trikot_number;
                                    }

                                    if ($player->position_id == $pos->position_id && $player->ptid == $this->match->projectteam1_id ) {
                                                ?>
                                                <li <?php echo ($this->config['show_player_picture'] == 2 ? 'class="list_pictureonly_left"' : 'class="list"') ?>>
                                    <?php
                                    /**
* ist der spieler ein spielführer ?
*/                  
                                    if ($player->captain != 0 ) {
                                        echo ' '.'&copy;';                  
                                    }                                            

                                    $routeparameter = array();
                                    $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                                    $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                                    $routeparameter['p'] = $this->project->slug;
                                    $routeparameter['tid'] = $player->team_slug;
                                    $routeparameter['pid'] = $player->person_slug;
                                    $player_link = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);
                                    if ($this->config['show_player_profile_name_trikotnumber'] ) {
                                        $prefix = $player->jerseynumber ? $player->jerseynumber."." : null;
                                    }
                                    else
                                    {
                                        $prefix = null;    
                                    }
                                    $match_player = sportsmanagementHelper::formatName($prefix, $player->firstname, $player->nickname, $player->lastname, $this->config["name_format"]);
                                    $isFavTeam = in_array($player->team_id, explode(",", $this->project->fav_team));

                                    if (($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)) ) {
                                        if ($this->config['show_player_picture'] == 2) {
                                            echo '';
                                        } else {
                                            
                                            if ($this->config['show_player_profile_link_alignment'] == 0 ) {
                                                echo HTMLHelper::link($player_link, $match_player.HTMLHelper::image(Uri::root().'images/com_sportsmanagement/database/teamplayers/shirt.php?text='.$player->jerseynumber, $player->jerseynumber, array('title'=> $player->jerseynumber)));
                                            }
                                        }
                                    } else {
                                        if ($this->config['show_player_picture'] == 2) {
                                            echo '';
                                        } else {
                                            echo $match_player;
                                        }
                                    }

                                    if (($this->config['show_player_picture'] == 1) || ($this->config['show_player_picture'] == 2)) {
                                        $imgTitle=($this->config['show_player_profile_link'] == 1) ? Text::sprintf('COM_SPORTSMANAGEMENT_MATCHREPORT_PIC', $match_player) : $match_player;
                                        $picture=$player->picture;
                                        if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player") ) || !curl_init($picture) ) {
                                            $picture = $player->ppic;
                                        }
                                        if (!curl_init($picture) ) {
                                            $picture = sportsmanagementHelper::getDefaultPlaceholder("player");
                                        }
                                        if (($this->config['show_player_picture'] == 2) && ($this->config['show_player_profile_link'] == 1) ) {
                                            echo sportsmanagementHelperHtml::getBootstrapModalImage('matchplayer'.$player->person_id, $picture, $imgTitle, $this->config['player_picture_width']);
                                            ?>
                                                
                                            <?PHP
                                        } 
                                        else 
                                        {
                                            echo sportsmanagementHelperHtml::getBootstrapModalImage('matchplayer'.$player->person_id, $picture, $imgTitle, $this->config['player_picture_width']);

                                            if ($this->config['show_player_profile_link_alignment'] == 1 ) {
                                                echo '<br>';
                                                echo HTMLHelper::link($player_link, $match_player.HTMLHelper::image(Uri::root().'images/com_sportsmanagement/database/teamplayers/shirt.php?text='.$player->jerseynumber, $player->jerseynumber, array('title'=> $player->jerseynumber)));
                                            }
                                            echo '&nbsp;';
                                        }

                                    }
                                    ?>
                                    </li>
                                    <?php
                                    }
                                }
    ?>
  </ul>
  </div>
  </td>
                    <!-- list of guest-team -->
                    <td class="list">
                        <div style="text-align: left;">
                            <ul style="list-style-type: none;">
                                <?php
                                foreach ($this->matchplayers as $player)
                                {
                                
                                    if ($player->trikot_number != 0 ) {
                                          $player->jerseynumber = $player->trikot_number;
                                    }

                                    if ($player->position_id == $pos->position_id && $player->ptid == $this->match->projectteam2_id ) {
                                    ?>
                                        <li <?php echo ($this->config['show_player_picture'] == 2 ? 'class="list_pictureonly_right"' : 'class="list"') ?>>
            <?php
                                            
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
            $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
            $routeparameter['p'] = $this->project->slug;
            $routeparameter['tid'] = $player->team_slug;
            $routeparameter['pid'] = $player->person_slug;
            $player_link = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);
            if ($this->config['show_player_profile_name_trikotnumber'] ) {
                        $prefix = $player->jerseynumber ? $player->jerseynumber."." : null;
            }
            else
            {
                        $prefix = null;    
            }
            $match_player=sportsmanagementHelper::formatName($prefix, $player->firstname, $player->nickname, $player->lastname, $this->config["name_format"]);
            $isFavTeam = in_array($player->team_id, explode(",", $this->project->fav_team));

            if (($this->config['show_player_picture'] == 1) || ($this->config['show_player_picture'] == 2)) {
                        $imgTitle=($this->config['show_player_profile_link'] == 1) ? Text::sprintf('COM_SPORTSMANAGEMENT_MATCHREPORT_PIC', $match_player) : $match_player;
                        $picture=$player->picture;
                if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player") ) || !curl_init($picture) ) {
                    $picture = $player->ppic;
                }
                if (!curl_init($picture) ) {
                    $picture = sportsmanagementHelper::getDefaultPlaceholder("player");
                }
                if (($this->config['show_player_picture'] == 2) && ($this->config['show_player_profile_link'] == 1) ) {
                    echo HTMLHelper::link($player_link, HTMLHelper::image($picture, $imgTitle, array('title' => $imgTitle,'width' => $this->config['player_picture_width'] )));                                                                
                    if ($this->config['show_player_profile_link_alignment'] == 1 ) {
                        echo '<br>';
                        echo HTMLHelper::link($player_link, HTMLHelper::image(Uri::root().'images/com_sportsmanagement/database/teamplayers/shirt.php?text='.$player->jerseynumber, $player->jerseynumber, array('title'=> $player->jerseynumber)).$match_player);
                    }
                }
                else 
                        {
                    echo sportsmanagementHelperHtml::getBootstrapModalImage('matchplayer'.$player->person_id, $picture, $imgTitle, $this->config['player_picture_width']);                         
                    ?>
                    <?PHP

                    ?>
                                                     
                    <?PHP
                    if ($this->config['show_player_profile_link_alignment'] == 1 ) {
                        echo '<br>';
                        echo HTMLHelper::link($player_link, HTMLHelper::image(Uri::root().'images/com_sportsmanagement/database/teamplayers/shirt.php?text='.$player->jerseynumber, $player->jerseynumber, array('title'=> $player->jerseynumber)).$match_player);
                    }
                    echo '&nbsp;';
                }
            }

            if (($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)) ) {
                if ($this->config['show_player_picture'] == 2) {
                    echo '';
                } else {

                    if ($this->config['show_player_profile_link_alignment'] == 0 ) {
                        echo HTMLHelper::link($player_link, HTMLHelper::image(Uri::root().'images/com_sportsmanagement/database/teamplayers/shirt.php?text='.$player->jerseynumber, $player->jerseynumber, array('title'=> $player->jerseynumber)).$match_player);
                    }
                          
                }
            } else {
                if ($this->config['show_player_picture'] == 2) {
                    echo '';
                } else {

                    echo $match_player;
                }
            }
                                    /**
* ist der spieler ein spielführer ?
*/                  
            if ($player->captain != 0 ) {
                        echo ' '.'&copy;';                  
            } 
                                    ?>
                                        </li>
                                    <?php
                                    }
                                }
                                ?>
        </ul>
        </div>
       </td>
      </tr>
        <?php
    }
}
    ?>
    </table>
    <?php
}
?>
<!-- END of Match players -->
<br />
</div>
<!-- END: game roster -->
