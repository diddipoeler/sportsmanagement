<?php
/**
 * @category	Core
 * @package		
 * @copyright (C) 2013
 * @license		GNU/GPL, see LICENSE.php
 */
 
;##################################################################
;/* 
;* Modified by 
;*  
;* email: 
;* date: 2013
;* Release: 1.0
;* License : http://www.gnu.org/copyleft/gpl.html GNU/GPL 
;*/
################################################################### 
 
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<table width="100%" border="0">
	<tr>
		<td width="100%" valign="top">
			<div id="cpanel">
				<?php echo $this->addIcon('tippspiel.png','index.php?option=com_sportsmanagement&view=predictiongames', JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_GAMES'));?>
                <?php echo $this->addIcon('tippspielgruppen.png','index.php?option=com_sportsmanagement&view=predictiongroups', JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_GROUPS'));?>
                <?php echo $this->addIcon('tippspielmitglieder.png','index.php?option=com_sportsmanagement&view=predictionmembers', JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_MEMBERS'));?>
                <?php echo $this->addIcon('tippspieltemplates','index.php?option=com_sportsmanagement&view=predictiontemplates', JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_TEMPLATES'));?>

			</div>
		</td>
		
	</tr>
	
</table>
