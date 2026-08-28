<?php
/** SportsManagement referees list for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonAgeHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (!$this->rows) {
    return;
}

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$pictureServer = \defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')
    ? (string) COM_SPORTSMANAGEMENT_PICTURE_SERVER
    : '';
$pictureUrl = static function (string $picture) use ($pictureServer): string {
    $picture = trim($picture);

    if ($picture === '') {
        return '';
    }

    return preg_match('#^https?://#i', $picture)
        ? $picture
        : rtrim($pictureServer, '/') . '/' . ltrim($picture, '/');
};

$databaseSelector = $this->databaseSelector;
$seasonId = $this->input->getInt('s', 0);
$projectSlug = (string) ($this->project->slug ?? $this->project->id ?? '');
$projectTimezone = trim((string) ($this->project->timezone ?? '')) ?: 'UTC';
$showBirthday = (int) ($this->config['show_birthday'] ?? 0);
$showGamesCount = !empty($this->config['show_games_count']);
$showIcon = !empty($this->config['show_icon']);
$modalMode = (int) ($this->overallconfig['use_jquery_modal'] ?? 0);
$position = null;
?>
<div class="<?php echo $this->escape($this->divclassrow); ?> table-responsive" id="referees">
    <table class="<?php echo $this->escape((string) ($this->config['table_class'] ?? 'table')); ?>">
        <tbody>
        <?php foreach ($this->rows as $row) : ?>
            <?php
            if ($position !== (string) $row->position) {
                $position = (string) $row->position;
                $colspan = 4 + ($showBirthday > 0 ? 1 : 0);
                ?>
                <tr class="sectiontableheader">
                    <th colspan="<?php echo $colspan; ?>">
                        <?php echo Text::_($position); ?>
                    </th>
                    <?php if ($showGamesCount) : ?>
                        <th class="text-center">
                            <?php
                            $imageTitle = Text::_('COM_SPORTSMANAGEMENT_REFEREES_GAMES');
                            echo HTMLHelper::image(
                                'images/com_sportsmanagement/database/events/'
                                . (string) ($this->project->fs_sport_type_name ?? '')
                                . '/refereed.png',
                                $imageTitle,
                                ['title' => $imageTitle, 'height' => 20]
                            );
                            ?>
                        </th>
                    <?php endif; ?>
                </tr>
                <?php
            }

            $refereeName = PersonNameFormatter::format(
                null,
                (string) ($row->firstname ?? ''),
                (string) ($row->nickname ?? ''),
                (string) ($row->lastname ?? ''),
                $this->config['name_format'] ?? 0
            );
            $picture = trim((string) ($row->picture ?? ''));
            if ($picture === '') {
                $picture = PersonImageHelper::placeholder();
            }
            ?>
            <tr>
                <td class="text-center" style="width:30px">&nbsp;</td>
                <td class="text-center nowrap" style="width:40px">
                    <?php
                    if ($showIcon && $picture !== '') {
                        echo ModalImageHelper::render(
                            'referee' . (int) ($row->id ?? 0),
                            $pictureUrl($picture),
                            $refereeName,
                            (int) ($this->config['referee_picture_width'] ?? 20),
                            '',
                            $this->modalwidth,
                            $this->modalheight,
                            $modalMode,
                            'itemprop',
                            'image'
                        );
                    }
                    ?>
                </td>
                <td style="width:20%">
                    <?php
                    if (!empty($this->config['link_name'])) {
                        $link = SiteRouteHelper::view('referee', [
                            'cfg_which_database' => $databaseSelector,
                            's' => $seasonId,
                            'p' => $projectSlug,
                            'pid' => (string) ($row->slug ?? $row->id ?? ''),
                        ]);
                        echo HTMLHelper::link($link, '<i>' . $escape($refereeName) . '</i>');
                    } else {
                        echo '<i>' . $escape($refereeName) . '</i>';
                    }
                    ?>
                </td>
                <td class="text-center nowrap" style="width:16px">
                    <?php echo CountryPresentationHelper::flag((string) ($row->country ?? '')); ?>
                </td>

                <?php if ($showBirthday > 0) : ?>
                    <td class="nowrap text-start" style="width:10%">
                        <?php
                        $birthday = (string) ($row->birthday ?? '0000-00-00');
                        $deathday = (string) ($row->deathday ?? '0000-00-00');
                        $hasBirthday = $birthday !== '' && $birthday !== '0000-00-00';
                        $age = PersonAgeHelper::calculate($birthday, $deathday);

                        switch ($showBirthday) {
                            case 1:
                                $birthdate = $hasBirthday
                                    ? HTMLHelper::date(
                                        $birthday . ' UTC',
                                        Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'),
                                        $projectTimezone
                                    )
                                    : '-';
                                echo $escape($birthdate) . '&nbsp;(' . $escape($age) . ')';
                                break;

                            case 2:
                                echo $hasBirthday
                                    ? $escape(HTMLHelper::date(
                                        $birthday . ' UTC',
                                        Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'),
                                        $projectTimezone
                                    ))
                                    : '-';
                                break;

                            case 3:
                                echo '(' . $escape($age) . ')';
                                break;

                            case 4:
                                echo $hasBirthday
                                    ? $escape(HTMLHelper::date($birthday . ' UTC', 'Y', $projectTimezone))
                                    : '-';
                                break;
                        }
                        ?>
                    </td>
                <?php endif; ?>

                <?php if ($showGamesCount) : ?>
                    <td><?php echo (int) ($row->countGames ?? 0); ?></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<br>
