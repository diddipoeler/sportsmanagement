<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div class="container-fluid">
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
                            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_SEASON'); ?></th>
                            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE'); ?></th>
                            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_SPORTSTYPE'); ?></th>
                            <th><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_BY_LABEL'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$this->items) : ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($this->items as $item) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string) $item->name, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) $item->season, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) $item->league, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) $item->sportstype, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) $item->editor, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($this->pagination) : ?>
        <div class="mt-3">
            <?php echo $this->pagination->getListFooter(); ?>
        </div>
    <?php endif; ?>
</div>
