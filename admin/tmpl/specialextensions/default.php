<?php
/**
 * Native Joomla 5/6 administrator special extensions overview.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div class="container-fluid px-0">
    <?php if ($this->extensions) : ?>
        <div class="row g-3">
            <?php foreach ($this->extensions as $extension) : ?>
                <?php
                $view = trim((string) $extension);
                $label = Text::_($view);
                ?>
                <div class="col-sm-6 col-lg-4 col-xl-3">
                    <a class="card h-100 text-decoration-none"
                       href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=' . rawurlencode($view), false); ?>">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="icon-puzzle-piece fs-2" aria-hidden="true"></span>
                            <span class="fw-semibold text-break"><?php echo $this->escape($label); ?></span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="alert alert-info">
            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php endif; ?>
</div>
