<?php
/** SportsManagement clubs list template for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$componentParams = ComponentHelper::getParams('com_sportsmanagement');
$pictureServer = \defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')
    ? (string) COM_SPORTSMANAGEMENT_PICTURE_SERVER
    : Uri::root();
$useExternalPictureServer = $pictureServer !== ''
    && rtrim($pictureServer, '/') !== rtrim(Uri::root(), '/');
$pictureUrl = static function (string $picture) use ($pictureServer): string {
    $picture = trim($picture);

    if ($picture === '') {
        return '';
    }

    return preg_match('#^https?://#i', $picture)
        ? $picture
        : rtrim($pictureServer, '/') . '/' . ltrim($picture, '/');
};
$resolvePicture = static function (string $picture, string $placeholderKey) use ($componentParams, $useExternalPictureServer): string {
    $picture = trim($picture);

    if (
        $picture === ''
        || (
            !$useExternalPictureServer
            && !preg_match('#^https?://#i', $picture)
            && !is_file(JPATH_SITE . '/' . ltrim($picture, '/'))
        )
    ) {
        return trim((string) $componentParams->get($placeholderKey, ''));
    }

    return $picture;
};
?>
<div class="<?php echo $this->escape($this->divclassrow); ?> table-responsive" id="clubs-list">
    <table class="<?php echo $this->escape((string) ($this->config['table_class'] ?? 'table')); ?>">
        <thead>
        <tr>
            <?php if (!empty($this->config['show_small_logo'])) : ?>
                <th class="club_logo"><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBS_LOGO'); ?></th>
            <?php endif; ?>
            <?php if (!empty($this->config['show_medium_logo'])) : ?>
                <th class="club_logo"><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBS_LOGO'); ?></th>
            <?php endif; ?>
            <?php if (!empty($this->config['show_big_logo'])) : ?>
                <th class="club_logo"><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBS_LOGO'); ?></th>
            <?php endif; ?>
            <th class="club_name"><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBS_CLUBNAME'); ?></th>
            <?php if (!empty($this->config['show_club_teams'])) : ?>
                <th class="club_teams"><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBS_TEAMS'); ?></th>
            <?php endif; ?>
            <?php if (!empty($this->config['show_club_internetadress_picture'])) : ?>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMS_HOMEPAGE_PICTURE'); ?></th>
            <?php endif; ?>
            <?php if (!empty($this->config['show_address'])) : ?>
                <th class="club_address"><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBS_ADDRESS'); ?></th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->clubs as $club) : ?>
            <?php
            $clubInfoLink = SiteRouteHelper::view('clubinfo', [
                'cfg_which_database' => $this->databaseSelector,
                's' => $this->input->getInt('s', 0),
                'p' => (string) ($this->project->slug ?? $this->project->id ?? ''),
                'cid' => (string) ($club->club_slug ?? $club->id ?? ''),
            ]);
            $title = Text::sprintf('COM_SPORTSMANAGEMENT_CLUBS_TITLE2', (string) $club->name);

            $smallPicture = $resolvePicture((string) ($club->logo_small ?? ''), 'ph_logo_small');
            $mediumPicture = $resolvePicture((string) ($club->logo_middle ?? ''), 'ph_logo_medium');
            $bigPicture = $resolvePicture((string) ($club->logo_big ?? ''), 'ph_logo_big');

            $smallLogo = HTMLHelper::image($pictureUrl($smallPicture), $title, ['height' => 21, 'title' => $title]);
            $mediumLogo = HTMLHelper::image($pictureUrl($mediumPicture), $title, ['height' => 50, 'title' => $title]);
            $bigLogo = HTMLHelper::image($pictureUrl($bigPicture), $title, ['height' => 150, 'title' => $title]);
            ?>
            <tr>
                <?php if (!empty($this->config['show_small_logo'])) : ?>
                    <td><?php echo HTMLHelper::link($clubInfoLink, $smallLogo); ?></td>
                <?php endif; ?>
                <?php if (!empty($this->config['show_medium_logo'])) : ?>
                    <td><?php echo HTMLHelper::link($clubInfoLink, $mediumLogo); ?></td>
                <?php endif; ?>
                <?php if (!empty($this->config['show_big_logo'])) : ?>
                    <td><?php echo HTMLHelper::link($clubInfoLink, $bigLogo); ?></td>
                <?php endif; ?>

                <td>
                    <?php
                    if (!empty($club->website)) {
                        echo HTMLHelper::link(
                            (string) $club->website,
                            (string) $club->name,
                            ['target' => '_blank', 'rel' => 'noopener']
                        );
                    } else {
                        echo $this->escape((string) $club->name);
                    }
                    ?>
                </td>

                <?php if (!empty($this->config['show_club_teams'])) : ?>
                    <td>
                        <?php foreach ((array) ($club->teams ?? []) as $team) : ?>
                            <?php
                            $pictureProperty = (string) ($this->config['show_picture'] ?? 'picture');
                            $teamPicture = $resolvePicture((string) ($team->{$pictureProperty} ?? ''), 'ph_team');
                            echo ModalImageHelper::render(
                                'teaminfo' . (int) $team->id,
                                $pictureUrl($teamPicture),
                                (string) $team->name,
                                (int) ($this->config['team_picture_width'] ?? 20),
                                '',
                                $this->modalwidth,
                                $this->modalheight,
                                (int) ($this->overallconfig['use_jquery_modal'] ?? 0)
                            );
                            echo '<br>';

                            $teamInfoLink = SiteRouteHelper::view('teaminfo', [
                                'cfg_which_database' => $this->databaseSelector,
                                's' => $this->input->getInt('s', 0),
                                'p' => (string) ($this->project->slug ?? $this->project->id ?? ''),
                                'tid' => (string) ($team->team_slug ?? $team->id ?? ''),
                                'ptid' => 0,
                            ]);
                            echo HTMLHelper::link($teamInfoLink, $this->escape((string) $team->name));
                            echo '<br>';
                            ?>
                        <?php endforeach; ?>
                    </td>
                <?php endif; ?>

                <?php if (!empty($this->config['show_club_internetadress_picture'])) : ?>
                    <td>
                        <?php
                        $website = trim((string) ($club->website ?? ''));
                        if ($website !== '') {
                            switch ((string) ($this->config['which_internetadress_picture_provider'] ?? '')) {
                                case 'thumbshots':
                                    echo '<img src="http://www.thumbshots.de/cgi-bin/show.cgi?url='
                                        . rawurlencode($website) . '" alt="">';
                                    break;
                                case 'thumbsniper':
                                    echo '<img src="http://api.thumbsniper.com/api_free.php?size=13&amp;effect='
                                        . rawurlencode((string) ($this->config['internetadress_picture_thumbsniper_preview'] ?? ''))
                                        . '&amp;url=' . rawurlencode($website) . '" alt="">';
                                    break;
                            }
                        }
                        ?>
                    </td>
                <?php endif; ?>

                <?php if (!empty($this->config['show_address'])) : ?>
                    <td>
                        <?php
                        echo CountryPresentationHelper::address(
                            (string) ($club->name ?? ''),
                            (string) ($club->address ?? ''),
                            (string) ($club->state ?? ''),
                            (string) ($club->zipcode ?? ''),
                            (string) ($club->location ?? ''),
                            (string) ($club->country ?? ''),
                            'COM_SPORTSMANAGEMENT_CLUBS_ADDRESS_FORM'
                        );
                        ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
