<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_footer.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage footer
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

JLoader::import('components.com_sportsmanagement.helpers.html', JPATH_SITE);


$view = $this->jinput->getVar("view");
$view = ucfirst(strtolower($view));
$cfg_help_server = ComponentHelper::getParams($this->jinput->getCmd('option'))->get('cfg_help_server', '');
$cfg_bugtracker_server = ComponentHelper::getParams($this->jinput->getCmd('option'))->get('cfg_bugtracker_server', '');
?>

<div id="j-main-container" class="j-toggle-main span12 center" >    

	<a title= "<?php echo Text::_('COM_SPORTSMANAGEMENT_SITE_LINK') ?>" target= "_blank" href="http://www.fussballineuropa.de">
		<img src= "<?php echo Uri::base(true) ?>/components/com_sportsmanagement/assets/icons/logo_transparent.png"               width="180" height="auto" </a>          
	<br />
<?php echo Text::_("COM_SPORTSMANAGEMENT_DESC"); ?>
	<br />    
	<?php echo Text::_("COM_SPORTSMANAGEMENT_COPYRIGHT"); ?> : &copy;
	<a href="http://www.fussballineuropa.de" target="_blank">Fussball in Europa</a>
	<br />    
	<?php echo Text::_("COM_SPORTSMANAGEMENT_VERSION"); ?> :     
<?php echo Text::sprintf('%1$s', sportsmanagementHelper::getVersion()); ?>
	<br />

</div>      
