<?php
/** Native Joomla 5/6 prediction management dashboard. */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$sections = [
    [
        'view' => 'predictiongames',
        'icon' => 'icon-list',
        'label' => 'COM_SPORTSMANAGEMENT_EXT_PREDICTION_GAMES',
    ],
    [
        'view' => 'predictiongroups',
        'icon' => 'icon-users',
        'label' => 'COM_SPORTSMANAGEMENT_EXT_PREDICTION_GROUPS',
    ],
    [
        'view' => 'predictionmembers',
        'icon' => 'icon-user',
        'label' => 'COM_SPORTSMANAGEMENT_EXT_PREDICTION_MEMBERS',
    ],
    [
        'view' => 'predictiontemplates',
        'icon' => 'icon-options',
        'label' => 'COM_SPORTSMANAGEMENT_EXT_PREDICTION_TEMPLATES',
    ],
];
?>
<div class="row g-4">
    <?php foreach ($sections as $section) : ?>
        <div class="col-12 col-md-6 col-xl-3">
            <a
                class="card h-100 text-decoration-none"
                href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=' . $section['view']); ?>"
            >
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="<?php echo $this->escape($section['icon']); ?> fs-2" aria-hidden="true"></span>
                    <span class="h5 mb-0 text-body">
                        <?php echo Text::_($section['label']); ?>
                    </span>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
