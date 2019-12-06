<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      gennode.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage treeto
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;


$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<form method="post" name="adminForm" id="adminForm" class="">
<?PHP
//
//if(version_compare(JVERSION,'3.0.0','ge')) 
//{
//echo $this->loadTemplate('joomla3');
//}
//else
//{
//echo $this->loadTemplate('joomla2');    
//}


echo $this->loadTemplate('data');



?>

	<input type="hidden" name="pid" 	value='<?php echo $this->projectws->id; ?>' />
	<input type="hidden" name="id" 			value='<?php echo $this->treeto->id; ?>' />
	<input type="hidden" name="task" 		value="treeto.generatenode" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>  
