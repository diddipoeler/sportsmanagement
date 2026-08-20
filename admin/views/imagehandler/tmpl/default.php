<?php
/** SportsManagement administrator image selector. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<div class="container-fluid">
    <form action="<?php echo $this->escape($this->request_url); ?>" method="post" id="adminForm" name="adminForm">
        <div class="d-flex gap-2 align-items-center mb-3">
            <label for="search" class="visually-hidden"><?php echo Text::_('JSEARCH_FILTER_LABEL'); ?></label>
            <input type="search" name="search" id="search" value="<?php echo $this->escape($this->search); ?>"
                   class="form-control" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>">
            <button type="submit" class="btn btn-primary"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" class="btn btn-secondary"
                    onclick="document.getElementById('search').value='';this.form.submit();">
                <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
            </button>
        </div>

        <div class="imglist d-flex flex-wrap gap-3">
            <?php for ($i = 0, $n = count($this->images); $i < $n; $i++) : ?>
                <?php $this->setImage($i); ?>
                <?php echo $this->loadTemplate('image'); ?>
            <?php endfor; ?>
        </div>

        <div class="mt-3"><?php echo $this->pageNav->getListFooter(); ?></div>

        <input type="hidden" name="option" value="com_sportsmanagement">
        <input type="hidden" name="view" value="imagehandler">
        <input type="hidden" name="tmpl" value="component">
        <input type="hidden" name="task" value="">
        <input type="hidden" name="folder" value="<?php echo $this->escape($this->folder); ?>">
        <input type="hidden" name="type" value="<?php echo $this->escape($this->type); ?>">
        <input type="hidden" name="field" value="<?php echo $this->escape($this->field); ?>">
        <input type="hidden" name="fieldid" value="<?php echo $this->escape($this->fieldid); ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
