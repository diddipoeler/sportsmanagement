<?php
/**
 * Native Joomla 5/6 next-match header.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\TeamLogoHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$teams = is_array($this->teams ?? null) ? $this->teams : [];
$home = $teams[0] ?? null;
$away = $teams[1] ?? null;
$pictureMode = (int) ($this->overallconfig['use_jquery_modal'] ?? 0);
$pictureBase = \defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')
    ? (string) COM_SPORTSMANAGEMENT_PICTURE_SERVER
    : Uri::root();
$teamPlaceholder = trim((string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_team', ''));

$renderProjectTeamPicture = static function (?object $team, string $target, int $width) use (
    $pictureBase,
    $teamPlaceholder,
    $pictureMode
): string {
    if (!$team) {
        return '';
    }

    $picture = trim((string) ($team->projectteam_picture ?? ''));
    if ($picture === '') {
        $picture = $teamPlaceholder;
    }
    if ($picture === '') {
        return '';
    }

    $url = preg_match('#^https?://#i', $picture)
        ? $picture
        : rtrim($pictureBase, '/') . '/' . ltrim($picture, '/');

    return ModalImageHelper::render(
        $target,
        $url,
        (string) ($team->name ?? ''),
        max(1, $width),
        '',
        $this->modalwidth,
        $this->modalheight,
        $pictureMode
    );
};
?>
<div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?> table-responsive" id="nextmatch">
    <table class="table">
        <?php if (!empty($this->config['show_logo']) && $home && $away) : ?>
            <?php $logoProperty = (string) ($this->config['show_picture'] ?? 'logo_big'); ?>
            <tr class="nextmatch">
                <td class="teamlogo">
                    <?php echo TeamLogoHelper::renderVariant(
                        $home,
                        $logoProperty,
                        'nextmatch' . (int) ($home->id ?? 0),
                        max(1, (int) ($this->config['club_logo_width'] ?? 20)),
                        $this->modalwidth,
                        $this->modalheight,
                        $pictureMode
                    ); ?>
                </td>
                <td class="vs">&nbsp;</td>
                <td class="teamlogo">
                    <?php echo TeamLogoHelper::renderVariant(
                        $away,
                        $logoProperty,
                        'nextmatch' . (int) ($away->id ?? 0),
                        max(1, (int) ($this->config['club_logo_width'] ?? 20)),
                        $this->modalwidth,
                        $this->modalheight,
                        $pictureMode
                    ); ?>
                </td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->config['show_team_picture']) && $home && $away) : ?>
            <tr class="nextmatch">
                <td class="teampicture">
                    <?php echo $renderProjectTeamPicture(
                        $home,
                        'nextmatch_team' . (int) ($home->id ?? 0),
                        max(1, (int) ($this->config['team_picture_width'] ?? 20))
                    ); ?>
                </td>
                <td class="vs">&nbsp;</td>
                <td class="teampicture">
                    <?php echo $renderProjectTeamPicture(
                        $away,
                        'nextmatch_team' . (int) ($away->id ?? 0),
                        max(1, (int) ($this->config['team_picture_width'] ?? 20))
                    ); ?>
                </td>
            </tr>
        <?php endif; ?>

        <tr class="nextmatch">
            <td class="team">
                <?php echo $home
                    ? htmlspecialchars((string) ($home->name ?? ''), ENT_QUOTES, 'UTF-8')
                    : Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_UNKNOWNTEAM'); ?>
            </td>
            <td class="vs"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_VS'); ?></td>
            <td class="team">
                <?php echo $away
                    ? htmlspecialchars((string) ($away->name ?? ''), ENT_QUOTES, 'UTF-8')
                    : Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_UNKNOWNTEAM'); ?>
            </td>
        </tr>
    </table>

    <?php
    $reportLink = $this->match
        ? SiteRouteHelper::view('matchreport', [
            'cfg_which_database' => $this->input->getInt('cfg_which_database', 0) === 1 ? 1 : 0,
            's' => max(0, $this->input->getInt('s', 0)),
            'p' => $this->project->slug ?? $this->project->id ?? 0,
            'mid' => $this->match->id ?? 0,
        ])
        : '';
    ?>

    <?php if ($reportLink !== '' && isset($this->match->team1_result, $this->match->team2_result)) : ?>
        <div class="notice">
            <?php echo HTMLHelper::link(
                $reportLink,
                Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_ALREADYPLAYED')
            ); ?>
        </div>
    <?php endif; ?>

    <br>
</div>
