<?php
/**
 * Joomla 5/6 folder-installer handoff for a downloaded SportsManagement archive.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

// Resolve the typed administrator application for Joomla 5/6. The updater
// can be included directly, before the component's namespace is registered.
if (!class_exists(SportsManagementAdministratorApplicationResolver::class)) {
    $resolverFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Service/SportsManagementAdministratorApplicationResolver.php';

    if (!is_file($resolverFile)) {
        throw new \\RuntimeException('SportsManagement administrator application resolver is missing.', 500);
    }

    require_once $resolverFile;
}

$app = SportsManagementAdministratorApplicationResolver::resolve();
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