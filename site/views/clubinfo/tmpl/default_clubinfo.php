<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage clubinfo
 * @file       default_clubinfo.php
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

if (!$this->club) {
    return;
}

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$params = ComponentHelper::getParams('com_sportsmanagement');
$modalMode = (int) ($this->overallconfig['use_jquery_modal'] ?? 0);
$pictureBase = \defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')
    ? (string) COM_SPORTSMANAGEMENT_PICTURE_SERVER
    : '';
$imageUrl = static function (string $picture) use ($pictureBase): string {
    $picture = trim($picture);

    if ($picture === '' || preg_match('#^https?://#i', $picture)) {
        return $picture;
    }

    return rtrim($pictureBase, '/') . '/' . ltrim($picture, '/');
};

$clubLogo = trim((string) ($this->club->logo_big ?? ''));
if ($clubLogo === '') {
    $clubLogo = (string) $params->get('ph_logo_big', '');
}
$clubLogoUrl = $imageUrl($clubLogo);
$clubEmblemTitle = str_replace(
    '%CLUBNAME%',
    (string) ($this->club->name ?? ''),
    Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_EMBLEM_TITLE')
);
?>
<div class="row">
    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
        <?php if (!empty($this->config['show_club_logo']) && $clubLogoUrl !== '') : ?>
            <?php
            echo ModalImageHelper::render(
                'clubinfo' . (int) $this->club->id,
                $clubLogoUrl,
                $clubEmblemTitle,
                (int) ($this->config['club_logo_width'] ?? 150),
                '',
                $this->modalwidth,
                $this->modalheight,
                $modalMode,
                'itemprop',
                'image'
            );
            ?>
        <?php endif; ?>

        <?php if (!empty($this->config['show_club_logo_copyright']) && !empty($this->club->cr_logo_big)) : ?>
            <?php
            echo Text::sprintf(
                'COM_SPORTSMANAGEMENT_PAINTER_INFO',
                '<i>' . $escape($this->club->cr_logo_big) . '</i>'
            );
            ?>
        <?php endif; ?>
    </div>

    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
        <?php if (!empty($this->config['show_club_info'])) : ?>
            <?php if (!empty($this->address_string)) : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_ADDRESS'); ?></strong><br>
                    <?php echo $escape($this->address_string); ?>

                    <?php if ($this->clubassoc && !empty($this->clubassoc->name)) : ?>
                        <span class="clubinfo_listing_value">
                            <?php if (!empty($this->clubassoc->assocflag)) : ?>
                                <?php
                                echo HTMLHelper::image(
                                    (string) $this->clubassoc->assocflag,
                                    (string) $this->clubassoc->name,
                                    [
                                        'title' => (string) $this->clubassoc->name,
                                        'width' => (int) ($this->config['club_assoc_flag_width'] ?? 20),
                                    ]
                                );
                                ?>
                            <?php endif; ?>

                            <?php if (!empty($this->clubassoc->picture)) : ?>
                                <?php
                                echo HTMLHelper::image(
                                    (string) $this->clubassoc->picture,
                                    (string) $this->clubassoc->name,
                                    [
                                        'title' => (string) $this->clubassoc->name,
                                        'width' => (int) ($this->config['club_assoc_logo_width'] ?? 30),
                                    ]
                                );
                                ?>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </address>
            <?php endif; ?>

            <?php if (!empty($this->club->phone)) : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_PHONE'); ?></strong>
                    <span itemprop="telephone"><?php echo $escape($this->club->phone); ?></span>
                </address>
            <?php endif; ?>

            <?php if (!empty($this->club->fax)) : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_FAX'); ?></strong>
                    <span itemprop="faxNumber"><?php echo $escape($this->club->fax); ?></span>
                </address>
            <?php endif; ?>

            <?php if (!empty($this->club->email)) : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_EMAIL'); ?></strong>
                    <?php
                    $email = (string) $this->club->email;
                    $identity = $this->app->getIdentity();
                    $showMailLink = (int) $identity->id > 0 || empty($this->overallconfig['nospam_email']);
                    ?>
                    <?php if ($showMailLink) : ?>
                        <a itemprop="email" href="mailto:<?php echo $escape($email); ?>"><?php echo $escape($email); ?></a>
                    <?php else : ?>
                        <span itemprop="email"><?php echo $escape($email); ?></span>
                    <?php endif; ?>
                </address>
            <?php endif; ?>

            <?php
            $externalLinks = [
                'website' => 'COM_SPORTSMANAGEMENT_CLUBINFO_WWW',
                'instagram' => 'COM_SPORTSMANAGEMENT_EXT_PERSON_INSTAGRAM',
                'twitter' => 'COM_SPORTSMANAGEMENT_EXT_PERSON_TWITTER',
                'facebook' => 'COM_SPORTSMANAGEMENT_EXT_PERSON_FACEBOOK',
            ];
            ?>
            <?php foreach ($externalLinks as $property => $label) : ?>
                <?php $url = trim((string) ($this->club->{$property} ?? '')); ?>
                <?php if ($url !== '') : ?>
                    <address>
                        <strong><?php echo Text::_($label); ?></strong>
                        <span itemprop="url">
                            <?php echo HTMLHelper::link($url, $escape($url), ['target' => '_blank', 'rel' => 'noopener noreferrer']); ?>
                        </span>
                    </address>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if (!empty($this->club->president)) : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_PRESIDENT'); ?></strong>
                    <?php echo $escape($this->club->president); ?>
                </address>
            <?php endif; ?>

            <?php if (!empty($this->club->manager)) : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_MANAGER'); ?></strong>
                    <?php echo $escape($this->club->manager); ?>
                </address>
            <?php endif; ?>

            <?php if (!empty($this->club->founded) && $this->club->founded !== '0000-00-00' && !empty($this->config['show_founded'])) : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_FOUNDED'); ?></strong>
                    <?php echo HTMLHelper::date($this->club->founded, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE')); ?>
                </address>
            <?php endif; ?>

            <?php if (!empty($this->club->founded_year) && !empty($this->config['show_founded_year'])) : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_FOUNDED_YEAR'); ?></strong>
                    <?php echo $escape($this->club->founded_year); ?>
                </address>
            <?php endif; ?>

            <?php if (!empty($this->club->dissolved) && $this->club->dissolved !== '0000-00-00' && !empty($this->config['show_dissolved'])) : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_DISSOLVED'); ?></strong>
                    <?php echo HTMLHelper::date($this->club->dissolved, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE')); ?>
                </address>
            <?php endif; ?>

            <?php if (!empty($this->club->dissolved_year) && !empty($this->config['show_dissolved_year'])) : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_DISSOLVED_YEAR'); ?></strong>
                    <?php echo $escape($this->club->dissolved_year); ?>
                </address>
            <?php endif; ?>

            <?php if (!empty($this->club->unique_id)) : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_UNIQUE_ID'); ?></strong>
                    <?php echo $escape($this->club->unique_id); ?>
                </address>
            <?php endif; ?>

            <?php if (!empty($this->config['show_playgrounds_of_club']) && $this->playgrounds) : ?>
                <?php $playgroundNumber = 1; ?>
                <?php foreach ($this->playgrounds as $playground) : ?>
                    <?php
                    $link = SiteRouteHelper::view('playground', [
                        'cfg_which_database' => $this->input->getInt('cfg_which_database', 0),
                        's' => $this->input->getInt('s', 0),
                        'p' => (string) ($this->project->slug ?? $this->project->id ?? ''),
                        'pgid' => (string) ($playground->slug ?? $playground->id ?? ''),
                    ]);
                    $playgroundPicture = trim((string) ($playground->picture ?? ''));
                    $isRemotePicture = preg_match('#^https?://#i', $playgroundPicture) === 1;
                    $exists = $isRemotePicture
                        || ($playgroundPicture !== '' && is_file(JPATH_ROOT . '/' . ltrim($playgroundPicture, '/')));

                    if (!$exists) {
                        $playgroundPicture = (string) $params->get('ph_stadium', '');
                    }
                    $playgroundPictureUrl = $imageUrl($playgroundPicture);
                    $label = str_replace(
                        '%NUMBER%',
                        (string) $playgroundNumber,
                        Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_PLAYGROUND')
                    );
                    ?>
                    <address>
                        <strong><?php echo $escape($label); ?></strong>
                        <?php echo HTMLHelper::link($link, $escape($playground->name ?? '')); ?>
                        <?php if ($playgroundPictureUrl !== '') : ?>
                            <?php
                            echo ModalImageHelper::render(
                                'playground' . (int) ($playground->id ?? 0),
                                $playgroundPictureUrl,
                                (string) ($playground->name ?? ''),
                                (int) ($this->config['playground_picture_width'] ?? 50),
                                '',
                                $this->modalwidth,
                                $this->modalheight,
                                $modalMode
                            );
                            ?>
                        <?php endif; ?>
                    </address>
                    <?php $playgroundNumber++; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($this->config['show_club_kunena_link']) && !empty($this->club->sb_catid)) : ?>
                <?php
                $kunenaLink = Route::_(
                    'index.php?option=com_kunena&view=topic&catid=' . (int) $this->club->sb_catid,
                    false
                );
                $imgTitle = (string) $this->club->name . ' Forum';
                $description = HTMLHelper::image(
                    'media/COM_SPORTSMANAGEMENT/jl_images/kunena.logo.png',
                    $imgTitle,
                    ['title' => $imgTitle, 'width' => '100']
                );
                ?>
                <span class="clubinfo_listing_value">
                    <?php echo HTMLHelper::link($kunenaLink, $description); ?>
                </span>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7">
        <?php if (!empty($this->config['show_notes_club'])) : ?>
            <?php
            $this->notes = [Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAM_DESCRIPTION')];
            echo $this->loadTemplate('jsm_notes');
            ?>
            <?php echo HTMLHelper::_('content.prepare', (string) ($this->club->notes ?? '')); ?>
        <?php endif; ?>

        <?php
        $this->notes = [Text::_('COM_SPORTSMANAGEMENT_CLUB_HISTORYLOGO')];
        echo $this->loadTemplate('jsm_notes');
        ?>

        <?php foreach ($this->logohistory_detail as $logo => $seasons) : ?>
            <div class="row">
                <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1">
                    <?php echo HTMLHelper::image((string) $logo, (string) ($this->club->name ?? ''), ['width' => 50]); ?>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                    <p class="text-break"><?php echo $escape(implode(',', (array) $seasons)); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
