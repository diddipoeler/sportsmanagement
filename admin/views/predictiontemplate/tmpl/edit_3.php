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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

$params = $this->form->getFieldsets('params');
$fieldsets = $this->form->getFieldsets();


JHtmlBehavior::formvalidation();
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.modal');



$i    = 1;
?>

<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&view=predictiontemplate&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	
	<?php
	
	?>
	<fieldset class="adminform">
		<legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_LEGEND', '<i>' . Text::_('COM_SPORTSMANAGEMENT_FES_' . strtoupper($this->form->getName()) . '_NAME') . '</i>', '<i>' . $this->predictionGame->name . '</i>'); ?></legend>
		<fieldset class="adminform">
			<?php
			echo Text::_('COM_SPORTSMANAGEMENT_FES_' . strtoupper($this->form->getName()) . '_DESCR');
			?>
		</fieldset>

<div class="form-horizontal">
<?php 

echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'COM_SPORTSMANAGEMENT_FES_PARAMS_GROUP_OPTIONS')); 


?>

<?PHP    
foreach ($fieldsets as $fieldset) 
{
echo HTMLHelper::_('bootstrap.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label, true));    


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

echo HTMLHelper::_('bootstrap.endTab');    
}    

?>    
	
<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
</div> 			
    
</fieldset>    
<div>		
<input type='hidden' name='user_id' value='<?php echo $this->user->id; ?>'/>
<input type="hidden" name="id" value="<?php echo $this->item->id; ?>"/>
<input type="hidden" name="predid" value="<?php echo $this->prediction_id; ?>"/>
<input type="hidden" name="task" value="predictiontemplate.edit"/>
<?php echo HTMLHelper::_('form.token'); ?>
</div>
</form>

<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   