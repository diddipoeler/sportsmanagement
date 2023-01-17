<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage updates
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<style>
    /*  #myModal1 .modal-dialog {
		width: 80%;
	  }
	*/

    /*
	.modaljsm {
		width: 80%;
		height: 60%;
	  }
	  */
    .modal-dialog {
        width: 80%;
    }

    .modal-dialog,
    .modal-content {
        /* 95% of window height */
        height: 95%;
    }


</style>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<?PHP
echo $this->loadTemplate('joomla_version');
?>
<input type="hidden" name="view" value="updates"/>
<input type="hidden" name="task" value=""/>
<input type="hidden" name="boxchecked" value="0"/>
<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>"/>
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>"/>
<?php echo HTMLHelper::_('form.token') . "\n"; ?>
</form>

<?PHP
echo $this->loadTemplate('footer');
?> 
