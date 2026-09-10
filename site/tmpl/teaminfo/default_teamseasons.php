<?php
/**
 * SportsManagement team seasons template for Joomla 5/6.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage teaminfo
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if ((string) ($this->config['show_teams_seasons'] ?? '0') !== '1') {
    return;
}

$input = Factory::getApplication()->input;
$cfgWhichDatabase = $input->getInt('cfg_which_database', 0);
$seasonFilter = $input->getInt('s', 0);
?>
<table class="fixtures">
    <tr class="sectiontableheader">
        <td><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON_TITLE'); ?></td>
    </tr>
</table>

<?php foreach ($this->seasons as $season) : ?>
    <?php
    if (empty($season->projectname)) {
        continue;
    }

    $panelId = 'team-season-' . (int) $this->team->id . '-' . (int) $season->projectid;
    ?>
    <table class="fixtures">
        <tr>
            <td>
                <button
                    type="button"
                    class="btn btn-link p-0"
                    data-team-season-toggle
                    aria-controls="<?php echo htmlspecialchars($panelId, ENT_QUOTES, 'UTF-8'); ?>"
                    aria-expanded="false"
                >
                    <?php echo htmlspecialchars((string) $season->projectname, ENT_QUOTES, 'UTF-8'); ?>
                </button>
            </td>
        </tr>
    </table>

    <div id="<?php echo htmlspecialchars($panelId, ENT_QUOTES, 'UTF-8'); ?>" class="text-center" hidden>
        <?php if (!empty($this->config['show_teams_logos'])) : ?>
            <?php
            $picture = (string) ($season->picture ?? '');

            if ($picture === '' || str_contains($picture, '/com_sportsmanagement/images/placeholders/placeholder_450.png')) {
                $picture = sportsmanagementHelper::getDefaultPlaceholder('team');
            }

            $pictureDescr = Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE')
                . ' ' . $this->team->name . ' (' . $season->projectname . ')';

            echo HTMLHelper::image(
                $picture,
                $pictureDescr,
                ['title' => $pictureDescr]
            );
            ?>
        <?php endif; ?>

        <br>
        <?php
        $routeparameter = [
            'cfg_which_database' => $cfgWhichDatabase,
            's' => $seasonFilter,
            'p' => $season->project_slug,
            'tid' => $season->team_slug,
            'ptid' => 0,
        ];
        echo HTMLHelper::link(
            SiteRouteHelper::view('roster', $routeparameter),
            Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON_PLAYERS')
        );
        ?>

        <br>
        <?php
        $routeparameter = [
            'cfg_which_database' => $cfgWhichDatabase,
            's' => $seasonFilter,
            'p' => $season->project_slug,
            'r' => 0,
            'division' => 0,
            'mode' => 0,
            'order' => 0,
            'layout' => 0,
        ];
        echo HTMLHelper::link(
            SiteRouteHelper::view('results', $routeparameter),
            Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON_RESULTS')
        );
        ?>

        <br>
        <?php
        $routeparameter = [
            'cfg_which_database' => $cfgWhichDatabase,
            's' => $seasonFilter,
            'p' => $season->project_slug,
            'type' => 0,
            'r' => 0,
            'from' => 0,
            'to' => 0,
            'division' => 0,
        ];
        echo HTMLHelper::link(
            SiteRouteHelper::view('ranking', $routeparameter),
            Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON_TABLES')
        );
        ?>
        <br>
    </div>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-team-season-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const panelId = button.getAttribute('aria-controls');
            const panel = panelId ? document.getElementById(panelId) : null;

            if (!panel) {
                return;
            }

            const willOpen = panel.hidden;
            panel.hidden = !willOpen;
            button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });
});
</script>
