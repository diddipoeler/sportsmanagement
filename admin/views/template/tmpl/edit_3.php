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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
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

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
//JHtmlBehavior::formvalidation();
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
JHtml::_('behavior.formvalidation');
//JHtml::_('behavior.colorpicker');
$params = $this->form->getFieldsets('params');
$fieldsets = $this->form->getFieldsets();

JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_FES_' . strtoupper($this->form->getName()) . '_NAME'), 'template');

$i    = 1;

//echo ' params -><br><pre>'.print_r($params,true).'</pre>';
//echo ' fieldSets -><br><pre>'.print_r($fieldsets,true).'</pre>';

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

<div class="form-horizontal">
<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'COM_SPORTSMANAGEMENT_FES_PARAMS_GROUP_PAGE_ELEMENTS')); ?>

<?PHP    
foreach ($fieldsets as $fieldset) 
{
echo JHtml::_('bootstrap.addTab', 'myTab', $fieldset->name, JText::_($fieldset->label, true));    


    ?>
    <div class="row-fluid">
			<div class="span9">
				<div class="row-fluid form-horizontal-desktop">
					<div class="span6">
    <?PHP
    foreach( $this->form->getFieldset($fieldset->name) as $field ) 
    {
        ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php

    }
    ?>
    </div>
				</div>
			</div>
            </div>
    <?PHP

echo JHtml::_('bootstrap.endTab');    
}    

?>    
	
<?php echo JHtml::_('bootstrap.endTabSet'); ?>
</div> 	

<div>		
<input type='hidden' name='user_id' value='<?php echo $this->user->id; ?>'/>
<input type='hidden' name='template' value='<?php echo $this->item->template; ?>'/>
<input type='hidden' name='title' value='<?php echo $this->item->title; ?>'/>
<input type="hidden" name="task" value="template.edit"/>
        
<?php echo JHtml::_('form.token'); ?>
</div>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   