<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div class="container-popup">
    <form action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>" method="post" id="addissue-form" name="adminForm">
        <fieldset class="options-form">
            <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_ADD_ISSUE'); ?></legend>

            <?php if (!$this->hasConfiguredToken) : ?>
                <div class="control-group">
                    <label class="control-label" for="gh_token"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_TOKEN'); ?></label>
                    <div class="controls">
                        <input type="password" name="gh_token" id="gh_token" value="" class="form-control" autocomplete="off" required>
                    </div>
                </div>
            <?php endif; ?>

            <div class="control-group">
                <label class="control-label" for="labels"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_LABELS'); ?></label>
                <div class="controls"><?php echo $this->lists['labels']; ?></div>
            </div>

            <div class="control-group">
                <label class="control-label" for="milestones"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_MILESTONE'); ?></label>
                <div class="controls"><?php echo $this->lists['milestones']; ?></div>
            </div>

            <div class="control-group">
                <label class="control-label" for="title"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_TITLE'); ?></label>
                <div class="controls">
                    <input
                        type="text"
                        name="title"
                        id="title"
                        value="<?php echo htmlspecialchars((string) $this->issuetitle, ENT_QUOTES, 'UTF-8'); ?>"
                        class="form-control"
                        maxlength="256"
                        required
                    >
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="message"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_MESSAGE'); ?></label>
                <div class="controls">
                    <textarea id="message" name="message" rows="10" class="form-control" required></textarea>
                </div>
            </div>

            <div class="btn-toolbar mt-3">
                <button type="submit" class="btn btn-primary" name="task" value="github.addissue">
                    <?php echo Text::_('JSAVE'); ?>
                </button>
                <button type="submit" class="btn btn-secondary" name="task" value="github.cancel" formnovalidate>
                    <?php echo Text::_('JCANCEL'); ?>
                </button>
            </div>
        </fieldset>

        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
