<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionmembers
 */

defined('_JEXEC') or die('Restricted access');

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

JHtml::_( 'behavior.tooltip' );
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
	

<?PHP
echo $this->loadTemplate('joomla_version');
?>		
	
  <input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked"			value="0" />
    <input type="hidden" name="prediction_id"		value="<?php echo $this->prediction_id; ?>" />
	<input type="hidden" name="filter_order"		value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir"	value="<?php echo $this->sortDirection; ?>" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
