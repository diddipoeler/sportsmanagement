<?php
/**
 * Native Joomla 5/6 rivals table.
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

$separator = (string) ($this->overallconfig['seperator'] ?? '-');
$showPicture = (string) ($this->config['show_picture'] ?? '');
$pictureWidth = (int) ($this->config['picture_width'] ?? 20);
$favouriteTeams = array_filter(
    array_map('intval', explode(',', (string) ($this->project->fav_team ?? ''))),
    static fn (int $id): bool => $id > 0
);
?>
<?php echo $this->pagetitle; ?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="rivals-table">
    <?php if ($this->opos) : ?>
        <table class="<?php echo $this->config['table_class']; ?>">
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
                                echo \sportsmanagementHelper::getPictureThumb(
                                    (string) ($team->projectteam_picture ?? ''),
                                    (string) ($team->name ?? ''),
                                    $pictureWidth,
                                    'auto',
                                    1
                                );
                                break;

                            case 'team_picture':
                                echo \sportsmanagementHelper::getPictureThumb(
                                    (string) ($team->team_picture ?? ''),
                                    (string) ($team->name ?? ''),
                                    $pictureWidth,
                                    'auto',
                                    1
                                );
                                break;

                            case 'country_flag':
                                echo \JSMCountries::getCountryFlag((string) ($team->country_flag ?? ''));
                                break;

                            case 'logo_small_country_flag':
                                echo $this->renderClubIcon($team, 'logo_small')
                                    . ' ' . \JSMCountries::getCountryFlag((string) ($team->country_flag ?? ''));
                                break;

                            case 'country_flag_logo_small':
                                echo \JSMCountries::getCountryFlag((string) ($team->country_flag ?? ''))
                                    . ' ' . $this->renderClubIcon($team, 'logo_small');
                                break;

                            case 'logo_middle_country_flag':
                                echo $this->renderClubIcon($team, 'logo_middle')
                                    . ' ' . \JSMCountries::getCountryFlag((string) ($team->country_flag ?? ''));
                                break;

                            case 'country_flag_logo_middle':
                                echo \JSMCountries::getCountryFlag((string) ($team->country_flag ?? ''))
                                    . ' ' . $this->renderClubIcon($team, 'logo_middle');
                                break;

                            case 'logo_big_country_flag':
                                echo $this->renderClubIcon($team, 'logo_big')
                                    . ' ' . \JSMCountries::getCountryFlag((string) ($team->country_flag ?? ''));
                                break;

                            case 'country_flag_logo_big':
                                echo \JSMCountries::getCountryFlag((string) ($team->country_flag ?? ''))
                                    . ' ' . $this->renderClubIcon($team, 'logo_big');
                                break;
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $isFavouriteTeam = in_array((int) $team->id, $favouriteTeams, true);
                        echo \sportsmanagementHelper::formatTeamName(
                            $team,
                            'tr' . $row,
                            $this->config,
                            $isFavouriteTeam
                        );
                        ?>
                    </td>
                    <td class="match_row"><?php echo (int) ($opponent['match'] ?? 0); ?></td>
                    <td class="win_row"><?php echo (int) ($opponent['win'] ?? 0); ?></td>
                    <td class="tie_row"><?php echo (int) ($opponent['tie'] ?? 0); ?></td>
                    <td class="los_row"><?php echo (int) ($opponent['los'] ?? 0); ?></td>
                    <td class="goals_row">
                        <?php echo ($opponent['g_for'] ?? 0) . ' ' . $separator . ' ' . ($opponent['g_aga'] ?? 0); ?>
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
