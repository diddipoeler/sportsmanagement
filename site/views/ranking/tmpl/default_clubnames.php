<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 *
 * @version    1.0.05
 * @file       default_clubnames.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage ranking
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
?>
<div class="<?php echo $this->divclassrow;?>" id="projectclubnames">
<h4>
    <?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_CLUBNAMES'); ?>
</h4>
<?php
if ($this->clubnames) {
?>
<div class="panel-group" id="countresult">
<div class="panel panel-default">
<div class="panel-heading">
<h4 class="panel-title">
<a class="" data-toggle="collapse" data-parent="#countresult" href="#collapseprojectclubnames" >
<?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_CLUBNAMES'); ?>
</a>
</h4>
</div>
<div class="collapse" id="collapseprojectclubnames">
<div class="card card-body">

<table class="<?PHP echo $this->config['table_class']; ?>">  
<?php  
foreach ( $this->clubnames as $key => $value )
{
?>
<tr>
<td>
<?php echo $value->name; ?>  
</td>  
<td>
<?php echo $value->name_long; ?>  
</td>  
</tr>  
<?php  
} 
?>  
</table>  
</div>
</div>
</div>
</div>
<?php  
} else {
?>
<div class="alert alert-warning" role="alert">
<?PHP
echo Text::_('COM_SPORTSMANAGEMENT_NO_RANKING_CLUBNAMES');
?>
</div>
<?PHP
}
?>
</div>
