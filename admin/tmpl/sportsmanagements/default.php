<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=sportsmanagements'); ?>"
    method="post"
    name="adminForm"
    id="adminForm"
>
    <div class="table-responsive">
        <table class="table table-striped" id="sportsmanagementList">
            <thead>
                <tr>
                    <th scope="col" class="w-1 text-center"><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
                    <th scope="col" class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                    <th scope="col"><?php echo Text::_('COM_HELLOWORLD_HELLOWORLD_HEADING_GREETING'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->items as $i => $item) : ?>
                    <?php $editUrl = Route::_('index.php?option=com_sportsmanagement&task=sportsmanagement.edit&id=' . (int) $item->id); ?>
                    <tr>
                        <td class="text-center"><?php echo (int) $item->id; ?></td>
                        <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, (int) $item->id); ?></td>
                        <td>
                            <?php if ($this->canEdit) : ?>
                                <a href="<?php echo $editUrl; ?>"><?php echo $this->escape((string) $item->greeting); ?></a>
                            <?php else : ?>
                                <?php echo $this->escape((string) $item->greeting); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($this->pagination) : ?>
        <?php echo $this->pagination->getListFooter(); ?>
    <?php endif; ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
