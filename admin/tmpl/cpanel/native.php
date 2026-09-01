<?php
/**
 * Native Joomla 5/6 SportsManagement administrator dashboard.
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="container-fluid">
    <div class="row g-3">
        <div class="col-12 col-xl-9">
            <?php foreach ($this->dashboardLinks as $section) : ?>
                <section class="card mb-3">
                    <div class="card-header">
                        <h2 class="h5 mb-0"><?php echo Text::_($section['title']); ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <?php foreach ($section['items'] as $item) : ?>
                                <div class="col-12 col-md-6 col-xxl-4">
                                    <a
                                        class="btn btn-outline-secondary w-100 text-start"
                                        href="<?php echo $escape(Route::_($item['url'])); ?>"
                                    >
                                        <span class="<?php echo $escape($item['icon']); ?>" aria-hidden="true"></span>
                                        <span><?php echo Text::_($item['label']); ?></span>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>

        <aside class="col-12 col-xl-3">
            <div class="card mb-3">
                <div class="card-header">
                    <h2 class="h5 mb-0"><?php echo Text::_('COM_SPORTSMANAGEMENT_INFORMATION'); ?></h2>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-6"><?php echo Text::_('COM_SPORTSMANAGEMENT_VERSION'); ?></dt>
                        <dd class="col-6"><?php echo $escape($this->version !== '' ? $this->version : '-'); ?></dd>

                        <dt class="col-6"><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_COUNTRIES'); ?></dt>
                        <dd class="col-6"><?php echo (int) $this->countryCount; ?></dd>
                    </dl>
                </div>
            </div>

            <?php if ($this->countryCount === 0) : ?>
                <div class="alert alert-warning">
                    <p class="mb-2">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_COUNTRIES_ERROR'); ?>
                    </p>
                    <a
                        class="btn btn-sm btn-outline-secondary"
                        href="<?php echo $escape(Route::_('index.php?option=com_sportsmanagement&view=databasetools')); ?>"
                    >
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TOOLS'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</div>
