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
$templatesToLoad = array('footer','listheader');
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

<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&layout=edit&id='.(int) $this->item->id.'&project_id='.(int) $this->item->project_id) ; ?>" method="post" name="adminForm" id="adminForm">
	<div class='col50'>
    
     <fieldset>
		<div class="fltrt">
			<button type="button" onclick="Joomla.submitform('predictionproject.store', this.form)">
				<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="<?php echo JRequest::getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
	</fieldset>
    
		
		
 <div class="width-60 fltlft">       
        <fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'); ?></legend>
			<ul class="adminformlist">
			<?php foreach($this->form->getFieldset('details') as $field) :?>
				<li><?php echo $field->label; ?>
				<?php echo $field->input; 
                ?></li>
			<?php endforeach; ?>
			</ul>
		</fieldset>
</div>         
<div class="width-40 fltrt">
		<?php
		echo JHtml::_('sliders.start');
		foreach ($fieldsets as $fieldset) :
			if ($fieldset->name == 'details') :
				continue;
			endif;
			echo JHtml::_('sliders.panel', JText::_($fieldset->label), $fieldset->name);
		if (isset($fieldset->description) && !empty($fieldset->description)) :
				echo '<p class="tab-description">'.JText::_($fieldset->description).'</p>';
			endif;
		echo $this->loadTemplate($fieldset->name);
		endforeach; ?>
		<?php echo JHtml::_('sliders.end'); ?>

	
	</div>    
            
</div>        

		<div class='clr'></div>
	<div>	
		<input type='hidden' name='id'					value='<?php echo $this->item->id; ?>' />
		<input type='hidden' name='task'					value='predictionproject.edit' />
		<input type='hidden' name='psapply'					value='1' />
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