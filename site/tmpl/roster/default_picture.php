<?php
/**
 * Native Joomla 5/6 roster picture layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Joomla\CMS\Component\ComponentHelper;

$componentParams = ComponentHelper::getParams('com_sportsmanagement');
$teamPlaceholder = trim((string) $componentParams->get('ph_team', ''));
$modalMode = (int) ($this->overallconfig['use_jquery_modal'] ?? 0);
$teamName = (string) ($this->team->name ?? '');
?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="roster">
    <?php if (!empty($this->config['show_team_logo'])) : ?>
        <?php
        $picture = trim((string) ($this->projectteam->picture ?? ''));

        if ($picture === '' || ($teamPlaceholder !== '' && $picture === $teamPlaceholder)) {
            $picture = trim((string) ($this->team->picture ?? ''));
        }
        ?>
        <table class="table" id="tableteampicture" width="100%">
            <tr>
                <td>
                    <?php if ($picture !== '') : ?>
                        <?php
                        echo ModalImageHelper::render(
                            'roster' . $teamName,
                            $picture,
                            $teamName,
                            (int) ($this->config['team_picture_height'] ?? 20),
                            '',
                            $this->modalwidth,
                            $this->modalheight,
                            $modalMode
                        );
                        ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $clubLogo = trim((string) ($this->team->logo_big ?? ''));
                    if ($clubLogo !== '') {
                        echo ModalImageHelper::render(
                            'rosterclub' . $teamName,
                            $clubLogo,
                            $teamName,
                            (int) ($this->config['club_picture_height'] ?? 20),
                            '',
                            $this->modalwidth,
                            $this->modalheight,
                            $modalMode
                        );
                    }
                    ?>
                </td>
            </tr>
        </table>
    <?php endif; ?>
</div>
