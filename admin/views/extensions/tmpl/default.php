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
				<?php 
                
                foreach ( $this->sporttypes as $key => $value )
                {
                    //echo $value . '<br>';
                    switch ($value)
                    {
                        case 'soccer':
                        echo $this->addIcon('dfbnetimport.png','index.php?option=com_sportsmanagement&view=configuration', JText::_('COM_SPORTSMANAGEMENT_EXT_DFBNETIMPORT'));
                        echo $this->addIcon('dfbschluessel.png','index.php?option=com_sportsmanagement&view=configuration', JText::_('COM_SPORTSMANAGEMENT_EXT_DFBKEY'));
                        echo $this->addIcon('lmoimport.png','index.php?option=com_sportsmanagement&view=configuration', JText::_('COM_SPORTSMANAGEMENT_EXT_LMO_IMPORT'));
                        break;
                        case 'basketball':
                        echo $this->addIcon('dbbimport.png','index.php?option=com_sportsmanagement&view=configuration', JText::_('COM_SPORTSMANAGEMENT_EXT_DBB_IMPORT'));
                        break;
                        case 'handball':
                        echo $this->addIcon('sisimport.png','index.php?option=com_sportsmanagement&view=configuration', JText::_('COM_SPORTSMANAGEMENT_EXT_SIS_IMPORT'));
                        break;
                        default:
                        break;
                        
                    }
                    
                    
                    
                }
                
                
                
                
                ?>
        		
                
			</div>
		</td>
		
	</tr>
	
</table>
