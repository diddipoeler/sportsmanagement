<?php
/** SportsManagement administrator image browser. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$link = 'index.php?option=com_sportsmanagement&view=imagehandler&layout=uploaddraganddrop'
    . '&type=' . rawurlencode((string) $this->type)
    . '&field=' . rawurlencode((string) $this->fieldname)
    . '&fieldid=' . rawurlencode((string) $this->fieldid)
    . '&tmpl=component&pid=' . (int) $this->pid
    . '&imagelist=' . (int) $this->imagelist
    . '&mid=' . (int) $this->mid;
?>
<div class="container-fluid" id="imageslist">
    <div class="mb-3">
        <?php
        echo sportsmanagementHelper::getBootstrapModalImage(
            'upload-image-browser',
            '',
            Text::_('JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE'),
            '20',
            Uri::base() . $link,
            $this->modalwidth,
            $this->modalheight
        );
        ?>
    </div>

    <form name="adminForm" id="adminForm" action="<?php echo $this->escape($this->uri->toString()); ?>" method="get">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <label for="limit" class="form-label mb-0"><?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?></label>
            <?php echo $this->pagination->getLimitBox(); ?>

            <label for="filter_search" class="visually-hidden"><?php echo Text::_('JSEARCH_FILTER_LABEL'); ?></label>
            <input type="search" name="filter_search" id="filter_search"
                   placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>"
                   value="<?php echo $this->escape($this->filter_search); ?>"
                   class="form-control w-auto">
            <button type="submit" class="btn btn-primary"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" class="btn btn-secondary"
                    onclick="document.getElementById('filter_search').value='';this.form.submit();">
                <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
            </button>
        </div>

        <div class="media-browser">
            <div class="media-browser-grid">
                <div class="media-browser-items media-browser-items-md">
                    <?php if ($this->images) : ?>
                        <?php for ($i = 0, $n = count($this->images); $i < $n; $i++) : ?>
                            <?php $this->setImage($i); ?>
                            <?php echo $this->loadTemplate('image'); ?>
                        <?php endfor; ?>
                    <?php else : ?>
                        <div id="media-noimages" class="alert alert-info">
                            <?php echo Text::_('COM_MEDIA_NO_IMAGES_FOUND'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
            <p class="counter"><?php echo $this->pagination->getResultsCounter(); ?></p>
            <?php echo $this->pagination->getPagesLinks(); ?>
        </div>

        <input type="hidden" name="limitstart" value="0">
        <input type="hidden" name="option" value="com_sportsmanagement">
        <input type="hidden" name="view" value="imagelist">
        <input type="hidden" name="imagelist" value="1">
        <input type="hidden" name="asset" value="com_sportsmanagement">
        <input type="hidden" name="folder" value="<?php echo $this->escape($this->folder); ?>">
        <input type="hidden" name="pid" value="<?php echo (int) $this->pid; ?>">
        <input type="hidden" name="mid" value="<?php echo (int) $this->mid; ?>">
        <input type="hidden" name="author" value="">
        <input type="hidden" name="fieldid" value="<?php echo $this->escape($this->fieldid); ?>">
        <input type="hidden" name="type" value="<?php echo $this->escape($this->type); ?>">
        <input type="hidden" name="fieldname" value="<?php echo $this->escape($this->fieldname); ?>">
        <input type="hidden" name="tmpl" value="component">
        <input type="hidden" name="club_id" value="<?php echo (int) $this->club_id; ?>">
        <input type="hidden" name="teamplayer_id" value="<?php echo (int) $this->teamplayer_id; ?>">
        <input type="hidden" name="player_id" value="<?php echo (int) $this->player_id; ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
