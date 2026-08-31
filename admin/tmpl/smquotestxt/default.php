<?php
/** Native Joomla 5/6 administrator quote text-files layout. */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_EXT_TXT'); ?></strong>
        </div>
        <div class="card-body p-0">
            <?php if (empty($this->files)) : ?>
                <div class="p-3 text-muted"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div>
            <?php else : ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($this->files as $file) : ?>
                        <?php
                        $fileName = (string) $file;
                        $link = Route::_(
                            'index.php?option=com_sportsmanagement&view=smquotetxt&layout=default&file_name=' . rawurlencode($fileName)
                        );
                        ?>
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?php echo $link; ?>">
                            <span><?php echo $escape($fileName); ?></span>
                            <span class="icon-edit" aria-hidden="true"></span>
                            <span class="visually-hidden"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_EXT_TXT_EDIT'); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
