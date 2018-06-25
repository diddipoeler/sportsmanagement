<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      mod_sportsmanagement_sports_type_statistics.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_sports_type_statistics
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if ( !defined('DS') )
{
    define('DS', DIRECTORY_SEPARATOR);
}

if ( !defined('JSM_PATH') )
{
    DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}
if ( !class_exists('JSMModelList') )
{
	// muss momentan eingebunden werden, ansonsten 500er seit update von model sportstypes
    $classpath = JPATH_ADMINISTRATOR . DS . 'components/com_sportsmanagement' . DS . 'libraries' . DS . 'sportsmanagement' . DS . 'model.php';
    JLoader::register('JSMModelList', $classpath);
}
// prüft vor Benutzung ob die gewünschte Klasse definiert ist
if ( !class_exists('sportsmanagementHelper') )
{
//add the classes for handling
    $classpath = JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'helpers' . DS . 'sportsmanagement.php';
    JLoader::register('sportsmanagementHelper', $classpath);
    JModelLegacy::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

// get helper
require_once( dirname(__FILE__) . DS . 'helper.php' );
$parameter   = $params->get('sportstypes');
$sportstypes = JText::_($params->get('sportstypes'));
$data        = modJSMSportsHelper::getData($params);

$document = JFactory::getDocument();

//add css file
//$document->addStyleSheet(JURI::base().'modules/mod_sportsmanagement_sports_type_statistics/css/mod_sportsmanagement_sports_type_statistics.css');

/**
 * wenn die komponente im frontend nicht geladen oder aufgerufen wurde,
 * dann muss die sprachdatei aus dem backend geladen werden.
 * ansonsten wird der übersetzte text der sportart nicht angezeigt.
 */
if ( !defined($data['sportstype'][0]->name) )
{
    $langtag      = JFactory::getLanguage();
    $extension    = 'com_sportsmanagement';
    $base_dir     = JPATH_ADMINISTRATOR;
    $language_tag = $langtag->getTag();
    $reload       = true;
    $lang->load($extension, $base_dir, $language_tag, $reload);
}

?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require(JModuleHelper::getLayoutPath($module->module));
?>
</div>
