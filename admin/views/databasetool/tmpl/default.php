<?php 
defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('footer');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
//echo $this->task.'<br>';
//echo '<pre>'.print_r($this->sm_tables,true).'</pre><br>';

?>


  
      
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<p class="nowarning"><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_INPROGRESS') ?></p>
<div class="joomlaupdate_spinner" ></div>



<div id="progressbar">
<div class="progress-label">
<?php echo $this->task; ?>
</div>
</div>

<input type="hidden" name="step" value="<?php echo $this->step; ?>" />
<input type="hidden" name="totals" value="<?php echo $this->totals; ?>" />


<?PHP
echo 'step -> '.$this->work_table.'<br>';

if ( $this->bar_value < 100)
{
echo '<meta http-equiv="refresh" content="1; URL='.$this->request_url.'">';
}
?>

</form>  