<?php 

defined('_JEXEC') or die('Restricted access');
// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

?>

<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'); ?></legend>
			<ul class="adminformlist">
			<?php 
            foreach($this->form->getFieldset('details') as $field) :
            
            //echo '<pre>'.print_r($field,true).'</pre>';
            ?>
				<li><?php echo $field->label; ?>
				<?php echo $field->input; 
                 
                if ( $field->name == 'jform[country]' )
                {
                echo JSMCountries::getCountryFlag($field->value);    
                }
                
                if ( $field->name == 'jform[standard_playground]' )
                {
                //echo sportsmanagementHelper::getPicturePlayground($field->value);
                $picture = sportsmanagementHelper::getPicturePlayground($field->value);
                //echo $picture;
                //echo JHtml::image($picture, 'Playground', array('title' => 'Playground','width' => '50' )); 
                //echo JHtml::_('image', $picture, 'Playground',array('title' => 'Playground','width' => '50' )); 
?>
<a href="<?php echo JURI::root().$picture;?>" title="<?php echo 'Playground';?>" class="modal">
<img src="<?php echo JURI::root().$picture;?>" alt="<?php echo 'Playground';?>" width="50" />
</a>
<?PHP                   
                }
                
                if ( $field->name == 'jform[website]' )
                {
                echo '<img style="" src="http://www.thumbshots.de/cgi-bin/show.cgi?url='.$field->value.'">';  
                }
                if ( $field->name == 'jform[twitter]' )
                {
                echo '<img style="" src="http://www.thumbshots.de/cgi-bin/show.cgi?url='.$field->value.'">';  
                }
                if ( $field->name == 'jform[facebook]' )
                {
                echo '<img style="" src="http://www.thumbshots.de/cgi-bin/show.cgi?url='.$field->value.'">';  
                }
                
                $suchmuster = array ("jform[","]");
                $ersetzen = array ('', '');
                $var_onlinehelp = str_replace($suchmuster, $ersetzen, $field->name);
                
                switch ($var_onlinehelp)
                {
                    case 'id':
                    break;
                    default:
                    if ( $field->type != 'Hidden')
                    {
                ?>
                <a	rel="{handler: 'iframe',size: {x: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH; ?>,y: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT; ?>}}"
									href="<?php echo COM_SPORTSMANAGEMENT_HELP_SERVER.'SM-Backend-Felder:'.JRequest::getVar( "view").'-'.$var_onlinehelp; ?>"
									 class="modal">
									<?php
									echo JHtml::_(	'image','media/com_sportsmanagement/jl_images/help.png',
													JText::_('COM_SPORTSMANAGEMENT_HELP_LINK'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_HELP_LINK').'"');
									?>
								</a>
                
                <?PHP
                }
                break;
                }
                ?>
                </li>
			<?php 
            
            //echo $field->type;
            
            endforeach; 
            ?>
			</ul>
		</fieldset>