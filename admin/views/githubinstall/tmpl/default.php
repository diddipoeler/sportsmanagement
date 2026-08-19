<?php
/** Joomla 5/6 GitHub update download form. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$templatesToLoad = ['footer'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<form action="<?php echo htmlspecialchars($this->request_url, ENT_QUOTES, 'UTF-8'); ?>"
      method="post" id="adminForm" name="adminForm">
    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h5 card-title"><?php echo Text::_('COM_SPORTSMANAGEMENT_GITHUBINSTALL'); ?></h2>
            <p class="card-text">
                <?php echo htmlspecialchars((string) $this->github_link, ENT_QUOTES, 'UTF-8'); ?>
            </p>
            <button type="submit" class="btn btn-primary">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_GITHUB_UPDATE'); ?>
            </button>
        </div>
    </div>

    <input type="hidden" name="task" value="githubinstall.CopyGithubLink">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php echo $this->loadTemplate('footer'); ?>
