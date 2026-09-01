<?php
/** Joomla 5/6 Google calendars table rows. */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th scope="col" class="w-1"><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_NAME_ID_LABEL'); ?></th>
                <th scope="col" class="w-1 text-center">
                    <input class="form-check-input" type="checkbox" name="checkall-toggle" value=""
                           title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)">
                </th>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_NAME_LABEL'); ?></th>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_COLOR_LABEL'); ?></th>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_CALENDAR_ID_LABEL'); ?></th>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_GCALENDARS_COLUMN_AUTHENTICATION'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) : ?>
            <?php
            $color = (string) ($item->color ?? '');
            $color = preg_match('/^#[0-9a-fA-F]{3,8}$/', $color) ? $color : 'transparent';
            ?>
            <tr>
                <td><?php echo (int) $item->id; ?></td>
                <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, (int) $item->id); ?></td>
                <td>
                    <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=jsmgcalendar.edit&id=' . (int) $item->id); ?>">
                        <?php echo $this->escape((string) $item->name); ?>
                    </a>
                </td>
                <td>
                    <span class="d-inline-block border rounded" style="width:2rem;height:2rem;background-color:<?php echo $color; ?>"
                          aria-label="<?php echo $this->escape((string) ($item->color ?? '')); ?>"></span>
                </td>
                <td class="text-break"><?php echo $this->escape(urldecode((string) ($item->calendar_id ?? ''))); ?></td>
                <td>
                    <?php if (!empty($item->magic_cookie)) : ?>
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_MAGIC_COOKIE_LABEL'); ?>
                    <?php elseif (!empty($item->username)) : ?>
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_GCALENDARS_COLUMN_AUTHENTICATION_USERNAME'); ?>
                    <?php else : ?>
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_GCALENDARS_COLUMN_AUTHENTICATION_NO'); ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (!$this->items) : ?>
    <div class="alert alert-info"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div>
<?php endif; ?>

<div class="mt-3">
    <?php echo $this->pagination->getListFooter(); ?>
</div>
