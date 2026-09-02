<?php
/**
 * Joomla 5/6 Teaminfo overview layout.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

if (!$this->team) {
    Log::add(Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_ERROR'), Log::WARNING, 'jsmerror');
    return;
}

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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

$pictureProperty = (string) ($this->config['show_picture'] ?? 'projectteam_picture');
$pictureProperty = in_array(
    $pictureProperty,
    ['picture', 'projectteam_picture', 'logo_small', 'logo_middle', 'logo_big'],
    true
) ? $pictureProperty : 'projectteam_picture';
$picture = trim((string) ($this->team->{$pictureProperty} ?? ''));

if ($picture === '') {
    $placeholderKey = match ($pictureProperty) {
        'logo_big' => 'ph_logo_big',
        'logo_middle' => 'ph_logo_medium',
        'logo_small' => 'ph_logo_small',
        default => 'ph_team',
    };
    $picture = trim((string) $params->get($placeholderKey, ''));
}

$pictureUrl = $imageUrl($picture);
$teamName = (string) ($this->team->name ?? $this->team->tname ?? '');
$databaseSelector = $this->input->getInt('cfg_which_database', 0);
$seasonId = $this->input->getInt('s', 0);
?>
<div class="row g-3">
    <div class="col-12 col-md-6" id="default_teaminfo-left">
        <?php if ($pictureUrl !== '') : ?>
            <?php
            echo ModalImageHelper::render(
                'teaminfo' . (int) ($this->team->id ?? 0),
                $pictureUrl,
                $teamName,
                (int) ($this->config['team_picture_width'] ?? 150),
                '',
                $this->modalwidth,
                $this->modalheight,
                $modalMode
            );
            ?>
        <?php endif; ?>

        <?php if (!empty($this->team->cr_projectteam_picture)) : ?>
            <?php
            echo Text::sprintf(
                'COM_SPORTSMANAGEMENT_COPYRIGHT_INFO',
                '<i>' . $escape($this->team->cr_projectteam_picture) . '</i>'
            );
            ?>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-6" id="default_teaminfo_right">
        <?php if (!empty($this->config['show_club_info']) && $this->club) : ?>
            <?php if ($this->clubAddress !== '') : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_ADDRESS'); ?></strong><br>
                    <?php echo $escape($this->clubAddress); ?>
                </address>
            <?php endif; ?>

            <?php if (!empty($this->club->phone)) : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_PHONE'); ?></strong>
                    <?php echo $escape($this->club->phone); ?>
                </address>
            <?php endif; ?>

            <?php if (!empty($this->club->fax)) : ?>
                <div class="jl_parentContainer">
                    <span class="clubinfo_listing_item">
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_FAX'); ?></strong>
                    </span>
                    <span class="clubinfo_listing_value"><?php echo $escape($this->club->fax); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($this->club->email)) : ?>
                <div class="jl_parentContainer">
                    <span class="clubinfo_listing_item">
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_EMAIL'); ?></strong>
                    </span>
                    <span class="clubinfo_listing_value">
                        <?php
                        $identity = $this->app->getIdentity();
                        $email = (string) $this->club->email;
                        ?>
                        <?php if ((int) $identity->id > 0 || empty($this->overallconfig['nospam_email'])) : ?>
                            <a href="mailto:<?php echo $escape($email); ?>"><?php echo $escape($email); ?></a>
                        <?php else : ?>
                            <?php echo HTMLHelper::_('email.cloak', $email); ?>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endif; ?>

            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_NAME'); ?></strong>
                <?php
                $clubLink = SiteRouteHelper::view('clubinfo', [
                    'cfg_which_database' => $databaseSelector,
                    's' => $seasonId,
                    'p' => $this->project->slug,
                    'cid' => $this->club->slug,
                ]);
                ?>
                <a href="<?php echo $escape($clubLink); ?>"><?php echo $escape($this->club->name); ?></a>
            </address>

            <?php if (!empty($this->club->website)) : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_SITE'); ?></strong>
                    <a href="<?php echo $escape($this->club->website); ?>" target="_blank" rel="noopener noreferrer">
                        <?php echo $escape($this->club->website); ?>
                    </a>
                </address>
            <?php endif; ?>

            <?php if ($this->merge_clubs) : ?>
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto px-2 fs-6">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_MERGE_CLUBS'); ?>
                    </legend>
                    <?php foreach ($this->merge_clubs as $mergeClub) : ?>
                        <?php
                        $mergeLink = SiteRouteHelper::view('clubinfo', [
                            'cfg_which_database' => $databaseSelector,
                            's' => $seasonId,
                            'p' => $this->project->slug,
                            'cid' => $mergeClub->slug,
                        ]);
                        ?>
                        <div class="jl_parentContainer">
                            <span class="clubinfo_listing_item">
                                <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_NAME'); ?>
                            </span>
                            <span class="clubinfo_listing_value">
                                <a href="<?php echo $escape($mergeLink); ?>"><?php echo $escape($mergeClub->name); ?></a>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($this->config['show_team_info'])) : ?>
            <?php
            $teamRoute = [
                'cfg_which_database' => $databaseSelector,
                's' => $seasonId,
                'p' => $this->project->slug,
                'tid' => $this->team->slug,
            ];
            $teamInfoRoute = $teamRoute;
            $teamInfoRoute['ptid'] = $this->input->getInt('ptid', 0);
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TEAM_NAME'); ?></strong>
                <a href="<?php echo $escape(SiteRouteHelper::view('teaminfo', $teamInfoRoute)); ?>">
                    <?php echo $escape($this->team->tname ?? $teamName); ?>
                </a>
            </address>

            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TEAM_NAME_SHORT'); ?></strong>
                <a href="<?php echo $escape(SiteRouteHelper::view('teamstats', $teamRoute)); ?>">
                    <?php echo $escape($this->team->short_name ?? ''); ?>
                </a>
            </address>

            <?php if (!empty($this->team->info)) : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_INFO'); ?></strong>
                    <?php echo $this->team->info; ?>
                </address>
            <?php endif; ?>

            <?php if (!empty($this->team->team_website)) : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TEAM_SITE'); ?></strong>
                    <a href="<?php echo $escape($this->team->team_website); ?>" target="_blank" rel="noopener noreferrer">
                        <?php echo $escape($this->team->team_website); ?>
                    </a>
                </address>
            <?php endif; ?>

            <?php if (!empty($this->team->team_email)) : ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TEAM_EMAIL'); ?></strong>
                    <?php
                    $identity = $this->app->getIdentity();
                    $email = (string) $this->team->team_email;
                    ?>
                    <?php if ((int) $identity->id > 0 || empty($this->overallconfig['nospam_email'])) : ?>
                        <a href="mailto:<?php echo $escape($email); ?>"><?php echo $escape($email); ?></a>
                    <?php else : ?>
                        <?php echo HTMLHelper::_('email.cloak', $email); ?>
                    <?php endif; ?>
                </address>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
