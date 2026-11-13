<?php
/**
 * Native Joomla 5/6 administrator SportsManagement Season edit layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
$this->getDocument()->getWebAssetManager()->useScript('keepalive');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=season&layout=edit&id=' . (int) ($this->item->id ?? 0)); ?>"
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-validate">
    <?php echo HTMLHelper::_('uitab.startTabSet', 'season-edit-tabs', ['active' => 'details']); ?>
    <?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
        <?php echo HTMLHelper::_(
            'uitab.addTab',
            'season-edit-tabs',
            $fieldset->name,
            Text::_($fieldset->label ?: $fieldset->name)
        ); ?>
        <fieldset class="options-form mb-4">
            <?php foreach ($this->form->getFieldset($fieldset->name) as $field) : ?>
                <?php echo $field->renderField(); ?>
            <?php endforeach; ?>
        </fieldset>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endforeach; ?>
    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
