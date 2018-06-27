<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_stats.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage statsrankingteams
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<table class="<?php	echo $this->config['table_class'];	?>">
<thead>
<tr class="">
<th class="td_r rank"><?php	echo JText::_( 'COM_SPORTSMANAGEMENT_STATSRANKING_RANK' );	?></th>  
<th class="td_l"><?php	echo JText::_( 'COM_SPORTSMANAGEMENT_STATSRANKING_TEAM' );	?></th>  
<?php  
foreach ( $this->stats AS $rows )
{
?>  
<th class="td_r" class="nowrap"><?php	echo JText::_($rows->name); ?></th>
<?php  
}
  
  
?>  
</tr>
</thead>  
</table>  
