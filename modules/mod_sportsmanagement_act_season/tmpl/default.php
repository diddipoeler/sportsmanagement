<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$routeFor = static function (object $row): string {
    $query = http_build_query([
        'option' => 'com_sportsmanagement',
        'view' => 'ranking',
        'cfg_which_database' => (int) ($row->database_selector ?? 0),
        's' => 0,
        'p' => $row->project_slug,
        'type' => 0,
        'r' => $row->roundcode,
        'from' => 0,
        'to' => 0,
        'division' => 0,
    ]);
    return Route::_('index.php?' . $query, false);
};
?>
<div class="<?php echo htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($module->module . '-' . $module->id, ENT_QUOTES, 'UTF-8'); ?>">
<?php if ($params->get('show_slider')) : ?>
    <?php echo HTMLHelper::_('bootstrap.startTabSet', 'jsm-act-season-' . (int) $module->id, ['active' => 'fed-0']); ?>
    <?php $tab = 0; foreach ($federations as $fedId => $federation) : ?>
        <?php echo HTMLHelper::_('bootstrap.addTab', 'jsm-act-season-' . (int) $module->id, 'fed-' . $tab, Text::_($federation->name)); ?>
        <?php foreach (($countriesByFederation[$fedId] ?? []) as $country) : ?>
            <div class="row g-2 mb-2">
                <?php foreach ($list as $row) : if ($row->country !== $country->alpha3) { continue; } ?>
                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                        <a href="<?php echo htmlspecialchars($routeFor($row), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo htmlspecialchars((string) $params->get('button_class', 'btn btn-primary'), ENT_QUOTES, 'UTF-8'); ?> w-100" role="button">
                            <?php echo $row->flag_html; ?> <?php echo Text::_($row->name); ?>
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
                <a href="<?php echo htmlspecialchars($routeFor($row), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo htmlspecialchars((string) $params->get('button_class', 'btn btn-primary'), ENT_QUOTES, 'UTF-8'); ?> w-100" role="button">
                    <?php echo $row->flag_html; ?> <?php echo Text::_($row->name); ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
</div>
