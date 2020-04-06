<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_teams.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @subpackage playground
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

?>

<h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_CLUB_TEAMS'); ?></h2>
<!-- Now show teams of this club -->
<div class="<?php echo $this->divclassrow;?> table-responsive" id="playground_teams">
    <?php foreach ((array)$this->teams AS $key => $value): ?>  
    <?php $projectname = $value->project; ?>
    <?php foreach ($value->project_team AS $team):
        $teaminfo = $value->teaminfo[0][0];
            $routeparameter = array();
        $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
        $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
        $routeparameter['p'] = $team->project_slug;
        $routeparameter['tid'] = $teaminfo->team_slug;
        $routeparameter['ptid'] = 0;
        $link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);
    ?>
            <h4><?php echo $projectname . " - " . HTMLHelper::link($link, $teaminfo->name) . ($teaminfo->short_name ? " (" . $teaminfo->short_name . ")" : ''); ?></h4>
            <div class="clubteaminfo">
            <?php
            $description = $teaminfo->notes;
            echo (!empty($description) ? Text :: _('COM_SPORTSMANAGEMENT_PLAYGROUND_TEAMINFO') . " " . HTMLHelper::_('content.prepare', $description) : ''); ?>
            </div>
    <?php endforeach; ?>
    <?php endforeach; ?>
</div>
