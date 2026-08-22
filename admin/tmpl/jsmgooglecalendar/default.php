<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$assetBase = Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/images';
$actions = [
    [
        'url' => Route::_('index.php?option=com_sportsmanagement&view=jsmgcalendars'),
        'image' => $assetBase . '/48-calendar.png',
        'label' => Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_GCALENDARS'),
    ],
    [
        'url' => Route::_('index.php?option=com_sportsmanagement&view=jsmgcalendarimport&layout=login'),
        'image' => $assetBase . '/admin/import.png',
        'label' => Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_IMPORT'),
    ],
    [
        'url' => Route::_('index.php?option=com_sportsmanagement&view=jsmgcalendar&layout=edit'),
        'image' => $assetBase . '/admin/add.png',
        'label' => Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_ADD'),
    ],
];
?>
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h4 mb-3"><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_WELCOME'); ?></h2>
            <p class="mb-0"><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_INTRO'); ?></p>
        </div>
    </div>

    <div class="row g-3">
        <?php foreach ($actions as $action) : ?>
            <div class="col-12 col-md-6 col-xl-4">
                <a class="card h-100 text-decoration-none" href="<?php echo $action['url']; ?>">
                    <div class="card-body d-flex align-items-center gap-3">
                        <img src="<?php echo htmlspecialchars($action['image'], ENT_QUOTES, 'UTF-8'); ?>"
                             alt=""
                             width="48"
                             height="48"
                             loading="lazy">
                        <span class="fw-semibold"><?php echo $action['label']; ?></span>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
