<?php
/**
 * Joomla 5/6 default layout for the first league overview module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Escape the name or HTML attribute of a federation and project.
 *
 * Malformed legacy rows can contain arrays, objects or invalid UTF-8; never
 * cast unsupported values directly into public Joomla HTML output.
 *
 * @param mixed $value Raw module option or database field.
 * @return string Escaped UTF-8 text or an empty string for invalid fields.
 */
$escape = static fn (mixed $value): string => htmlspecialchars(
    is_string($value) || is_numeric($value) ? (string) $value : '',
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

/**
 * Accept only relative Joomla routes or properly formed HTTP(S) addresses.
 *
 * Prevents javascript:, data:, protocol-relative and userinfo URLs from
 * entering the federation image or ranking link attributes.
 *
 * @param mixed $value Generated project route or federation picture.
 * @return string Safe link target, or empty text when invalid.
 */
$safeUrl = static function (mixed $value): string {
    if (!is_string($value)) {
        return '';
    }

    $url = trim($value);

    if ($url === '' || strlen($url) > 4096
        || preg_match('/[\x00-\x20\x7f]/', $url)
        || str_contains($url, '\\')
        || str_starts_with($url, '//')) {
        return '';
    }

    $parts = parse_url($url);

    if (!is_array($parts)) {
        return '';
    }

    $scheme = strtolower((string) ($parts['scheme'] ?? ''));

    if ($scheme !== '' && (!in_array($scheme, ['http', 'https'], true)
        || empty($parts['host']) || isset($parts['user']) || isset($parts['pass']))) {
        return '';
    }

    return $url;
};

$federations = array_filter(is_array($federations ?? null) ? $federations : [], 'is_object');
$firstleagueoverview = array_filter(
    is_array($firstleagueoverview ?? null) ? $firstleagueoverview : [],
    'is_object'
);
$tabSetId = 'jsm-firstleagueoverview-' . (int) ($module->id ?? 0);
$activeTab = $federations ? 'federation-' . (int) array_key_first($federations) : '';
?>
<div class="<?php echo $escape($params->get('moduleclass_sfx', '')); ?>"
     id="<?php echo $escape((is_string($module->module ?? null) ? $module->module : 'mod_sportsmanagement_firstleagueoverview') . '-' . (int) ($module->id ?? 0)); ?>">
    <div class="mb-2">
        <?php echo Text::_('MOD_SPORTSMANAGEMENT_FIRSTLEAGUEOVERVIEW_DESCRIPTION'); ?>
    </div>

    <?php if ($federations) : ?>
        <?php echo HTMLHelper::_('uitab.startTabSet', $tabSetId, ['active' => $activeTab]); ?>
        <?php foreach ($federations as $federationId => $federation) : ?>
            <?php
            $tabId = 'federation-' . (int) $federationId;
            // Label text is escaped separately from the optional safe image.
            $federationName = is_string($federation->name ?? null) ? $federation->name : '';
            $label = $escape(Text::_($federationName));
            $pictureUrl = $safeUrl($federation->picture_url ?? '');

            if ($pictureUrl !== '') {
                $label .= ' <img src="' . $escape($pictureUrl)
                    . '" alt="' . $escape(Text::_($federationName))
                    . '" width="50" loading="lazy">';
            }
            ?>
            <?php echo HTMLHelper::_('uitab.addTab', $tabSetId, $tabId, $label); ?>

            <?php $hasProjects = false; ?>
            <?php foreach ($firstleagueoverview as $project) : ?>
                <?php
                $projectFederationId = filter_var($project->federation ?? 0, FILTER_VALIDATE_INT);
                if ($projectFederationId === false || $projectFederationId !== (int) $federationId) {
                    continue;
                }
                $rankingUrl = $safeUrl($project->ranking_link ?? '');
                ?>
                <?php $hasProjects = true; ?>
                <div class="mb-1">
                    <?php echo is_string($project->flag_html ?? null) ? $project->flag_html : ''; ?>
                    <?php if ($rankingUrl !== '') : ?>
                        <a href="<?php echo $escape($rankingUrl); ?>">
                    <?php endif; ?>
                        <?php echo $escape($project->name ?? ''); ?>
                    <?php if ($rankingUrl !== '') : ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if (!$hasProjects) : ?>
                <div class="text-muted"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div>
            <?php endif; ?>

            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endforeach; ?>
        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    <?php else : ?>
        <div class="text-muted"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div>
    <?php endif; ?>
</div>
