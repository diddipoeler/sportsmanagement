<?php
/**
 * Joomla 5/6 wrapper layout for the SportsManagement AJAX top navigation module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

// The runtime still displays the configured country map image. Keep this one
// compatibility helper local to the presentation layer until country flags are
// moved into a namespaced service.
$countriesHelper = JPATH_SITE . '/components/com_sportsmanagement/helpers/countries.php';
if (!class_exists('JSMCountries', false) && is_file($countriesHelper)) {
    require_once $countriesHelper;
}
?>
<div
    class="<?= htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8') ?> jsm-ajax-top-navigation"
    id="<?= htmlspecialchars((string) $module->module, ENT_QUOTES, 'UTF-8') ?>-<?= (int) $module->id ?>"
    data-jsm-ajax-top-navigation
    data-module-id="<?= (int) $module->id ?>"
>
    <?php require ModuleHelper::getLayoutPath($module->module, 'runtime'); ?>
</div>
