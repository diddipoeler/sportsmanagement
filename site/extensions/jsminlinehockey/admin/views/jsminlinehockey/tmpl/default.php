<?PHP
defined( '_JEXEC' ) or die( 'Restricted access' );

//JHtml::_('behavior.tooltip');
//JHtml::_('behavior.modal');

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>

<div id="editcell">
	
    
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
    
<form enctype='multipart/form-data' action='<?php echo $this->request_url; ?>' method='post' id='adminForm' name='adminForm'>


<fieldset style='text-align: center; '>
				<input class='input_box' id='import_package' name='import_package' type='file' size='57' />
				<input class='button' type='submit' value='<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_UPLOAD_BUTTON'); ?>' />
			</fieldset>
<input type='hidden' name='sent' value='1' />
		
		<input type='hidden' name='task' value='jsminlinehockey.save' />
                    
</form>


</div>



<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?> 