<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage treetos
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
    <form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
		<?PHP
        echo $this->loadTemplate('joomla_version');
		echo $this->loadTemplate('data');
		?>
        <input type="hidden" name="project_id" value="<?php echo $this->projectws->id; ?>"/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="view" value="treetos"/>
        <input type="hidden" name="task" value="treeto.display"/>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
<div>
<?PHP
echo $this->loadTemplate('footer');
?>
</div>