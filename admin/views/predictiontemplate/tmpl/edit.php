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
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

JHtmlBehavior::formvalidation();
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');


$i    = 1;
?>
<style type="text/css">
	<!--
	fieldset.panelform label, fieldset.panelform div.paramrow label, fieldset.panelform span.faux-label {
		max-width: 255px;
		min-width: 255px;
		padding: 0 5px 0 0;
	}
	-->
</style>
<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&view='.$this->view.'&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	
	<?php
	
	?>
	<fieldset class="adminform">
		<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_LEGEND', '<i>' . JText::_('COM_SPORTSMANAGEMENT_FES_' . strtoupper($this->form->getName()) . '_NAME') . '</i>', '<i>' . $this->predictionGame->name . '</i>'); ?></legend>
		<fieldset class="adminform">
			<?php
			echo JText::_('COM_SPORTSMANAGEMENT_FES_' . strtoupper($this->form->getName()) . '_DESCR');
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
				<ul class="config-option-list">
				<?php foreach ($this->form->getFieldset($name) as $field): ?>
					<li>
					<?php if (!$field->hidden) : ?>
					<?php echo $field->label; ?>
					<?php endif; ?>
					<?php echo $field->input; ?>
					</li>
				<?php endforeach; ?>
				</ul>
			</fieldset>
 
    <div class="clr"></div>
    <?php endforeach; ?>
    <?php echo JHtml::_('tabs.end'); ?>
    
</fieldset>    
	<div>		
		<input type='hidden' name='user_id' value='<?php echo $this->user->id; ?>'/>
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>"/>
        <input type="hidden" name="predid" value="<?php echo $this->prediction_id; ?>"/>
		<input type="hidden" name="task" value="predictiontemplate.edit"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   