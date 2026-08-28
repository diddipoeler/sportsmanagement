<?php
/**
 * Native Joomla 5/6 rivals table.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\TeamPresentationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$separator = (string) ($this->overallconfig['seperator'] ?? '-');
$showPicture = (string) ($this->config['show_picture'] ?? '');
$pictureWidth = max(1, (int) ($this->config['picture_width'] ?? 20));
$databaseSelector = $this->input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
$seasonId = max(0, $this->input->getInt('s', 0));
$favouriteTeams = array_filter(
    array_map('intval', explode(',', (string) ($this->project->fav_team ?? ''))),
    static fn (int $id): bool => $id > 0
);
$componentParams = ComponentHelper::getParams('com_sportsmanagement');
$pictureBase = \defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')
    ? (string) COM_SPORTSMANAGEMENT_PICTURE_SERVER
    : Uri::root();
$renderTeamPicture = static function (object $team, string $property) use (
    $componentParams,
    $pictureBase,
    $pictureWidth,
    $escape
): string {
    $picture = trim((string) ($team->{$property} ?? ''));
    $isRemote = preg_match('#^https?://#i', $picture) === 1;

    if (!$isRemote && ($picture === '' || !is_file(JPATH_SITE . '/' . ltrim(str_replace('\\', '/', $picture), '/')))) {
        $picture = trim((string) $componentParams->get('ph_team', ''));
        $isRemote = preg_match('#^https?://#i', $picture) === 1;
    }

    if ($picture === '') {
        return '';
    }

    $url = $isRemote
        ? $picture
        : rtrim($pictureBase, '/') . '/' . ltrim($picture, '/');
    $name = (string) ($team->name ?? '');

    return '<a href="' . $escape($url) . '" target="_blank" rel="noopener" title="' . $escape($name) . '">'
        . '<img src="' . $escape($url) . '" alt="' . $escape($name) . '" title="' . $escape($name)
        . '" style="width:' . $pictureWidth . 'px;height:auto" />'
        . '</a>';
};
?>
<?php if ($this->pagetitle !== '') : ?>
    <div class="mb-2"><?php echo $escape($this->pagetitle); ?></div>
<?php endif; ?>
<div class="<?php echo $escape($this->divclassrow); ?> table-responsive" id="rivals-table">
    <?php if ($this->opos) : ?>
        <table class="<?php echo $escape($this->config['table_class'] ?? 'table'); ?>">
            <thead>
            <tr class="sectiontableheader">
                <th class="name_row"></th>
                <th class="name_row"><?php echo Text::_('COM_SPORTSMANAGEMENT_RIVALS_RIVAL'); ?></th>
                <th class="match_row"><?php echo Text::_('COM_SPORTSMANAGEMENT_RIVALS_MATCHES'); ?></th>
                <th class="win_row"><?php echo Text::_('COM_SPORTSMANAGEMENT_RIVALS_WIN'); ?></th>
                <th class="tie_row"><?php echo Text::_('COM_SPORTSMANAGEMENT_RIVALS_DRAW'); ?></th>
                <th class="los_row"><?php echo Text::_('COM_SPORTSMANAGEMENT_RIVALS_LOS'); ?></th>
                <th class="goals_row"><?php echo Text::_('COM_SPORTSMANAGEMENT_RIVALS_TOTAL_GOALS'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $row = 0;

            foreach ($this->opos as $opponent) :
                if (empty($opponent['name'])) {
                    continue;
                }

                $team = ArrayHelper::toObject($opponent);
                if (empty($team->id)) {
                    continue;
                }

                $countryFlag = CountryPresentationHelper::flag((string) ($team->country_flag ?? ''));
                ?>
                <tr class="sectiontableentry<?php echo ($row % 2) + 1; ?>">
                    <td>
                        <?php
                        switch ($showPicture) {
                            case 'logo_small':
                            case 'logo_middle':
                            case 'logo_big':
                                echo $this->renderClubIcon($team, $showPicture);
                                break;

                            case 'projectteam_picture':
                            case 'team_picture':
                                echo $renderTeamPicture($team, $showPicture);
                                break;

                            case 'country_flag':
                                echo $countryFlag;
                                break;

                            case 'logo_small_country_flag':
                                echo $this->renderClubIcon($team, 'logo_small') . ' ' . $countryFlag;
                                break;

                            case 'country_flag_logo_small':
                                echo $countryFlag . ' ' . $this->renderClubIcon($team, 'logo_small');
                                break;

                            case 'logo_middle_country_flag':
                                echo $this->renderClubIcon($team, 'logo_middle') . ' ' . $countryFlag;
                                break;

                            case 'country_flag_logo_middle':
                                echo $countryFlag . ' ' . $this->renderClubIcon($team, 'logo_middle');
                                break;

                            case 'logo_big_country_flag':
                                echo $this->renderClubIcon($team, 'logo_big') . ' ' . $countryFlag;
                                break;

                            case 'country_flag_logo_big':
                                echo $countryFlag . ' ' . $this->renderClubIcon($team, 'logo_big');
                                break;
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $isFavouriteTeam = in_array((int) $team->id, $favouriteTeams, true);
                        echo TeamPresentationHelper::formatName(
                            $team,
                            'tr' . $row,
                            $this->config,
                            $isFavouriteTeam,
                            $this->project,
                            $databaseSelector,
                            $seasonId
                        );
                        ?>
                    </td>
                    <td class="match_row"><?php echo (int) ($opponent['match'] ?? 0); ?></td>
                    <td class="win_row"><?php echo (int) ($opponent['win'] ?? 0); ?></td>
                    <td class="tie_row"><?php echo (int) ($opponent['tie'] ?? 0); ?></td>
                    <td class="los_row"><?php echo (int) ($opponent['los'] ?? 0); ?></td>
                    <td class="goals_row">
                        <?php echo $escape($opponent['g_for'] ?? 0); ?>
                        <?php echo $escape($separator); ?>
                        <?php echo $escape($opponent['g_aga'] ?? 0); ?>
                    </td>
                </tr>
                <?php
                $row++;
            endforeach;
            ?>
            </tbody>
        </table>
    <?php else : ?>
        <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_NO_MATCHES'); ?>
    <?php endif; ?>
</div>
