<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_teams.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$imageAttributes = ['width' => '30'];
$placeholder = (string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_team', '');
$modalMode = (int) ($this->overallconfig['use_jquery_modal'] ?? 0);
$this->notes = [Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_TEAMS')];
echo $this->loadTemplate('jsm_notes');
?>
<div class="<?php echo $this->divclassrow; ?>" id="default_teams" itemscope itemtype="https://schema.org/SportsTeam">
    <?php foreach ($this->teams as $team) : ?>
        <?php
        if (empty($team->team_name) || !property_exists($team, 'ptid')) {
            continue;
        }

        $link = SiteRouteHelper::view('teaminfo', [
            'cfg_which_database' => $this->input->getInt('cfg_which_database', 0),
            's' => $this->input->getInt('s', 0),
            'p' => (string) ($team->pid ?? ''),
            'tid' => (string) ($team->team_slug ?? ''),
            'ptid' => (int) $team->ptid,
        ]);
        $teamName = $escape($team->team_name);
        $shortcut = trim((string) ($team->team_shortcut ?? ''));
        $label = '<span itemprop="name">' . $teamName
            . (!empty($this->config['show_teams_shortcut_of_club']) && $shortcut !== ''
                ? ' (' . $escape($shortcut) . ')'
                : '')
            . '</span>';

        if (!empty($this->config['show_teams_trikot_of_club']) && !empty($team->trikot_home)) {
            $label = HTMLHelper::image((string) $team->trikot_home, (string) $team->team_name, $imageAttributes) . $label;
        }
        ?>
        <div class="row">
            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                <?php echo HTMLHelper::link($link, $label); ?>&nbsp;
            </div>

            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                <?php
                if (!empty($team->team_description) && !empty($this->config['show_teams_description_of_club'])) {
                    echo HTMLHelper::_('content.prepare', (string) $team->team_description);
                } else {
                    echo '&nbsp;';
                }
                ?>
            </div>

            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                <?php if (!empty($this->config['show_teams_picture'])) : ?>
                    <?php
                    $picture = trim((string) ($team->project_team_picture ?? '')) ?: $placeholder;

                    if ($picture !== '') {
                        $pictureUrl = preg_match('#^https?://#i', $picture)
                            ? $picture
                            : rtrim((string) COM_SPORTSMANAGEMENT_PICTURE_SERVER, '/') . '/' . ltrim($picture, '/');
                        echo ModalImageHelper::render(
                            'clubteam' . (int) ($team->id ?? 0),
                            $pictureUrl,
                            (string) $team->team_name,
                            (int) ($this->config['team_picture_width'] ?? 50),
                            '',
                            $this->modalwidth,
                            $this->modalheight,
                            $modalMode,
                            'itemprop',
                            'image'
                        );
                    }
                    ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
