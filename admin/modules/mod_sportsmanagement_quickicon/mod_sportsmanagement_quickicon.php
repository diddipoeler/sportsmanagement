<?php defined('_JEXEC') or die('Restricted Access'); // Protect from unauthorized access
/**
* @package	 	Joomla
* @subpackage  	Sports Management Quickicon
* @copyright	Copyright (C) 2014 JSM. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
// Make sure Sportsmanagement is enabled
jimport( 'joomla.application.component.helper' );
if ( !JComponentHelper::isEnabled( 'com_sportsmanagement', true) )
{
	JError::raiseError( 'E_SMNOTENABLED', JText( 'SM_NOT_ENABLED' ) );
	return;
}
?>
<div id="cpanel">	     
  <div class="icon-wrapper">      
    <div class="icon">           
      <a title="<?php echo JText::_('MOD_SPORTSMANAGEMENT_QUICKICON_PANEL_LINK')?>" href="index.php?option=com_sportsmanagement">               
        <img src="<?php echo JUri::base( false ) ?>/components/com_sportsmanagement/assets/icons/transparent_schrift_48.png">               
        <span>            
          <?php echo JText::_('MOD_SPORTSMANAGEMENT_QUICKICON_PANEL_LABEL')?>               
        </span></a>		        
    </div>    
  </div>    
  <div class="icon-wrapper">      
    <div class="icon">           
      <a title="<?php echo JText::_('MOD_SPORTSMANAGEMENT_QUICKICON_EXTENSIONS_LINK')?>" href="index.php?option=com_sportsmanagement&view=extensions">               
        <img src="<?php echo JUri::base( false ) ?>/components/com_sportsmanagement/assets/icons/extensions.png">               
        <span>            
          <?php echo JText::_('MOD_SPORTSMANAGEMENT_QUICKICON_EXTENSIONS_LABEL')?>               
        </span></a>		        
    </div>    
  </div>    
  <div class="icon-wrapper">      
    <div class="icon">           
      <a title="<?php echo JText::_('MOD_SPORTSMANAGEMENT_QUICKICON_PROJECTS_LINK')?>" href="index.php?option=com_sportsmanagement&view=projects">               
        <img src="<?php echo JUri::base( false ) ?>/components/com_sportsmanagement/assets/icons/projekte.png">               
        <span>            
          <?php echo JText::_('MOD_SPORTSMANAGEMENT_QUICKICON_PROJECTS_LABEL')?>               
        </span></a>		        
    </div>    
  </div>    
  <div class="icon-wrapper">      
    <div class="icon">           
      <a title="<?php echo JText::_('MOD_SPORTSMANAGEMENT_QUICKICON_PREDICTIONS_LINK')?>" href="index.php?option=com_sportsmanagement&view=predictions">               
        <img src="<?php echo JUri::base( false ) ?>/components/com_sportsmanagement/assets/icons/tippspiele.png">               
        <span>            
          <?php echo JText::_('MOD_SPORTSMANAGEMENT_QUICKICON_PREDICTIONS_LABEL')?>               
        </span></a>		        
    </div>    
  </div>    
  <div class="icon-wrapper">      
    <div class="icon">           
      <a title="<?php echo JText::_('MOD_SPORTSMANAGEMENT_QUICKICON_CURRENT_SAISON_LINK')?>" href="index.php?option=com_sportsmanagement&view=currentseasons">               
        <img src="<?php echo JUri::base( false ) ?>/components/com_sportsmanagement/assets/icons/aktuellesaison.png">               
        <span>            
          <?php echo JText::_('MOD_SPORTSMANAGEMENT_QUICKICON_CURRENT_SAISON_LABEL')?>               
        </span></a>		        
    </div>    
  </div>	    
</div>