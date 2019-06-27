<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage joomleagueimport
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
?>
      
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<p class="nowarning"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_INPROGRESS') ?></p>
<div class="joomlaupdate_spinner" ></div>

<div id="progressbar">
<div class="progress-label">
<?php echo $this->task; ?>
</div>
</div>

<input type="hidden" name="step" value="<?php echo $this->step; ?>" />
<input type="hidden" name="totals" value="<?php echo $this->totals; ?>" />


<?PHP
//echo 'step -> '.$this->work_table.'<br>';

if ( $this->bar_value < 100)
{
echo '<meta http-equiv="refresh" content="1; URL='.$this->request_url.'">';
}
?>

</form>  

  