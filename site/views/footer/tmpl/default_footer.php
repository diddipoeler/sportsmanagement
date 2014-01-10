<?php 
defined('_JEXEC') or die('Restricted access');
JHTML::_( 'behavior.modal' );

$option = JRequest::getCmd('option');
$view = JRequest::getVar( "view") ;
$view = ucfirst(strtolower($view));
$cfg_help_server = JComponentHelper::getParams($option)->get('cfg_help_server','') ;
$modal_popup_width = JComponentHelper::getParams($option)->get('modal_popup_width',0) ;
$modal_popup_height = JComponentHelper::getParams($option)->get('modal_popup_height',0) ;
	
//if (JComponentHelper::getParams('com_sportsmanagement')->get('show_footer',0))
//{
?>
	

<div style="text-align:center; clear:both">      
      
      <br />      
      
              <a title= "<?php echo JText::_('COM_SPORTSMANAGEMENT_SITE_LINK')?>" target= "_blank" href="http://www.fussballineuropa.de">
                <img src= "<?php echo JURI::base( true ) ?>/administrator/components/com_sportsmanagement/assets/icons/logo_transparent.png"               width="180" height="auto"</a>            
      <br />
      <?php echo JText::_( "COM_SPORTSMANAGEMENT_DESC" ); ?>
      <br />      
      <?php echo JText::_( "COM_SPORTSMANAGEMENT_COPYRIGHT" ); ?> : &copy; 
      <a href="http://www.fussballineuropa.de" target="_blank">Fussball in Europa</a>
      <br />      
      <?php echo JText::_( "COM_SPORTSMANAGEMENT_VERSION" ); ?> :       
      <?php 
      //echo JText::sprintf( '%1$s', sportsmanagementHelper::getVersion() ); 
      echo JHTML::link('index.php?option='.$option.'&amp;view=about',sprintf('Version %1$s (diddipoeler)',sportsmanagementHelper::getVersion()));
      ?>
      <br />      
      <?php
      echo 'Hilfeseite ';
			$send = '<a class="modal" rel="{handler: \'iframe\', size: {x: '.$modal_popup_width.', y: '.$modal_popup_height.'}}" '.
         ' href="'.$cfg_help_server.'SM-Frontend:'.$view.'">'.JText::_('Onlinehilfe').'</a>';
//$send="<a href=\"".$cfg_help_server."Frontend:".$view."\" target=\"_blank\" onclick=\"window.open(this.href,this.target,'width=".$modal_popup_width.",height=".$modal_popup_height."'); return false;\">Popup öffnen</a>";             
      echo $send;   
			?>     
    </div>   
            
<?php
//}
?>