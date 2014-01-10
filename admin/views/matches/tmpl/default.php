<?php 
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$massadd=JRequest::getInt('massadd',0);
$templatesToLoad = array('footer');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
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
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   