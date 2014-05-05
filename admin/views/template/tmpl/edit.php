<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');

//require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'models'.DS.'fields'.DS.'jlgcolor.php');

$templatesToLoad = array('footer');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
//JHtmlBehavior::formvalidation();
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
JHtml::_('behavior.formvalidation');
//JHtml::_('behavior.colorpicker');
$params = $this->form->getFieldsets('params');

$i    = 1;
?>

<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&view=template&layout=edit&id='.(int) $this->template->id); ?>" method="post" id="adminForm" name="adminForm" >
	<div style='text-align: right;'>
		<?php echo $this->lists['templates']; ?>
	</div>
	<?php
	if ($this->project->id != $this->template->project_id) {
		JError::raiseNotice(0, JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_MASTER_WARNING'));
		?><input type="hidden" name="master_id" value="<?php echo $this->template->project_id; ?>"/><?php
	}
	?>
	<fieldset class="adminform">
		<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_LEGEND', '<i>' . JText::_('COM_SPORTSMANAGEMENT_FES_' . strtoupper($this->form->getName()) . '_NAME') . '</i>', '<i>' . $this->project->name . '</i>'); ?></legend>
		<fieldset class="adminform">
			<?php
			echo JText::_('COM_SPORTSMANAGEMENT_FES_' . strtoupper($this->form->getName()) . '_DESCR');
            //echo '<br>'.strtoupper($this->form->getName()).'<br>';
			?>
		</fieldset>

		<?php
		echo JHtml::_('tabs.start','tabs', array('useCookie'=>1));
        $fieldSets = $this->form->getFieldsets();
        foreach ($fieldSets as $name => $fieldSet) :
            $label = $fieldSet->name;
            echo JHtml::_('tabs.panel',JText::_($label), 'panel'.$i++);
			?>
			<fieldset class="panelform">
				<?php
				if (isset($fieldSet->description) && !empty($fieldSet->description)) :
					echo '<fieldset class="adminform">'.JText::_($fieldSet->description).'</fieldset>';
				endif;
				?>
				<!-- <ul class="config-option-list"> -->
				<?php 
                foreach ($this->form->getFieldset($name) as $field): 
                
                $suchmuster = array ("params[","]","[");
                $ersetzen = array ('', '', '');
                $var_onlinehelp = str_replace($suchmuster, $ersetzen, $field->name);
                
                if ( $var_onlinehelp != 'colors' )
                {
                    //echo '<li>';
                }    
                ?>
					
					<?php if (!$field->hidden) : ?>
					<?php echo $field->label; ?>
					<?php endif; ?>
					<?php echo $field->input; 
                    
                    
                  
                    //echo ' <br><pre>'.print_r($field->name,true).'</pre>';
                    
                    //if ($fieldSet->customselbox)
                    //{
                    //echo '<br>customselbox<br>'.$field->customselbox.'<br>';
                    //}
                    
//                    $suchmuster = array ("params[","]","[");
//                $ersetzen = array ('', '', '');
//                $var_onlinehelp = str_replace($suchmuster, $ersetzen, $field->name);
               
                    
                if ( $var_onlinehelp == 'ordered_columns_new_select' )
                {
                ?>
                <input  type="button" class="inputbox"
						onclick="move_list_items('params_ordered_columns_new_select','params_ordered_columns_new');jQuery('#params_ordered_columns_new option').prop('selected', true);"
						value="&gt;&gt;" />
				<br /><br />
                <?PHP    
                }    
                if ( $var_onlinehelp == 'ordered_columns_new' )
                {
                ?>
                <input  type="button" class="inputbox"
						onclick="move_list_items('params_ordered_columns_new','params_ordered_columns_new_select');jQuery('#params_ordered_columns_new option').prop('selected', true);"
						value="&lt;&lt;" />
                <input  type="button" class="inputbox"
						onclick="move_up('params_ordered_columns_new');jQuery('#params_ordered_columns_new option').prop('selected', true);"
						value="<?php echo JText::_('JLIB_HTML_MOVE_UP'); ?>" />
                        <br />
                <input type="button" class="inputbox"
					   onclick="move_down('params_ordered_columns_new');jQuery('#params_ordered_columns_new option').prop('selected', true);"
					   value="<?php echo JText::_('JLIB_HTML_MOVE_DOWN'); ?>" />            
                <?PHP    
                }
                switch ($var_onlinehelp)
                {
                    case 'id':
                    case 'colors':
                    break;
                    default:
                ?>
                <a	rel="{handler: 'iframe',size: {x: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH; ?>,y: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT; ?>}}"
									href="<?php echo COM_SPORTSMANAGEMENT_HELP_SERVER.'SM-Backend-Templateparameter:'.$var_onlinehelp; ?>"
									 class="modal">
									<?php
									echo JHtml::_(	'image','media/com_sportsmanagement/jl_images/help.png',
													JText::_('COM_SPORTSMANAGEMENT_HELP_LINK'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_HELP_LINK').'"');
									?>
								</a>
                
                <?PHP
                break;
                }
                
                if ( $var_onlinehelp != 'colors' )
                {
                    //echo '</li>';
                }  
                
                ?>
			
				<?php 
                endforeach; 
                ?>
				<!-- </ul> -->
                </fieldset>
                <?PHP
                
                ?>

    <div class="clr"></div>
    <?php endforeach; ?>
    <?php echo JHtml::_('tabs.end'); ?>

</fieldset>

	<div>		
		<input type='hidden' name='user_id' value='<?php echo $this->user->id; ?>'/>
		
		<input type="hidden" name="task" value="template.edit"/>
        
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   