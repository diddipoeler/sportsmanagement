<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_eventsranking
 */

defined('_JEXEC') or die; 
use Joomla\CMS\HTML\HTMLHelper; 
use Joomla\CMS\Language\Text;

?>

<div class="row-fluid">

<?php
$header = ""; 
if ($params->get('show_project_name', 0)) {
    $header .= $list['project']->name;
}

$showPicture = $params->get('show_picture', 0);
$pictureHeight = $params->get('picture_height', 40);
$pictureWidth = $params->get('picture_width', 40);
$showTeam = $params->get('show_team', 1);
$showLogo = $params->get('show_logo', 0);
$teamLink = $params->get('teamlink', '');
$teamnametype = $params->get('teamnametype', 'short_name');
?>
<p class="projectname"><?php echo $header; ?></p>
<?php 
if (count($list['eventtypes']) > 0) {
?>
<table class="table">
    <tbody>
    <?php 
    foreach ($list['eventtypes'] as $eventtype)
    {
         $rankingforevent = $list['ranking'];
            ?>
        <tr class="sectiontableheader">
         <td class="eventtype"><?php echo Text::_($eventtype->name); ?></td>
        </tr>
        <tr>
         <td>
            <?php
            if (count($rankingforevent) > 0) {
            ?>
           <table class="<?php echo $params->get('table_class', '');?>">
        <thead>
         <tr class="sectiontableheader">
          <th class="rank"><?php echo Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_COL_RANK')?></th>
            <?php if ($showPicture == 1) : ?>
                            <th class="picture"><?php echo Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_COL_PICTURE');?></th>
            <?php endif; ?>
          <th class="personname"><?php echo Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_COL_NAME')?></th>
            <?php if ($showTeam == 1) : ?>
                            <th class="team"><?php echo Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_COL_TEAM');?></th>
            <?php endif; 
if ($list['project']->sport_type_name == 'COM_SPORTSMANAGEMENT_ST_DART' ) {
    $colspan = 2; 
}
else
         {
    $colspan = 1;    
}                   
                            ?>
              <th class="td_c" colspan="<?php echo $colspan;?>">
                <?php if ($params->get('show_event_icon', 1)) : ?>
                                <?php echo modSMEventsrankingHelper::getEventIcon($eventtype);?>
        <?php else: ?>
                                <?php echo Text::_($eventtype->name);?>
        <?php endif; ?>
              </th>
             </tr>
            </thead>
            <tbody>
            <?php
            $lastRank = 0;
            $k = 0;
            foreach (array_slice($rankingforevent, 0, $params->get('limit', 5)) as $item)
            {
                $team = $list['teams'][$item->tid];
                $style_class = ( $k == 0 ) ? 'style_class1' : 'style_class2';
                $class = $params->get($style_class, 0);
                ?>
                <tr class="<?php echo $class; ?>">
               <td class="rank">
                <?php
                $rank = ($item->rank == $lastRank) ? "-" : $item->rank;
                $lastRank = $item->rank;
                echo $rank;
                ?>
               </td>
                <?php
                if ($showPicture == 1) {
                    $picture = isset($item->teamplayerpic) ? $item->teamplayerpic : null;
                    if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player"))) {
                        $picture = $item->picture;
                    }
                    if (!file_exists($picture)) {
                        $picture = sportsmanagementHelper::getDefaultPlaceholder("player");
                    }
                    $name = sportsmanagementHelper::formatName(null, $item->fname, $item->nname, $item->lname, $params->get("name_format"));
                ?>
                 <td class="picture">
                <?php echo sportsmanagementHelper::getPictureThumb($picture, $name, $pictureWidth, $pictureHeight); ?>
                </td>
                <?php
                }
                ?>
               <td class="personname">
                <?php modSMEventsrankingHelper::printName($item, $team, $params, $list['project']); ?>
              </td>
            <?php
            if ($showTeam == 1) {
            ?>
             <td class="team">
            <?php
            if ($showLogo ) {
                echo modSMEventsrankingHelper::getLogo($team, $showLogo);
            }
            if ($teamLink) {
                echo HTMLHelper::link(modSMEventsrankingHelper::getTeamLink($team, $params, $list['project']), $team->$teamnametype);
            }
            else
            {
                echo $team->$teamnametype;
            }
            ?>
             </td>
            <?php
            }
            ?>
              <td class="td_c"><?php echo $item->p; ?></td>
                            <?php
                            if ($list['project']->sport_type_name == 'COM_SPORTSMANAGEMENT_ST_DART' ) {
                            ?>    
                              <td class="td_c"><?php echo $item->zaehler; ?></td>
                                <?php  
                            }
                            ?>
                 </tr>
                <?php
                $k=(1-$k);
            }
            ?>
            </tbody>
           </table>
            <?php
            }
            else
            {
            ?>
           <p class="modjlgstat"><?php echo Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_NO_ITEMS');?></p>
            <?php
            }
            ?>
         </td>
        </tr>
        <?php
        //}
    }
    ?>
    </tbody>
</table>
<?php
}
else
{
?>
<p class="modjlgstat"><?php echo Text::_("MOD_SPORTSMANAGEMENT_EVENTSRANKING_NO_EVENTS_SELECTED"); ?></p>
<?php
}
?>
<?php if ($params->get('show_full_link', 1)) :?>
<p class="fulltablelink">
    <?php //echo HTMLHelper::link(	sportsmanagementHelperRoute::getEventsRankingRoute($list['project']->slug, $params->get('divisionid',0) , $params->get('tid',0), $params->get('evid',0), $params->get('mid',0)), 
                            //Text::_('MOD_JOOMLEAGUE_EVENTSRANKING_VIEW_FULL_TABLE')); ?>
</p>
<?php endif; ?>



</div>
