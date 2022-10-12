<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage databasetools
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
    <form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">

		<?PHP
        echo $this->loadTemplate('joomla_version');
        /*
		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			echo $this->loadTemplate('joomla3');
		}
		else
		{
			echo $this->loadTemplate('joomla2');
		}
        */
		?>



        <input type="hidden" name="task" value="databasetool.execute"/>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
<?PHP

echo $this->loadTemplate('footer');

