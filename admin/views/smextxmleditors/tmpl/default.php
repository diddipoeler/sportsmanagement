<?php 
defined('_JEXEC') or die('Restricted access');


?>
<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_EXT_XML'); ?></legend>
<table>
<?PHP
foreach ( $this->files as $file )
{


			$link = JRoute::_('index.php?option=com_sportsmanagement&view=smextxmleditor&layout=default&file_name='.$file);
			?>
			<tr class="">
				<td class="center"></td>
				<?php
					
                    ?>
                    <td class="center" nowrap="nowrap">
								<a	href="<?php echo $link; ?>" >
                                    <?php
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_EXT_XML_EDIT');
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													$imageTitle,'title= "'.$imageTitle.'"');
									?>
                    </a>                 
					</td>
				<td>
                <?php
					
					echo $file;
					
					?>
     </td>
	
			</tr>
			<?php

    
}    

?>
</table>
</fieldset>