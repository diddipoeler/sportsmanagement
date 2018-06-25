<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_roster_card.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.modal');
use Joomla\CMS\HTML\HTMLHelper;

?>
<link rel="stylesheet" href="<?php echo JURI::base(true); ?>/components/com_sportsmanagement/assets/css/matchreport_2.css" >

<!-- START: game roster -->
<!-- Show Match players -->
<?php
if (!empty($this->matchplayerpositions))
{
foreach ($this->matchplayerpositions as $pos)
		{
			$personCount=0;
			foreach ($this->matchplayers as $player)
			{
				//if ($player->pposid == $pos->pposid)
                if ($player->position_id == $pos->position_id)
				{
					$personCount++;
				}
			}
?>
<div class="">     
    <div class="d-flex flex-row justify-content-between p-2 mb-2 position">
        <div class="5">
            Home team
        </div>  
        <div class="positionid">
            <?php echo JText::_($pos->name); ?>
        </div>
        <div class="">
            Guest team
        </div>                
    </div>

    <div class="d-flex flex-row justify-content-center">
        <!-- list of home-team -->
        <div class="list d-flex flex-row flex-wrap justify-content-start">
        <?php
foreach ($this->matchplayers as $player)
{        
if ( $player->trikot_number != 0 )
{
$player->jerseynumber = $player->trikot_number;
}
                          
if ( $player->position_id == $pos->position_id && $player->ptid == $this->match->projectteam1_id )
{
/**
* ist der spieler ein spielführer ?
*/                  
if ( $player->captain != 0 )
{
echo ' '.'&copy;';                  
}                                            
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['tid'] = $player->team_slug;
$routeparameter['pid'] = $player->person_slug;
$player_link = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter);
$prefix = $player->jerseynumber ? $player->jerseynumber."." : null;
$match_player = sportsmanagementHelper::formatName($prefix,$player->firstname,$player->nickname,$player->lastname, $this->config["name_format"]);
$isFavTeam = in_array( $player->team_id, explode(",",$this->project->fav_team));
?>
<div class="d-flex flex-column align-self-start align-items-center p-2">
<?php
if ( ($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)) )
{
if ($this->config['show_player_picture'] == 2) {
echo '';
} else {
											
if ( $this->config['show_player_profile_link_alignment'] == 0 )
{
echo JHtml::link($player_link,$match_player.HTMLHelper::image(JURI::root().'images/com_sportsmanagement/database/teamplayers/shirt.php?text='.$player->jerseynumber,$player->jerseynumber,array('title'=> $player->jerseynumber)));
}
}
} else {
if ($this->config['show_player_picture'] == 2) {
echo '';
} else {
echo $match_player;
}
}

if (($this->config['show_player_picture'] == 1) || ($this->config['show_player_picture'] == 2))
{
$imgTitle=($this->config['show_player_profile_link'] == 1) ? JText::sprintf('COM_SPORTSMANAGEMENT_MATCHREPORT_PIC', $match_player) : $match_player;
$picture=$player->picture;
if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player") ) || !curl_init( $picture ) )
{
$picture = $player->ppic;
}
if ( !curl_init( $picture ) )
{
$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
}
if ( ($this->config['show_player_picture'] == 2) && ($this->config['show_player_profile_link'] == 1) )
{
echo sportsmanagementHelperHtml::getBootstrapModalImage('matchplayer'.$player->person_id,$picture,$imgTitle,$this->config['player_picture_width']);
?>
                                                
<?PHP
} 
else 
{
echo sportsmanagementHelperHtml::getBootstrapModalImage('matchplayer'.$player->person_id,$picture,$imgTitle,$this->config['player_picture_width']);

if ( $this->config['show_player_profile_link_alignment'] == 1 )
{
echo '<br>';
echo JHtml::link($player_link,$match_player.HTMLHelper::image(JURI::root().'images/com_sportsmanagement/database/teamplayers/shirt.php?text='.$player->jerseynumber,$player->jerseynumber,array('title'=> $player->jerseynumber)));
}
echo '&nbsp;';
}

}

?>                										
</div>


<?php
}
}        
        ?>
        
        
           
                           
        </div>
        <!-- list of line -->
        <div class="line mb-2"></div>
        <!-- list of guest-team -->
        <div class="list d-flex flex-row flex-wrap justify-content-end">
<?php
foreach ($this->matchplayers as $player)
{        

if ( $player->trikot_number != 0 )
{
$player->jerseynumber = $player->trikot_number;
}        
if ( $player->position_id == $pos->position_id && $player->ptid == $this->match->projectteam2_id )
{
/**
* ist der spieler ein spielführer ?
*/                  
if ( $player->captain != 0 )
{
echo ' '.'&copy;';                  
}                                            
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['tid'] = $player->team_slug;
$routeparameter['pid'] = $player->person_slug;
$player_link = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter);
$prefix = $player->jerseynumber ? $player->jerseynumber."." : null;
$match_player=sportsmanagementHelper::formatName($prefix,$player->firstname,$player->nickname,$player->lastname, $this->config["name_format"]);
$isFavTeam = in_array( $player->team_id, explode(",",$this->project->fav_team));


?>
<div class="d-flex flex-column align-self-start align-items-center p-2">
<?php
if (($this->config['show_player_picture'] == 1) || ($this->config['show_player_picture'] == 2))
{
$imgTitle=($this->config['show_player_profile_link'] == 1) ? JText::sprintf('COM_SPORTSMANAGEMENT_MATCHREPORT_PIC', $match_player) : $match_player;
$picture=$player->picture;
if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player") ) || !curl_init( $picture ) )
{
$picture = $player->ppic;
}
if ( !curl_init( $picture ) )
{
$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
}
if ( ($this->config['show_player_picture'] == 2) && ($this->config['show_player_profile_link'] == 1) )
{
echo JHtml::link($player_link,JHtml::image($picture, $imgTitle, array('title' => $imgTitle,'width' => $this->config['player_picture_width'] )));                                                                
if ( $this->config['show_player_profile_link_alignment'] == 1 )
{
echo '<br>';
echo JHtml::link($player_link,HTMLHelper::image(JURI::root().'images/com_sportsmanagement/database/teamplayers/shirt.php?text='.$player->jerseynumber,$player->jerseynumber,array('title'=> $player->jerseynumber)).$match_player);
}
}
else 
{
echo sportsmanagementHelperHtml::getBootstrapModalImage('matchplayer'.$player->person_id,$picture,$imgTitle,$this->config['player_picture_width']);                         
?>
<?PHP
//echo JHtml::image($picture, $imgTitle, array('title' => $imgTitle,'width' => $this->config['player_picture_width'] ));
?>
                                                     
<?PHP
if ( $this->config['show_player_profile_link_alignment'] == 1 )
{
echo '<br>';
echo JHtml::link($player_link,HTMLHelper::image(JURI::root().'images/com_sportsmanagement/database/teamplayers/shirt.php?text='.$player->jerseynumber,$player->jerseynumber,array('title'=> $player->jerseynumber)).$match_player);
}
echo '&nbsp;';
}
}

if ( ($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)) )
{
if ($this->config['show_player_picture'] == 2) {
echo '';
} else {
//echo JHtml::link($player_link,$match_player);
if ( $this->config['show_player_profile_link_alignment'] == 0 )
{
echo JHtml::link($player_link,HTMLHelper::image(JURI::root().'images/com_sportsmanagement/database/teamplayers/shirt.php?text='.$player->jerseynumber,$player->jerseynumber,array('title'=> $player->jerseynumber)).$match_player);
}
                          
}
} else {
if ($this->config['show_player_picture'] == 2) {
echo '';
} else {
//echo JHtml::link($player_link,JHtml::image(JURI::root().'images/com_sportsmanagement/database/teamplayers/shirt.php?text='.$player->jerseynumber,$player->jerseynumber,array('title'=> $player->jerseynumber)).$match_player);
echo $match_player;
}
}
/**
* ist der spieler ein spielführer ?
*/                  
if ( $player->captain != 0 )
{
echo ' '.'&copy;';                  
} 

?> 
</div>


<?php
}
}  
?>


                          
        </div>
    </div>


</div>
<?php
}
}
?>
<!-- END of Match players -->
<br />

<!-- END: game roster -->
