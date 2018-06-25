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
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
JHtml::_( 'behavior.tooltip' );

//echo 'predictionuser<pre>',print_r($this->predictionuser, true),'</pre>';

// Set toolbar items for the page
$edit = JFactory::getApplication()->input->getVar( 'edit', true );
$text = !$edit ? JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_NEW' ) : JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_EDIT' );
JToolbarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_PGAME' ) . ': <small><small>[ ' . $text . ' ]</small></small>' );
JToolbarHelper::save('predictionmember.save');

if ( !$edit )
{
	JToolbarHelper::divider();
	JToolbarHelper::cancel('predictionmember.cancel');
}
else
{
	// for existing items the button is renamed `close` and the apply button is showed
	JToolbarHelper::apply('predictionmember.apply');
	JToolbarHelper::divider();
	JToolbarHelper::cancel( 'predictionmember.cancel');
}
JLToolBarHelper::onlinehelp();

$uri =& JFactory::getURI();


?>


<style type="text/css">
	table.paramlist td.paramlist_key {
		width: 92px;
		text-align: left;
		height: 30px;
	}
</style>

<form action="index.php" method="post"  id="adminForm" class="form-validate">
	<div class="col50">
		<fieldset class="adminform">
			<legend>
				<?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBER' ).' ['.$this->predictionuser->username.']';
				?>
			</legend>

			<table class="admintable">
            <?php foreach ($this->form->getFieldset('details') as $field): ?>
					<tr>
						<td class="key"><?php echo $field->label; ?></td>
						<td><?php echo $field->input; ?></td>
					</tr>					
					<?php endforeach; ?>    
    			
				
				
				
				
				
				
			</table>
		</fieldset>

		

		<div class="clr"></div>
		
		<input type="hidden" name="option"											value="com_joomleague" />
		
		<input type="hidden" name="cid[]"											value="<?php echo $this->predictionuser->id; ?>" />
		<input type="hidden" name="task"											value="" />
	</div>
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   