<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       mod_sportsmanagement_sports_type_statistics.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_sports_type_statistics
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JSM_PATH')) {
    DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}
if (!class_exists('JSMModelList')) {
    // muss momentan eingebunden werden, ansonsten 500er seit update von model sportstypes
    $classpath = JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components/com_sportsmanagement' .DIRECTORY_SEPARATOR. 'libraries' .DIRECTORY_SEPARATOR. 'sportsmanagement' .DIRECTORY_SEPARATOR. 'model.php';
    JLoader::register('JSMModelList', $classpath);
}
// prüft vor Benutzung ob die gewünschte Klasse definiert ist
if (!class_exists('sportsmanagementHelper')) {
    //add the classes for handling
    $classpath = JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. JSM_PATH .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'sportsmanagement.php';
    JLoader::register('sportsmanagementHelper', $classpath);
    BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

/**
* 
 * Include the functions only once 
*/
JLoader::register('modJSMSportsHelper', __DIR__ . '/helper.php');

$parameter = $params->get('sportstypes');
$sportstypes = Text::_($params->get('sportstypes'));
$data = modJSMSportsHelper::getData($params);

$document = Factory::getDocument();

//add css file
//$document->addStyleSheet(Uri::base().'modules/mod_sportsmanagement_sports_type_statistics/css/mod_sportsmanagement_sports_type_statistics.css');

/**
 * wenn die komponente im frontend nicht geladen oder aufgerufen wurde,
 * dann muss die sprachdatei aus dem backend geladen werden.
 * ansonsten wird der übersetzte text der sportart nicht angezeigt.
 */
if (!defined($data['sportstype'][$sportstypes]->name)) {
    $langtag = Factory::getLanguage();
    $extension = 'com_sportsmanagement';
    $base_dir = JPATH_ADMINISTRATOR;
    $language_tag = $langtag->getTag();
    $reload = true;
    $lang->load($extension, $base_dir, $language_tag, $reload);
}

?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>"
     id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
    <?PHP
    require ModuleHelper::getLayoutPath($module->module);
    ?>
</div>
