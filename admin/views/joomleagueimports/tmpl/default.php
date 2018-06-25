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


if ( $this->jl_table_import_step != 'ENDE' )
{

?>

<script>

jQuery(document).ready(function () {
    document.getElementById('delayMsg').innerHTML = '';

    delayRedirect();
    // Handler for .ready() called.
//    window.setTimeout(function () {
//        location.href = "<?php echo $this->request_url.'&task=joomleagueimports.importjoomleaguenew'; ?>";
//    }, 2000);


});

function delayRedirect(){
    document.getElementById('delayMsg').innerHTML = '<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT_STEP'); ?>';
    var count = 5;
    setInterval(function(){
        count--;
        document.getElementById('countDown').innerHTML = count;
        if (count == 0) {
            document.getElementById('delayMsg').innerHTML = '<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT_STEP_START'); ?>';
            window.location = '<?php echo $this->request_url.'&task=joomleagueimports.importjoomleaguenew'; ?>'; 
        }
    },1000);
}

</script>

<?PHP    
}

if ( $this->jl_table_import_step === 'ENDE' )
{

?>

<script>

jQuery(document).ready(function () {
    document.getElementById('delayMsg').innerHTML = '';

    delayRedirect();
    // Handler for .ready() called.
//    window.setTimeout(function () {
//        location.href = "<?php echo $this->request_url.'&task=joomleagueimports.importjoomleaguenew'; ?>";
//    }, 2000);


});

function delayRedirect(){
    document.getElementById('delayMsg').innerHTML = '<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT_STEP'); ?>';
    var count = 5;
    setInterval(function(){
        count--;
        document.getElementById('countDown').innerHTML = count;
        if (count == 0) {
            document.getElementById('delayMsg').innerHTML = '<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT_STEP_START'); ?>';
            window.location = '<?php echo $this->request_url.'&task=joomleagueimports.importjoomleagueagegroup'; ?>'; 
        }
    },1000);
}

</script>

<?PHP    
}





//echo '<br><pre>'.print_r($this->success,true).'</pre>';

?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<?PHP 
/*
 if(version_compare(JVERSION,'3.0.0','ge'))  
 { 
 echo $this->loadTemplate('joomla3'); 
 } 
 else 
 { 
 echo $this->loadTemplate('joomla2');     
 } 
 */
?>

<table>
<tr>
<td class="nowrap" align="right"><?php echo $this->lists['sportstypes'].'&nbsp;&nbsp;'; ?></td>
</tr>
</table>

<table class="<?php echo $this->table_data_class; ?>">
<tr>
<td class="nowrap" align="center">
<img src= "<?php echo JURI::base( true ) ?>/components/com_sportsmanagement/assets/icons/jl.png" width="180" height="auto" >
</td>
<td class="nowrap" align="center">
<div id="delayMsg"></div>
</td>
<td class="nowrap" align="center">
<img src= "<?php echo JURI::base( true ) ?>/components/com_sportsmanagement/assets/icons/logo_transparent.png" width="180" height="auto" >
</td>
</tr>
</table>
  
<!-- <input type="button" onclick="delayRedirect()" value="Click to Redirect"/>  -->
<div id='editcell'>
<?PHP
if ( $this->success )
{
foreach ($this->success as $key => $value)
		{
			?>
			<fieldset>
				<legend><?php echo JText::_($key); ?></legend>
				<table class='adminlist'><tr><td><?php echo $value; ?></td></tr></table>
			</fieldset>
			<?php
		}
}        
?>
</div>


<fieldset>
			<legend><?php echo JText::_('Post data from importform was:'); ?></legend>
			<table class='adminlist'><tr><td><?php //echo '<pre>'.print_r($this->success,true).'</pre>'; ?></td></tr></table>
		</fieldset>
        
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
<input type="hidden" name="jl_table_import_step" value="<?php echo $this->jl_table_import_step; ?>" />

<?php echo JHtml::_('form.token')."\n"; ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>  
