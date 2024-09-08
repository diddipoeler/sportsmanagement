<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage updates
 * @file       jsm_install_jcomments.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Updater\Update;
use Joomla\CMS\Updater\Updater;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;

//PluginHelper::importPlugin('plg_installer_webinstaller');
Factory::getDocument()->addScript(Uri::root() .'media\plg_installer_webinstaller\js\client.js');



$uri = Uri::getInstance();

//$table = Factory::getApplication()->input->getVar('table');
//$uri->delVar('table');
//$link = $uri->toString();

?>
    <script type="text/javascript">
    function  submitButtonUrl() {
      const form = document.getElementById('adminForm'); // do field validation

      if (form.install_url.value === '' || form.install_url.value === 'http://' || form.install_url.value === 'https://') {
        Joomla.renderMessages({
          warning: [Joomla.Text._('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL')]
        });
      } else {
        const loading = document.getElementById('loading');

        if (loading) {
          loading.classList.remove('hidden');
        }

        form.installtype.value = 'url';
        form.submit();
      }
    }

        function sendData(sData) {
            var oldLocation = '<?PHP echo $link;?>';
            window.location = oldLocation + '&table=' + sData;
//  window.location.search = sData;
//  window.location.reload(true)
        }
    </script>
<?PHP

$version           = '4.0.33';

$minor = 0;
$major = 0;
$build = 0;
$revision = '';

$updateFileDate    = '2016-02-01';
$updateFileTime    = '00:05';
$updateDescription = '<span style="color:orange">'.Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UPDATES_ALIAS').'</span>';
$excludeFile       = 'false';

$maxImportTime = ComponentHelper::getParams('com_sportsmanagement')->get('max_import_time', 0);

if (empty($maxImportTime))
{
	$maxImportTime = 880;
}


if ((int) ini_get('max_execution_time') < $maxImportTime)
{
	@set_time_limit($maxImportTime);
}

$maxImportMemory = ComponentHelper::getParams('com_sportsmanagement')->get('max_import_memory', 0);

if (empty($maxImportMemory))
{
	$maxImportMemory = '150M';
}


if ((int) ini_get('memory_limit') < (int) $maxImportMemory)
{
	ini_set('memory_limit', $maxImportMemory);
}

$db = sportsmanagementHelper::getDBConnection();

$app  = Factory::getApplication();
//$tabs = $app->triggerEvent('onInstallerAddInstallationTab', []);
//echo 'tabs <pre>'.print_r($tabs,true).'</pre>';
?>

<form enctype="multipart/form-data" action="<?php echo Route::_('index.php?option=com_installer&view=install&tmpl=component'); ?>" method="post" name="adminForm" id="adminForm">

<fieldset class="uploadform option-fieldset options-form">
<legend>Von URL installieren</legend>

<div class="control-group">
    <label for="install_url" class="control-label">
        Von URL installieren    </label>
    <div class="controls">
        <input type="text" id="install_url" name="install_url" class="form-control" value="https://github.com/exstreme/Jcomments-4/releases/download/v4.0.33/pkg_jcomments_4.0.33.zip">
    </div>
</div>
<div class="control-group">
    <div class="controls">
        <button type="button" class="btn btn-primary" id="installbutton_url" onclick="this.form.submit()">
            Überprüfen und installieren        </button>
    </div>
</div>
</fieldset>


<input type="hidden" name="installtype" value="url">
<input type="hidden" name="task" value="install.install">
<?php echo HTMLHelper::_('form.token'); ?>


</form>

<div id="loading"></div>
















