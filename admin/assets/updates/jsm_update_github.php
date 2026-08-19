<?php
/** Joomla 5/6 folder-installer handoff for a downloaded SportsManagement archive. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$app = Factory::getApplication();
$app->getDocument()->getWebAssetManager()
    ->registerAndUseScript(
        'plg_installer_folderinstaller.folderinstaller',
        'plg_installer_folderinstaller/folderinstaller.js',
        [],
        ['defer' => true],
        ['core']
    );

$tmpPath = rtrim((string) $app->get('tmp_path'), '/\\');
$installDirectory = (string) $app->getUserState(
    'com_sportsmanagement.github_update_dir',
    $tmpPath . DIRECTORY_SEPARATOR . 'sportsmanagement-master'
);
?>
<form enctype="multipart/form-data"
      action="<?php echo Route::_('index.php?option=com_installer&view=install&tmpl=component'); ?>"
      method="post" name="adminForm" id="adminForm">
    <fieldset class="uploadform option-fieldset options-form">
        <legend>Update vom Verzeichnis</legend>

        <div class="control-group">
            <label for="install_directory" class="control-label">Update vom Verzeichnis</label>
            <div class="controls">
                <input type="text" id="install_directory" name="install_directory" class="form-control"
                       value="<?php echo htmlspecialchars($installDirectory, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <button type="button" class="btn btn-primary" id="installbutton_directory"
                        onclick="Joomla.submitbuttonfolder()">
                    Update installieren
                </button>
            </div>
        </div>
    </fieldset>

    <input type="hidden" name="installtype" value="folder">
    <input type="hidden" name="task" value="install.install">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<div id="loading"></div>
