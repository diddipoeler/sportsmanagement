<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      mod_sportsmanagement_playgroundplan.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_playgroundplan
 */


// no direct access
defined('_JEXEC') or die('Restricted access');

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if ( !defined('JSM_PATH') )
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

// prüft vor Benutzung ob die gewünschte Klasse definiert ist
if ( !class_exists('sportsmanagementHelper') ) 
{
//add the classes for handling
$classpath = JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php';
JLoader::register('sportsmanagementHelper', $classpath);
JModelLegacy::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'route.php');

// get helper
require_once (dirname(__FILE__).DS.'helper.php');


$list = modSportsmanagementPlaygroundplanHelper::getData($params);

$document = JFactory::getDocument();

//add css file
$document->addStyleSheet(JURI::base().'modules'.DS.$module->module.DS.'css'.DS.$module->module.'.css');

$mode = $params->def("mode");

switch ($mode)
	{
	case 0:
		$document->addScript(JURI::base().'modules'.DS.$module->module.DS.'js/qscroller.js');
		require_once(dirname(__FILE__).DS.'js'.DS.'ticker.js');
		break;
	case 1:
		break;
}

?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require(JModuleHelper::getLayoutPath($module->module));
?>
</div>