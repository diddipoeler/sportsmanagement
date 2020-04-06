<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage jlxmlexports
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<div class="row-fluid">	
<?PHP
echo 'exportSystem '.$this->exportSystem;
?>
</div>
<input type="hidden" name="task" value="" />
<?php echo HTMLHelper::_('form.token')."\n"; ?>
<?php echo $this->table_data_div; ?>
</form>
<div>
<?PHP
echo $this->loadTemplate('footer');
?>
</div>
