<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage playground
 * @file       default_teams.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$this->notes = [Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_CLUB_TEAMS')];
echo $this->loadTemplate('jsm_notes');
?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="playground_teams">
    <?php foreach ($this->teams as $value) : ?>
        <?php
        $projectName = (string) ($value->project ?? '');
        $teamInfo = $value->teaminfo[0][0] ?? null;

        if (!$teamInfo) {
            continue;
        }
        ?>

        <?php foreach ((array) ($value->project_team ?? []) as $team) : ?>
            <?php
            $link = SiteRouteHelper::view('teaminfo', [
                'cfg_which_database' => $this->input->getInt('cfg_which_database', 0),
                's' => $this->input->getInt('s', 0),
                'p' => (string) ($team->project_slug ?? ''),
                'tid' => (string) ($teamInfo->team_slug ?? ''),
                'ptid' => 0,
            ]);
            $teamName = $escape($teamInfo->name ?? '');
            $shortName = trim((string) ($teamInfo->short_name ?? ''));
            ?>
            <h4>
                <?php echo $escape($projectName); ?> - <?php echo HTMLHelper::link($link, $teamName); ?><?php echo $shortName !== '' ? ' (' . $escape($shortName) . ')' : ''; ?>
            </h4>
            <div class="clubteaminfo">
                <?php
                $description = (string) ($teamInfo->notes ?? '');

                if ($description !== '') {
                    echo Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_TEAMINFO')
                        . ' ' . HTMLHelper::_('content.prepare', $description);
                }
                ?>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
