<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
$templatesToLoad = array('footer','fieldsets');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');
// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

?>
<!-- import the functions to move the events between selection lists	-->
<?php
//$version = urlencode(sportsmanagementHelper::getVersion());
//echo JHtml::script('JL_eventsediting.js?v='.$version,'administrator/components/com_sportsmanagement/assets/js/');
?>
<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&view='.$this->view.'&layout=edit&id='.(int) $this->item->id); ?>" method="post" id="adminForm" name="adminForm" class="form-validate">

<div class="form-horizontal">
<?php 

if ( $this->change_training_date )
{
echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'training'));     
}
else
{
echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); 
} 

?>

<?PHP    
foreach ($fieldsets as $fieldset) 
{
echo JHtml::_('bootstrap.addTab', 'myTab', $fieldset->name, JText::_($fieldset->label, true));    

switch ($fieldset->name)
{
    case 'details':
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
    break;
    default:
    $this->fieldset = $fieldset->name;
    echo $this->loadTemplate('fieldsets');
    break;
}    
echo JHtml::_('bootstrap.endTab');    
}    

?>    
	
<?php echo JHtml::_('bootstrap.endTabSet'); ?>
</div> 
<div class="clr"></div>
<div>        
<input type="hidden" name="pid" value="<?php echo $this->item->project_id; ?>" />
<input type="hidden" name="project_id" value="<?php echo $this->item->project_id; ?>" />
<input type="hidden" name="season_id" value="<?php echo $this->season_id; ?>" />
<input type="hidden" name="task" value="projectteam.edit" />
</div>
<?php echo JHtml::_('form.token'); ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   