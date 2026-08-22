<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

$layout = $legacyLayout ?: 'default';
if ($layout === 'native' || $layout === '_:native') {
    $layout = 'default';
}
?>
<div
    class="<?= htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8') ?> jsm-ajax-top-navigation"
    id="<?= htmlspecialchars((string) $module->module, ENT_QUOTES, 'UTF-8') ?>-<?= (int) $module->id ?>"
    data-jsm-ajax-top-navigation
    data-module-id="<?= (int) $module->id ?>"
>
    <?php require ModuleHelper::getLayoutPath($module->module, $layout); ?>
</div>
