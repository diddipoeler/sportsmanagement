<?php 
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$massadd=JRequest::getInt('massadd',0);
?>

<div id="alt_decision_enter" style="display:<?php echo ($massadd == 0) ? 'none' : 'block'; ?>">
<?php 
echo $this->loadTemplate('massadd'); 
?>
</div>
<?php 
echo $this->loadTemplate('matches'); 
?>	
<?php 
echo $this->loadTemplate('matrix'); 
?>
