<?php
/** Native Joomla 5/6 GitHub update download form. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&task=githubinstall.CopyGithubLink'); ?>"
    method="post"
    id="adminForm"
    name="adminForm"
>
    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h5 card-title"><?php echo Text::_('COM_SPORTSMANAGEMENT_GITHUBINSTALL'); ?></h2>
            <?php if ($this->github_link !== '') : ?>
                <p class="card-text text-break"><?php echo $this->escape($this->github_link); ?></p>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-download" aria-hidden="true"></span>
                    <?php echo Text::_('COM_SPORTSMANAGEMENT_GITHUB_UPDATE'); ?>
                </button>
            <?php else : ?>
                <div class="alert alert-warning mb-0">
                    <?php echo Text::_('COM_SPORTSMANAGEMENT_GITHUBINSTALL'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php echo HTMLHelper::_('form.token'); ?>
</form>
