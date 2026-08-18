<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$items = is_array($list) ? $list : [];
$moduleId = (int) ($module->id ?? 0);
?>
<div class="mod-sportsmanagement-new-project <?php echo htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8'); ?>">
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
                            <img src="<?php echo htmlspecialchars((string) $row->flag_url, ENT_QUOTES, 'UTF-8'); ?>"
                                 alt="<?php echo htmlspecialchars((string) $row->country, ENT_QUOTES, 'UTF-8'); ?>"
                                 loading="lazy" style="max-width:24px;height:auto">
                        <?php endif; ?>
                        <a href="<?php echo htmlspecialchars((string) $row->project_url, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars((string) $row->name, ENT_QUOTES, 'UTF-8'); ?>
                            <span class="text-muted">(<?php echo htmlspecialchars((string) $row->league_name, ENT_QUOTES, 'UTF-8'); ?>)</span>
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
        <script>
        (() => {
            const root = document.currentScript.closest('.mod-sportsmanagement-new-project');
            if (!root) return;
            const button = root.querySelector('[data-jsm-create-project-articles]');
            const form = root.querySelector('[data-jsm-create-project-articles-form]');
            const status = root.querySelector('[data-jsm-create-project-articles-status]');
            if (!button || !form || !status) return;
            button.addEventListener('click', async () => {
                button.disabled = true;
                status.textContent = <?php echo json_encode(Text::_('MOD_SPORTSMANAGEMENT_NEW_PROJECT_CREATING')); ?>;
                try {
                    const response = await fetch('index.php?option=com_ajax&module=sportsmanagement_new_project&method=createArticles&format=json', {
                        method: 'POST',
                        body: new FormData(form),
                        credentials: 'same-origin'
                    });
                    const payload = await response.json();
                    const result = Array.isArray(payload.data) ? payload.data[0] : payload.data;
                    if (!response.ok || !result) throw new Error(payload.message || 'Request failed');
                    status.textContent = <?php echo json_encode(Text::_('MOD_SPORTSMANAGEMENT_NEW_PROJECT_CREATED_STATUS')); ?>
                        .replace('%1$d', Number(result.created || 0))
                        .replace('%2$d', Number(result.skipped || 0));
                } catch (error) {
                    status.textContent = <?php echo json_encode(Text::_('MOD_SPORTSMANAGEMENT_NEW_PROJECT_CREATE_ERROR')); ?>;
                } finally {
                    button.disabled = false;
                }
            });
        })();
        </script>
    <?php endif; ?>
</div>
