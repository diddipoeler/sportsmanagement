<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage updates
 * @file       jsm_update_github.php
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


?>

<form enctype="multipart/form-data" action="<?php echo Route::_('index.php?option=com_installer&view=install&tmpl=component'); ?>" method="post" name="adminForm" id="adminForm">

<fieldset class="uploadform option-fieldset options-form">
<legend>Update vom Verzeichnis</legend>

<div class="control-group">
    <label for="install_directory" class="control-label">
        Update vom Verzeichnis    </label>
    <div class="controls">
        <input type="text" id="install_directory" name="install_directory" class="form-control" value="">
    </div>
</div>
<div class="control-group">
    <div class="controls">
        <button type="button" class="btn btn-primary" id="installbutton_directory" onclick="this.form.submit()">
            Überprüfen und installieren        </button>
    </div>
</div>
</fieldset>


<input type="hidden" name="installtype" value="directory">
<input type="hidden" name="task" value="install.install">
<?php echo HTMLHelper::_('form.token'); ?>


</form>

<div id="loading"></div>

