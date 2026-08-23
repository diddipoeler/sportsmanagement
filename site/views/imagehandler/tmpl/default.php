<?php
/** Joomla 5/6 SportsManagement frontend image selector. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<form action="<?php echo $this->escape($this->request_url); ?>" method="post" id="adminForm" name="adminForm">
    <div class="imghead">
        <?php echo Text::_('JSEARCH_FILTER_LABEL') . ' '; ?>
        <input type="text"
               name="search"
               id="search"
               value="<?php echo $this->escape($this->search); ?>"
               class="text_area"
               onchange="document.getElementById('adminForm').submit();">
        <button type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
        <button type="submit" onclick="document.getElementById('search').value='';">
            <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
        </button>
    </div>

    <div class="imglist">
        <?php foreach (array_keys($this->images) as $index) : ?>
            <?php $this->setImage($index); ?>
            <?php echo $this->loadTemplate('image'); ?>
        <?php endforeach; ?>
    </div>

    <div class="clr"></div>

    <?php if ($this->pageNav !== null) : ?>
        <div class="pnav"><?php echo $this->pageNav->getListFooter(); ?></div>
    <?php endif; ?>

    <input type="hidden" name="option" value="com_sportsmanagement">
    <input type="hidden" name="view" value="imagehandler">
    <input type="hidden" name="tmpl" value="component">
    <input type="hidden" name="task" value="display">
    <input type="hidden" name="folder" value="<?php echo $this->escape($this->folder); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
