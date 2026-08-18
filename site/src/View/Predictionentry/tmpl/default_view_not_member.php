<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div class="alert alert-info">
    <p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_01'); ?></p>
    <p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_02'); ?></p>
    <p><?php echo Text::sprintf(
        'COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_03',
        htmlspecialchars((string) ($this->config['ownername'] ?? ''), ENT_QUOTES, 'UTF-8'),
        '<b>' . htmlspecialchars($this->websiteName, ENT_QUOTES, 'UTF-8') . '</b>'
    ); ?></p>
</div>

<?php if ($this->isNotApprovedMember) : ?>
    <div class="alert alert-warning">
        <p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_04'); ?></p>
        <p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_05'); ?></p>
    </div>
<?php else : ?>
    <p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_06'); ?></p>
    <form method="post" action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>">
        <button type="submit" class="btn btn-primary" name="task" value="predictionentry.register">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_07'); ?>
        </button>
        <input type="hidden" name="option" value="com_sportsmanagement">
        <input type="hidden" name="view" value="predictionentry">
        <input type="hidden" name="prediction_id" value="<?php echo (int) $this->predictionGameID; ?>">
        <input type="hidden" name="pj" value="<?php echo (int) $this->projectID; ?>">
        <input type="hidden" name="r" value="<?php echo (int) $this->roundID; ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
<?php endif; ?>
