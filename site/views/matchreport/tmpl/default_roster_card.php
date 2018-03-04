<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_roster_card.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.modal');
use Joomla\CMS\HTML\HTMLHelper;

?>
<!-- START: game roster -->
<!-- Show Match players -->
<?php
if (!empty($this->matchplayerpositions))
{
?>

<?php
}
?>
<!-- END of Match players -->
<br />

<!-- END: game roster -->
