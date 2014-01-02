<?php defined('_JEXEC') or die('Restricted access');
JHTML::_( 'behavior.modal' );

$view = JRequest::getVar( "view") ;
$view = ucfirst(strtolower($view));
$cfg_help_server = JComponentHelper::getParams('com_sportsmanagement')->get('cfg_help_server','') ;
$modal_popup_width = JComponentHelper::getParams('com_sportsmanagement')->get('modal_popup_width',0) ;
$modal_popup_height = JComponentHelper::getParams('com_sportsmanagement')->get('modal_popup_height',0) ;
	
if (JComponentHelper::getParams('com_sportsmanagement')->get('show_footer',0))
{
?>
	<br />
		<div align='center' style='font-size:9px;float:none;clear:both; '>
			<?php
			echo ' :: Extended Version Powered by ';
			echo JHTML::link('http://www.fussballineuropa.de','Fussball in Europa',array('target' => '_blank'));
			echo ' - ';
			echo JHTML::link('index.php?option=com_sportsmanagement&amp;view=about',sprintf('Version %1$s (diddipoeler)',JoomleagueHelper::getVersion()));
			echo ' :: ';
			echo ' :: Hilfeseite ';
			
			$send = '<a class="modal" rel="{handler: \'iframe\', size: {x: '.$modal_popup_width.', y: '.$modal_popup_height.'}}" '.
         ' href="'.$cfg_help_server.'Frontend:'.$view.'">'.JText::_('Onlinehilfe').'</a>';
//$send="<a href=\"".$cfg_help_server."Frontend:".$view."\" target=\"_blank\" onclick=\"window.open(this.href,this.target,'width=".$modal_popup_width.",height=".$modal_popup_height."'); return false;\">Popup öffnen</a>";             
      echo $send;   
			?>
		</div>
<?php
}
?>