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

$items = is_array($list) ? $list : [];
$moduleId = (int) ($module->id ?? 0);
$escape = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
                    <li class="d-flex gap-2 align-items-center mb-2">
                        <?php if (!empty($row->flag_url)) : ?>
                            <img src="<?php echo $escape($row->flag_url); ?>"
                                 alt="<?php echo $escape($row->country); ?>"
                                 loading="lazy" style="max-width:24px;height:auto">
                        <?php endif; ?>
                        <a href="<?php echo $escape($row->project_url); ?>">
                            <?php echo $escape($row->name); ?>
                            <span class="text-muted">(<?php echo $escape($row->league_name); ?>)</span>
                        </a>
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
