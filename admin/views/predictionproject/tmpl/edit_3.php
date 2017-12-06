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
$templatesToLoad = array('footer','fieldsets');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();


?>
<script type="text/javascript">

function change_published () {
  if (document.adminForm.published0.checked == true) {
    var deaktiviert=true;
  } else {
    var deaktiviert=false;
  }
  document.adminForm.mode.disabled=deaktiviert;
  document.adminForm.overview.disabled=deaktiviert;
  document.adminForm.joker0.disabled=deaktiviert;
  document.adminForm.joker1.disabled=deaktiviert;
  document.adminForm.joker_limit_select0.disabled=deaktiviert;
  document.adminForm.joker_limit_select1.disabled=deaktiviert;
  document.adminForm.champ0.disabled=deaktiviert;
  document.adminForm.champ1.disabled=deaktiviert;

  document.adminForm.points_correct_result.disabled=deaktiviert;
    document.adminForm.points_correct_result_joker.disabled=deaktiviert;
  document.adminForm.points_correct_diff.disabled=deaktiviert;
    document.adminForm.points_correct_diff_joker.disabled=deaktiviert;
  document.adminForm.points_correct_draw.disabled=deaktiviert;
    document.adminForm.points_correct_draw_joker.disabled=deaktiviert;
  document.adminForm.points_correct_tendence.disabled=deaktiviert;
    document.adminForm.points_correct_tendence_joker.disabled=deaktiviert;
  document.adminForm.points_tipp.disabled=deaktiviert;
    document.adminForm.points_tipp_joker.disabled=deaktiviert;

    document.adminForm.joker_limit.disabled=deaktiviert;

    document.adminForm.points_tipp_champ.disabled=deaktiviert;

  if (deaktiviert == false){
  change_joker();
  change_jokerlimit();
  change_champ();
}
}

function change_joker () {
  if (document.adminForm.joker0.checked == true) {
    var deaktiviert=true;
  } else {
    var deaktiviert=false;
  }
  alert(deaktiviert);
  document.adminForm.points_correct_result_joker.disabled=deaktiviert;
  document.adminForm.points_correct_diff_joker.disabled=deaktiviert;
  document.adminForm.points_correct_draw_joker.disabled=deaktiviert;
  document.adminForm.points_correct_tendence_joker.disabled=deaktiviert;
  document.adminForm.points_tipp_joker.disabled=deaktiviert;
}

function change_jokerlimit () {
  if (document.adminForm.joker_limit_select0.checked == true) {
    var deaktiviert=true;
  } else {
    var deaktiviert=false;
  }
  document.adminForm.joker_limit.disabled=deaktiviert;
}

function change_champ () {
  if (document.adminForm.champ0.checked == true) {
    var deaktiviert=true;
  } else {
    var deaktiviert=false;
  }
  document.adminForm.points_tipp_champ.disabled=deaktiviert;
  document.adminForm.league_champ.disabled=deaktiviert;
}

</script>

<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&view='.$this->view.'&layout=edit&id='.(int) $this->item->id.'&project_id='.(int) $this->item->project_id) ; ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

 <fieldset>
		<div class="fltrt">
			<button type="button" onclick="Joomla.submitform('predictionproject.store', this.form)">
				<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="<?php echo JFactory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
	</fieldset>
    	
 <div class="form-horizontal">
<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

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
		
		


            
        


	<div>	
		<input type='hidden' name='id' value='<?php echo $this->item->id; ?>' />
		<input type='hidden' name='task' value='predictionproject.edit' />
		<input type='hidden' name='psapply'	value='1' />
	</div>
	<?php 
    echo JHtml::_('form.token'); 

    ?>
</form>
<script type="text/javascript">change_published();</script>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   