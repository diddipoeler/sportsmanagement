<?php 


defined('_JEXEC') or die('Restricted access');
//$version = urlencode(JoomleagueHelper::getVersion());
//JHtml::script('JL_matchdetailsediting.js?v='.$version,'administrator/components/com_sportsmanagement/assets/js/');
JHtml::_('behavior.tooltip');

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>


<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<?PHP

if(version_compare(JVERSION,'3.0.0','ge')) 
{
echo $this->loadTemplate('joomla3');
}
else
{
echo $this->loadTemplate('joomla2');    
}

echo $this->loadTemplate('data');
?>			
<input type="hidden" name="project_id" value="<?php echo $this->projectws->id; ?>" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="view" value="treetos" />
<input type="hidden" name="task" value="treeto.display" />
<?php echo JHtml::_('form.token'); ?>
</form>

<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>  
