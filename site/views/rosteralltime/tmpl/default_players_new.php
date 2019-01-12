<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_players_new.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage rosteralltime
 */


defined('_JEXEC') or die('Restricted access');

echo ' <br><pre>'.print_r($this->items,true).'</pre>';

?>

<div class="pagination">
<p class="counter">
<?php echo $this->pagination->getPagesCounter(); ?>
</p>
<p class="counter">
<?php echo $this->pagination->getResultsCounter(); ?>
</p>
<?php echo $this->pagination->getPagesLinks(); ?>
</div>