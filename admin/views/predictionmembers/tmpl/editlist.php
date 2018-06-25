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

defined( '_JEXEC' ) or die( 'Restricted access' );
JHtml::_( 'behavior.tooltip' );

//save and close 
$close = JFactory::getApplication()->input->getInt('close',0);
if($close == 1) {
	?><script>
	window.addEvent('domready', function() {
		$('cancel').onclick();	
	});
	</script>
	<?php 
}



// Set toolbar items for the page
//$edit = JFactory::getApplication()->input->getVar('edit',true);

//$component_text = 'COM_SPORTSMANAGEMENT_';
//JToolbarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONMEMBERS_ASSIGN' ) );
#JToolbarHelper::title( $this->projectws->name . ' - ' . JText::_( 'Teams' ) . ' ' );
//JToolbarHelper::save( 'predictionmember.save_memberlist' );

// for existing items the button is renamed `close` and the apply button is showed
//JToolbarHelper::cancel( 'predictionmember.cancel', 'JL_GLOBAL_CLOSE' );

//JLToolBarHelper::onlinehelp();

//$uri = JFactory::getURI();
?>
<!-- import the functions to move the events between selection lists  -->
<?php
//$version = urlencode(JoomleagueHelper::getVersion());
//echo JHtml::script( 'JL_eventsediting.js?v='.$version,'administrator/components/com_joomleague/assets/js/');
?>


<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement');?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	
    <fieldset>
		<div class="fltrt">
			<button type="button" onclick="Joomla.submitform('predictionmembers.save_memberlist', this.form)">
				<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="<?php echo JFactory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
		
	</fieldset>
    
    <div class="col50">
		<fieldset class="adminform">
			<legend>
				<?php
				echo JText::sprintf( 'COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONMEMBERS_ASSIGN_TITLE', '<i>' . $this->prediction_name . '</i>');
				?>
			</legend>
			
			
			
			<table class="admintable" border="0">
				<tr>
					<td>
						<b>
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONMEMBERS_ASSIGN_AVAIL_MEMBERS' );
							?>
						</b><br />
						<?php
                        if ( isset($this->lists['members']) )
                        {
						echo $this->lists['members'];
                        }
                        else
                        {
                            
                        }
						?>
					</td>
					<td style="text-align:center; ">
						&nbsp;&nbsp;
						<input	type="button" class="inputbox"
								onclick="move_list_items('members','prediction_members');jQuery('#prediction_members option').prop('selected', true);"
								value="&gt;&gt;" />
						&nbsp;&nbsp;<br />&nbsp;&nbsp;
					 	<input	type="button" class="inputbox"
					 			onclick="move_list_items('prediction_members','members');jQuery('#prediction_members option').prop('selected', true);"
								value="&lt;&lt;" />
						&nbsp;&nbsp;
					</td>
					<td>
						<b>
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONMEMBERS_ASSIGN_PROJ_MEMBERS' );
							?>
						</b><br />
						<?php
						echo $this->lists['prediction_members'];
						?>
					</td>
			   </tr>
			</table>
		</fieldset>
		<div class="clr"></div>

		<input type='hidden' name='task' value='' />
        <input type="hidden" name="option"				value="com_sportsmanagement" />
		<input type="hidden" name="cid"				value="<?php echo $this->prediction_id; ?>" />
        <input type="hidden" name="component" value="com_sportsmanagement" />
		<?php echo JHtml::_('form.token')."\n"; ?>
	</div>
</form>