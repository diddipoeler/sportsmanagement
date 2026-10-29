<?php
/**
 * Default Joomla 5/6 layout for the current-season module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$routeFor = static fn (object $row): string => SiteRouteHelper::view('ranking', [
    'cfg_which_database' => (int) ($row->database_selector ?? 0),
    's' => 0,
    'p' => $row->project_slug,
    'type' => 0,
    'r' => $row->roundcode,
    'from' => 0,
    'to' => 0,
    'division' => 0,
]);

$buttonClass = trim((string) $params->get('button_class', 'btn btn-secondary'));
if ($buttonClass === 'btn btn-default') {
    $buttonClass = 'btn btn-secondary';
}

// Only button styles offered by the module manifest should become CSS classes.
$allowedButtonClasses = [
    'btn btn-secondary', 'btn btn-primary', 'btn btn-success',
    'btn btn-info', 'btn btn-warning', 'btn btn-danger', 'btn btn-link',
];
if (!in_array($buttonClass, $allowedButtonClasses, true)) {
    $buttonClass = 'btn btn-secondary';
}
$buttonClass = htmlspecialchars($buttonClass, ENT_QUOTES, 'UTF-8');
$moduleClass = htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8');
$moduleId = htmlspecialchars($module->module . '-' . $module->id, ENT_QUOTES, 'UTF-8');
?>
<div class="<?php echo $moduleClass; ?>" id="<?php echo $moduleId; ?>">
<?php
// Rows without a country cannot be assigned to a country tab; show the flat list
// in that case so no current-season projects disappear from the module.
$allRowsHaveCountry = true;
foreach ($list as $row) {
    if (trim((string) ($row->country ?? '')) === '') {
        $allRowsHaveCountry = false;
        break;
    }
}
?>
<?php if ((bool) $params->get('show_slider', 0) && $list !== [] && $federations !== [] && $countriesByFederation !== [] && $allRowsHaveCountry) : ?>
    <?php echo HTMLHelper::_('bootstrap.startTabSet', 'jsm-act-season-' . (int) $module->id, ['active' => 'fed-0']); ?>
    <?php $tab = 0; foreach ($federations as $fedId => $federation) : ?>
        <?php echo HTMLHelper::_('bootstrap.addTab', 'jsm-act-season-' . (int) $module->id, 'fed-' . $tab, htmlspecialchars(Text::_((string) $federation->name), ENT_QUOTES, 'UTF-8')); ?>
        <?php foreach (($countriesByFederation[$fedId] ?? []) as $country) : ?>
            <div class="row g-2 mb-2">
                <?php foreach ($list as $row) : if ((int) ($row->federation ?? 0) !== (int) $fedId || $row->country !== $country->alpha3) { continue; } ?>
                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                        <a href="<?php echo htmlspecialchars($routeFor($row), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $buttonClass; ?> w-100" role="button">
                            <?php echo $row->flag_html; ?> <?php echo htmlspecialchars(Text::_((string) $row->name), ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
    <?php $tab++; endforeach; ?>
    <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
<?php else : ?>
    <div class="row g-2">
        <?php foreach ($list as $row) : ?>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <a href="<?php echo htmlspecialchars($routeFor($row), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $buttonClass; ?> w-100" role="button">
                    <?php echo $row->flag_html; ?> <?php echo htmlspecialchars(Text::_((string) $row->name), ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
</div>
