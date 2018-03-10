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

//$cfg_help_server = JComponentHelper::getParams('com_sportsmanagement')->get('cfg_help_server','') ;
//$modal_popup_width = JComponentHelper::getParams('com_sportsmanagement')->get('modal_popup_width',0) ;
//$modal_popup_height = JComponentHelper::getParams('com_sportsmanagement')->get('modal_popup_height',0) ;

// No direct access
defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('footer','fieldsets');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
//jimport( 'joomla.html.html.tabs' );
jimport('joomla.html.pane');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.modal');
$params = $this->form->getFieldsets('params');

//$options = array(
//    'onActive' => 'function(title, description){
//        description.setStyle("display", "block");
//        title.addClass("open").removeClass("closed");
//    }',
//    'onBackground' => 'function(title, description){
//        description.setStyle("display", "none");
//        title.addClass("closed").removeClass("open");
//    }',
//    'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
//    'useCookie' => true, // this must not be a string. Don't use quotes.
//);

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();
$formparams = $this->formparams->getFieldsets();

//echo ' params<br><pre>'.print_r($params,true).'</pre>';
//echo ' fieldsets<br><pre>'.print_r($fieldsets,true).'</pre>';
//echo ' formparams<br><pre>'.print_r($formparams,true).'</pre>';

foreach($formparams as $fieldset) :
$this->description = $fieldset->description;
endforeach;
?>
<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&view='.$this->view.'&layout=edit&id='.(int) $this->item->id .'&tmpl='.$this->tmpl); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

<?PHP
if ( $this->tmpl )
{
		?>
			<fieldset>
				<div class="fltrt">
					<button type="button" onclick="Joomla.submitform('statistic.apply', this.form);">
						<?php echo JText::_('JAPPLY');?></button>
					<button type="button" onclick="Joomla.submitform('statistic.save', this.form);">
						<?php echo JText::_('JSAVE');?></button>
					<button id="cancel" type="button" onclick="<?php echo JFactory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
						<?php echo JText::_('JCANCEL');?></button>
				
                
                </div>
				
			</fieldset>
<?PHP                
}
?> 

<?PHP
                
                ?>
                
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

    
 <div class="clr"></div>
	<div>
		<input type="hidden" name="task" value="club.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   