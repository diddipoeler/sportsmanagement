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
				<?php echo $this->addIcon('dfb-key.jpg','index.php?option=com_sportsmanagement&view=configuration', JText::_('COM_SPORTSMANAGEMENT_EXT_DFBKEY'));?>
        		
                <?php echo $this->addIcon('dbb.png','index.php?option=com_sportsmanagement&view=configuration', JText::_('COM_SPORTSMANAGEMENT_EXT_DBB_IMPORT'));?>
                <?php echo $this->addIcon('lmo.jpg','index.php?option=com_sportsmanagement&view=configuration', JText::_('COM_SPORTSMANAGEMENT_EXT_LMO_IMPORT'));?>
                <?php echo $this->addIcon('sis.jpg','index.php?option=com_sportsmanagement&view=configuration', JText::_('COM_SPORTSMANAGEMENT_EXT_SIS_IMPORT'));?>

			</div>
		</td>
		
	</tr>
	
</table>
