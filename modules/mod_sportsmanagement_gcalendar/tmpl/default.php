<?php
/**
 * Joomla 5/6 layout for the SportsManagement Google calendar module.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

$moduleId = (int) $module->id;
$root = rtrim(Uri::root(), '/') . '/';
$loader = htmlspecialchars(
    $root . 'administrator/components/com_sportsmanagement/assets/images/ajax-loader.gif',
    ENT_QUOTES,
    'UTF-8'
);
?>
<div id="gcalendar_module_<?php echo $moduleId; ?>_loading" class="jsm-gcalendar-loading" style="text-align:center;">
    <img src="<?php echo $loader; ?>" alt="" loading="lazy">
</div>
<div id="gcalendar_module_<?php echo $moduleId; ?>" class="jsm-gcalendar"></div>
<div id="gcalendar_module_<?php echo $moduleId; ?>_popup" hidden></div>
