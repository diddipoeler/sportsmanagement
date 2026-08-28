<?php
/** SportsManagement teams list template for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

usort(
    $this->teams,
    static fn (object $a, object $b): int => strnatcasecmp(
        (string) ($a->club_name ?? ''),
        (string) ($b->club_name ?? '')
    )
);

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
            && !is_file(JPATH_SITE . '/' . ltrim(str_replace('\\', '/', $picture), '/'))
        )
    ) {
        return trim((string) $componentParams->get($placeholderKey, ''));
    }

    return $picture;
};
$renderPicture = static function (string $picture, string $title, int $height) use ($pictureUrl): string {
    if ($picture === '') {
        return '';
    }

    return HTMLHelper::image(
        $pictureUrl($picture),
        $title,
        [
            'title' => $title,
            'style' => 'width:auto;height:' . max(1, $height) . 'px',
        ]
    );
};

$databaseSelector = $this->databaseSelector;
$seasonId = $this->input->getInt('s', 0);
$projectSlug = (string) ($this->project->slug ?? $this->project->id ?? '');
?>
<div class="<?php echo $this->escape($this->divclassrow); ?> table-responsive" id="teams">
    <table class="<?php echo $this->escape((string) ($this->config['table_class'] ?? 'table')); ?>">
        <thead>
        <tr>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMS_LOGO_TEAM'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMS_NAME_TEAM'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMS_NAME_CLUB'); ?></th>
            <?php if (!empty($this->config['show_medium_logo'])) : ?>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMS_LOGO_CLUB'); ?></th>
            <?php endif; ?>
            <?php if (!empty($this->config['show_club_internetadress_picture'])) : ?>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMS_HOMEPAGE_PICTURE'); ?></th>
            <?php endif; ?>
            <?php if (!empty($this->config['show_club_number'])) : ?>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMS_CLUB_NUMBER'); ?></th>
            <?php endif; ?>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMS_NAME_CLUBADDRESS'); ?></th>
            <?php if (!empty($this->config['show_club_playground'])) : ?>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_PLAYGROUND'); ?></th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->teams as $team) : ?>
            <?php
            $teamInfoLink = SiteRouteHelper::view('teaminfo', [
                'cfg_which_database' => $databaseSelector,
                's' => $seasonId,
                'p' => $projectSlug,
                'tid' => (string) ($team->team_slug ?? $team->id ?? ''),
                'ptid' => 0,
            ]);
            $clubInfoLink = SiteRouteHelper::view('clubinfo', [
                'cfg_which_database' => $databaseSelector,
                's' => $seasonId,
                'p' => $projectSlug,
                'cid' => (string) ($team->club_slug ?? $team->club_id ?? ''),
            ]);

            $teamTitle = Text::sprintf(
                'COM_SPORTSMANAGEMENT_TEAMS_TEAM_PROJECT_INFO',
                (string) ($team->team_name ?? $team->name ?? '')
            );
            $clubTitle = Text::sprintf(
                'COM_SPORTSMANAGEMENT_TEAMS_CLUB_PROJECT_INFO',
                (string) ($team->club_name ?? '')
            );

            if (!empty($this->config['show_small_logo'])) {
                $teamPictureProperty = (string) ($this->config['team_picture'] ?? 'picture');
                $teamPicture = (string) ($team->{$teamPictureProperty} ?? '');
                $placeholderKey = match ($teamPictureProperty) {
                    'logo_big' => 'ph_logo_big',
                    'logo_middle' => 'ph_logo_medium',
                    'logo_small' => 'ph_logo_small',
                    default => 'ph_team',
                };
                $teamPicture = $resolvePicture($teamPicture, $placeholderKey);
                $teamPictureHeight = in_array($teamPictureProperty, ['logo_small', 'logo_middle', 'logo_big'], true)
                    ? (int) ($this->config['team_logo_width'] ?? 20)
                    : (int) ($this->config['team_picture_width'] ?? 20);
                $teamImage = $renderPicture($teamPicture, $teamTitle, $teamPictureHeight);
            } else {
                $teamImage = HTMLHelper::image(
                    'media/com_sportsmanagement/jl_images/icon_copyright_2.png',
                    $teamTitle,
                    [
                        'title' => $teamTitle,
                        'style' => 'width:auto;height:' . max(1, (int) ($this->config['team_picture_width'] ?? 20)) . 'px',
                    ]
                );
            }
            $smallTeamLogoLink = $teamImage !== '' ? HTMLHelper::link($teamInfoLink, $teamImage) : '';

            $mediumClubLogoLink = '';
            if (!empty($this->config['show_medium_logo'])) {
                $clubPicture = $resolvePicture((string) ($team->logo_big ?? ''), 'ph_logo_medium');
                $clubImage = $renderPicture(
                    $clubPicture,
                    $clubTitle,
                    (int) ($this->config['team_logo_width'] ?? 50)
                );
                $mediumClubLogoLink = $clubImage !== '' ? HTMLHelper::link($clubInfoLink, $clubImage) : '';
            }
            ?>
            <tr>
                <td><?php echo $smallTeamLogoLink; ?></td>
                <td>
                    <?php
                    if ((int) ($this->config['which_link1'] ?? 0) === 1) {
                        echo HTMLHelper::link($teamInfoLink, $this->escape((string) ($team->team_name ?? '')));
                    } elseif (!empty($team->team_www)) {
                        echo HTMLHelper::link(
                            (string) $team->team_www,
                            $this->escape((string) ($team->team_name ?? '')),
                            ['target' => '_blank', 'rel' => 'noopener']
                        );
                    } else {
                        echo $this->escape((string) ($team->team_name ?? ''));
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if ((int) ($this->config['which_link2'] ?? 0) === 1) {
                        echo HTMLHelper::link($clubInfoLink, $this->escape((string) ($team->club_name ?? '')));
                    } elseif (!empty($team->club_www)) {
                        echo HTMLHelper::link(
                            (string) $team->club_www,
                            $this->escape((string) ($team->club_name ?? '')),
                            ['target' => '_blank', 'rel' => 'noopener']
                        );
                    } else {
                        echo $this->escape((string) ($team->club_name ?? ''));
                    }
                    ?>
                </td>

                <?php if (!empty($this->config['show_medium_logo'])) : ?>
                    <td><?php echo $mediumClubLogoLink; ?></td>
                <?php endif; ?>

                <?php if (!empty($this->config['show_club_internetadress_picture'])) : ?>
                    <td>
                        <?php
                        $website = trim((string) ($team->club_www ?? ''));
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
                                case 'pagepeeker':
                                    echo '<img src="http://free.pagepeeker.com/v2/thumbs.php?size='
                                        . rawurlencode((string) ($this->config['pagepeeker_size'] ?? ''))
                                        . '&amp;url=' . rawurlencode($website) . '" alt="">';
                                    break;
                                case 'miniature':
                                    echo '<img src="https://api.miniature.io/?width=100&amp;height=50&amp;url='
                                        . rawurlencode($website) . '" alt="">';
                                    break;
                            }
                        }
                        ?>
                    </td>
                <?php endif; ?>

                <?php if (!empty($this->config['show_club_number'])) : ?>
                    <td><?php echo $this->escape((string) ($team->unique_id ?? '')); ?></td>
                <?php endif; ?>

                <td>
                    <?php
                    echo CountryPresentationHelper::address(
                        (string) ($team->club_name ?? ''),
                        (string) ($team->club_address ?? ''),
                        (string) ($team->club_state ?? ''),
                        (string) ($team->club_zipcode ?? ''),
                        (string) ($team->club_location ?? ''),
                        (string) ($team->club_country ?? ''),
                        'COM_SPORTSMANAGEMENT_TEAMS_ADDRESS_FORM'
                    );

                    if (!empty($this->config['show_club_phone']) && !empty($team->club_phone)) {
                        echo '<br>';
                        echo HTMLHelper::image(
                            'administrator/components/com_sportsmanagement/assets/images/phone_14402.png',
                            '',
                            ['width' => 16]
                        );
                        echo $this->escape((string) $team->club_phone);
                    }

                    if (!empty($this->config['show_club_fax']) && !empty($team->club_fax)) {
                        echo '<br>';
                        echo HTMLHelper::image(
                            'administrator/components/com_sportsmanagement/assets/images/fax_icon-icons_com_52496.png',
                            '',
                            ['width' => 16]
                        );
                        echo $this->escape((string) $team->club_fax);
                    }

                    if (!empty($this->config['show_club_email']) && !empty($team->club_email)) {
                        echo '<br>';
                        echo HTMLHelper::image(
                            'administrator/components/com_sportsmanagement/assets/images/mail.png',
                            ''
                        );
                        echo $this->escape((string) $team->club_email);
                    }

                    $socialLinks = [
                        'facebook' => 'administrator/components/com_sportsmanagement/assets/images/facebook.png',
                        'instagram' => 'administrator/components/com_sportsmanagement/assets/images/instagram.png',
                        'twitter' => 'administrator/components/com_sportsmanagement/assets/images/twitter.png',
                    ];
                    foreach ($socialLinks as $property => $icon) {
                        $configKey = 'show_club_' . $property;
                        $url = trim((string) ($team->{$property} ?? ''));
                        if (!empty($this->config[$configKey]) && $url !== '') {
                            echo '<br>';
                            echo HTMLHelper::link(
                                $url,
                                HTMLHelper::image($icon, $url),
                                [
                                    'target' => '_blank',
                                    'rel' => 'noopener',
                                    'title' => (string) ($team->club_name ?? ''),
                                ]
                            );
                        }
                    }

                    if (!empty($this->config['show_googlemap_link'])) {
                        $mapQuery = trim(
                            (string) ($team->club_address ?? '') . ', '
                            . (string) ($team->club_zipcode ?? '') . ' '
                            . (string) ($team->club_location ?? '')
                        );
                        if ($mapQuery !== '') {
                            echo '<br>';
                            $mapLink = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($mapQuery);
                            echo HTMLHelper::link(
                                $mapLink,
                                HTMLHelper::image(
                                    'images/com_sportsmanagement/database/jl_images/map.gif',
                                    (string) ($team->club_name ?? '')
                                ),
                                [
                                    'target' => '_blank',
                                    'rel' => 'noopener',
                                    'title' => (string) ($team->club_name ?? ''),
                                ]
                            );
                        }
                    }
                    ?>
                </td>

                <?php if (!empty($this->config['show_club_playground'])) : ?>
                    <td>
                        <?php
                        $playgroundName = (string) ($team->playground_name ?? '');
                        $playgroundSlug = (string) ($team->playground_slug ?? '');

                        if ($playgroundName !== '') {
                            if ($playgroundSlug !== '') {
                                $playgroundLink = SiteRouteHelper::view('playground', [
                                    'cfg_which_database' => $databaseSelector,
                                    's' => $seasonId,
                                    'p' => $projectSlug,
                                    'pgid' => $playgroundSlug,
                                ]);
                                echo HTMLHelper::link($playgroundLink, $this->escape($playgroundName));
                            } else {
                                echo $this->escape($playgroundName);
                            }
                        }

                        if (!empty($this->config['show_playground_picture'])) {
                            $playgroundPicture = $resolvePicture(
                                (string) ($team->playground_picture ?? ''),
                                'ph_stadium'
                            );
                            if ($playgroundPicture !== '') {
                                echo '<br>';
                                echo ModalImageHelper::render(
                                    'playgroundteam' . (int) ($team->projectteamid ?? $team->id ?? 0),
                                    $pictureUrl($playgroundPicture),
                                    $playgroundName,
                                    (int) ($this->config['playground_picture_width'] ?? 20),
                                    '',
                                    $this->modalwidth,
                                    $this->modalheight,
                                    (int) ($this->overallconfig['use_jquery_modal'] ?? 0)
                                );
                            }
                        }
                        ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
