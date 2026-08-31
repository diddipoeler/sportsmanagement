<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

// The remaining legacy layout still renders country flags through JSMCountries.
// Keep that compatibility dependency in the presentation layer instead of the
// native data helper.
$countriesHelper = JPATH_SITE . '/components/com_sportsmanagement/helpers/countries.php';
if (!class_exists('JSMCountries', false) && is_file($countriesHelper)) {
    require_once $countriesHelper;
}

$layout = $legacyLayout ?: 'default';
if ($layout === 'native' || $layout === '_:native') {
    $layout = 'default';
}

// Legacy layouts still reference these values at the bottom of the template.
$ajax = $ajax ?? 0;
$ajaxmod = $ajaxmod ?? 0;
?>
<div
    class="<?= htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8') ?> jsm-ajax-top-navigation"
    id="<?= htmlspecialchars((string) $module->module, ENT_QUOTES, 'UTF-8') ?>-<?= (int) $module->id ?>"
    data-jsm-ajax-top-navigation
    data-module-id="<?= (int) $module->id ?>"
>
    <?php require ModuleHelper::getLayoutPath($module->module, $layout); ?>
</div>
