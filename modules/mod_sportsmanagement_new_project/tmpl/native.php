<?php
/**
 * Joomla 5/6 native layout for mod_sportsmanagement_new_project.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$items = is_array($list ?? null) ? $list : [];
$moduleId = (int) ($module->id ?? 0);
/**
 * Escape a module field for HTML attributes and visible text.
 *
 * Invalid legacy values must not be converted from arrays or objects into
 * PHP 8 warnings. Malformed UTF-8 is replaced rather than breaking layout.
 *
 * @param mixed $value Module option, project title or generated route.
 * @return string Escaped scalar text or empty string for unsupported values.
 */
$escape = static fn (mixed $value): string => htmlspecialchars(
    is_string($value) || is_numeric($value) ? (string) $value : '',
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

/**
 * Validate an optional project or country-flag URL before rendering a link.
 *
 * Allows safe internal Joomla routes and HTTP(S) URLs but rejects executable
 * URI schemes, embedded credentials and control characters from corrupt
 * cached project data.
 *
 * @param mixed $value Generated module route or flag URL.
 * @return string Safe URL or an empty string when invalid.
 */
$safeUrl = static function (mixed $value): string {
    if (!is_string($value)) {
        return '';
    }

    $url = trim($value);

    if ($url === '' || strlen($url) > 4096
        || preg_match('/[\x00-\x20\x7F]/', $url)
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
$endpoint = 'index.php?option=com_ajax&module=sportsmanagement_new_project&method=createArticles&format=json';
?>
<div class="mod-sportsmanagement-new-project <?php echo $escape($params->get('moduleclass_sfx', '')); ?>"
     data-jsm-new-project
     data-endpoint="<?php echo $escape($endpoint); ?>"
     data-creating-text="<?php echo $escape(Text::_('MOD_SPORTSMANAGEMENT_NEW_PROJECT_CREATING')); ?>"
     data-created-text="<?php echo $escape(Text::_('MOD_SPORTSMANAGEMENT_NEW_PROJECT_CREATED_STATUS')); ?>"
     data-error-text="<?php echo $escape(Text::_('MOD_SPORTSMANAGEMENT_NEW_PROJECT_CREATE_ERROR')); ?>">
    <details open>
        <summary><?php echo Text::_('MOD_SPORTSMANAGEMENT_NEW_PROJECT_LIST_TITLE'); ?></summary>

        <?php if (!$items) : ?>
            <p><?php echo Text::_('MOD_SPORTSMANAGEMENT_NEW_PROJECT_NONE'); ?></p>
        <?php else : ?>
            <p><?php echo Text::sprintf('MOD_SPORTSMANAGEMENT_NEW_PROJECT_COUNT', count($items)); ?></p>
            <ul class="list-unstyled">
                <?php foreach ($items as $row) : ?>
                    <?php
                    if (!is_object($row)) {
                        continue;
                    }
                    $flagUrl = $safeUrl($row->flag_url ?? '');
                    $projectUrl = $safeUrl($row->project_url ?? '');
                    ?>
                    <li class="d-flex gap-2 align-items-center mb-2">
                        <?php if ($flagUrl !== '') : ?>
                            <img src="<?php echo $escape($flagUrl); ?>"
                                 alt="<?php echo $escape($row->country ?? ''); ?>"
                                 loading="lazy" style="max-width:24px;height:auto">
                        <?php endif; ?>
                        <?php if ($projectUrl !== '') : ?>
                            <a href="<?php echo $escape($projectUrl); ?>">
                        <?php endif; ?>
                            <?php echo $escape($row->name ?? ''); ?>
                            <span class="text-muted">(<?php echo $escape($row->league_name ?? ''); ?>)</span>
                        <?php if ($projectUrl !== '') : ?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </details>

    <?php if ($canCreateArticles && $moduleId > 0) : ?>
        <div class="mt-2">
            <button type="button" class="btn btn-sm btn-primary" data-jsm-create-project-articles>
                <?php echo Text::_('MOD_SPORTSMANAGEMENT_NEW_PROJECT_CREATE_ARTICLES'); ?>
            </button>
            <span class="ms-2" data-jsm-create-project-articles-status aria-live="polite"></span>
        </div>
        <form hidden data-jsm-create-project-articles-form>
            <input type="hidden" name="module_id" value="<?php echo $moduleId; ?>">
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    <?php endif; ?>
</div>
