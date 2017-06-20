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


?>

<style>
 


.modaljsm {
    width: 80%;
    height: 60%;
  }  



</style>


<?php
//save and close 
$close = JRequest::getInt('close',0);
if($close == 1) {
	?><script>
	window.addEvent('domready', function() {
		jQuery('cancel').onclick();	
	});
	</script>
	<?php 
}
?>
<div class="modaljsm">
<div id="exTab1" class="container">
<form  action="<?php echo JRoute::_('index.php?option=com_sportsmanagement');?>" id='adminForm' method='post' style='display:inline' name='adminform' >
	
		<div class="fltrt">
			<button type="button" onclick="jQuery('select.position-starters option').prop('selected', 'selected');jQuery('select.position-staff option').prop('selected', 'selected');Joomla.submitform('matches.saveroster', this.form);">
				<?php echo JText::_('JAPPLY');?></button>
			<button type="button" onclick="jQuery('close').value=1; jQuery('select.position-starters option').prop('selected', 'selected');jQuery('select.position-staff option').prop('selected', 'selected');Joomla.submitform('matches.saveroster', this.form);">
				<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="<?php echo JRequest::getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
		<div class="configuration" >
			<?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELU_TITLE',$this->teamname); ?>
		</div>
	
	<div class="clear"></div>

<ul  class="nav nav-pills">
<li class="active">
<a  href="#1a" data-toggle="tab">
<?PHP
echo JText::_('COM_SPORTSMANAGEMENT_TABS_SUBST');
?>
</a>
</li>
<li>
<a href="#2a" data-toggle="tab">
<?PHP
echo JText::_('COM_SPORTSMANAGEMENT_TABS_PLAYERS');
?>
</a>
</li>
<li>
<a href="#3a" data-toggle="tab">
<?PHP
echo JText::_('COM_SPORTSMANAGEMENT_TABS_STAFF');
?>
</a>
</li>
<li>
<a href="#4a" data-toggle="tab">
<?PHP
echo JText::_('COM_SPORTSMANAGEMENT_TABS_PLAYER_TRIKOT_NUMBERS');
?>
</a>
</li>
</ul>


<div class="tab-content clearfix">
<div class="tab-pane active" id="1a">
<?php
echo $this->loadTemplate('substitutions');
?>
</div>
<div class="tab-pane" id="2a">
<?php
echo $this->loadTemplate('players');
?>
</div>
<div class="tab-pane" id="3a">
<?php
echo $this->loadTemplate('staff');
?>
</div>
<div class="tab-pane" id="4a">				
<?php
echo $this->loadTemplate('players_trikot_numbers');
?>
</div>				
</div>



		
		<?php
       // echo 'hallo';
        
		

		?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="view" value="" />
        <input type="hidden" name="project_id" value="<?php echo $this->project_id; ?>" />
		<input type="hidden" name="close" id="close" value="0" />
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
		<input type="hidden" name="changes_check" value="0" id="changes_check" />
		
		<input type="hidden" name="team" value="<?php echo $this->tid; ?>" id="team" />
		<input type="hidden" name="positionscount" value="<?php echo count($this->positions); ?>" id="positioncount"	/>
        
        
		<?php echo JHtml::_('form.token')."\n"; ?>

</form>
</div>
</div>
