<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_roster_card.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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
        
if ( $player->position_id == $pos->position_id && $player->ptid == $this->match->projectteam1_id )
{
$prefix = $player->jerseynumber ? $player->jerseynumber."." : null;
$match_player = sportsmanagementHelper::formatName($prefix,$player->firstname,$player->nickname,$player->lastname, $this->config["name_format"]);
?>
 <div class="d-flex flex-column align-self-start align-items-center p-2">
                <a title="<?php echo $match_player; ?>" class="" href="#">
                    <img width="60" alt="<?php echo $match_player; ?>" src="<?php echo $player->picture; ?>">
                </a>    
                <br><a href="#"><img src="" alt="0" title="0"><?php echo $match_player; ?></a>&nbsp;										
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
        
if ( $player->position_id == $pos->position_id && $player->ptid == $this->match->projectteam2_id )
{
$prefix = $player->jerseynumber ? $player->jerseynumber."." : null;
$match_player = sportsmanagementHelper::formatName($prefix,$player->firstname,$player->nickname,$player->lastname, $this->config["name_format"]);
?>
 <div class="d-flex flex-column align-self-start align-items-center p-2">
                <a title="<?php echo $match_player; ?>" class="" href="#">
                    <img width="60" alt="<?php echo $match_player; ?>" src="<?php echo $player->picture; ?>">
                </a>    
                <br><a href="#"><img src="" alt="0" title="0"><?php echo $match_player; ?></a>&nbsp;										
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
