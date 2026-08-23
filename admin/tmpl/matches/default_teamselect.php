<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

if (empty($this->lists['projectteams'])) {
    return;
}
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=matches&pid=' . (int) $this->project_id); ?>"
    method="post"
    name="projectteamForm"
    id="projectteamForm"
    class="mb-3"
>
    <?php echo HTMLHelper::_(
        'select.genericlist',
        $this->lists['projectteams'],
        'projectteam',
        'class="form-select" onchange="this.form.submit();"',
        'value',
        'text',
        $this->projectteamsel
    ); ?>
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
