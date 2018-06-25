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

?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">

<?PHP

if(version_compare(JVERSION,'3.0.0','ge')) 
{
echo $this->loadTemplate('joomla3');
}
else
{
echo $this->loadTemplate('joomla2');    
}

?>



<!-- <fieldset class="adminform"> -->
<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_EXT_IMAGES_IMPORT'); ?></legend>

<table class="<?php echo $this->table_data_class; ?>">

<table>
		<tr>
			<td class="nowrap" align="left" width="100%">
				<?php
				echo JText::_('JSEARCH_FILTER_LABEL');
				?>
				<input	type="text" name="filter_search" id="filter_search"
								value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
								class="text_area" onchange="$('adminForm').submit(); " />
                                
				<button onclick="this.form.submit(); "><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button onclick="document.getElementById('filter_search').value='';this.form.submit(); ">
					<?php
					echo JText::_('JSEARCH_FILTER_CLEAR');
					?>
				</button>
			</td>
			
			<td class="nowrap" align="right"><?php echo $this->lists['folders']; ?></td>
            
            <td class="nowrap" align="right">
            <select name="filter_published" id="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php 
                echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true);
                ?>
			</select>
            </td>
		</tr>
	</table>
<table class="<?php echo $this->table_data_class; ?>">
<thead>
				<tr>
					<th width="5"><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>
                    <th>
                    <?php 
                    //echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_IMAGE'); 
                    echo JHTML::_( 'grid.sort', JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_IMAGE'), 'name', $this->sortDirection, $this->sortColumn);
                    
                    ?>
                    </th>
                    <th><?php 
                    //echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_PATH'); 
                    echo JHTML::_( 'grid.sort', JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_PATH'), 'folder', $this->sortDirection, $this->sortColumn);
                    ?>
                    </th>
                    <th><?php 
                    //echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_DIRECTORY'); 
                    echo JHTML::_( 'grid.sort', JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_DIRECTORY'), 'directory', $this->sortDirection, $this->sortColumn);
                    ?>
                    </th>
                    <th><?php 
                    //echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_FILE'); 
                    echo JHTML::_( 'grid.sort', JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_FILE'), 'file', $this->sortDirection, $this->sortColumn);
                    ?>
                    </th>
<th><?php echo JText::_('JSTATUS'); ?></th>
</tr>
</thead>

<tfoot><tr><td colspan="7"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
                    
<?PHP
$k=0;
for ($i=0,$n=count($this->items); $i < $n; $i++)
{
$row =& $this->items[$i];
$checked = JHtml::_('grid.checkedout',$row,$i);
$published  = JHtml::_('grid.published',$row,$i,'tick.png','publish_x.png','smimageimports.');
?>
<tr class="<?php echo "row$k"; ?>">
<td class="center"><?php echo ($i +1); ?></td>
<td class="center"><?php echo $checked; ?></td>
<td><?php echo $row->name; ?></td>
<input type='hidden' name='picture[<?php echo $row->id; ?>]' value='<?php echo $row->name; ?>' />
<td><?php echo $row->folder; ?></td>
<input type='hidden' name='folder[<?php echo $row->id; ?>]' value='<?php echo $row->folder; ?>' />
<td><?php echo $row->directory; ?></td>
<input type='hidden' name='directory[<?php echo $row->id; ?>]' value='<?php echo $row->directory; ?>' />
<td><?php echo $row->file; ?></td>
<input type='hidden' name='file[<?php echo $row->id; ?>]' value='<?php echo $row->file; ?>' />
<td class="center">
<?php 
if ( $row->published )
{
$imageTitle = JText::_('bereits installiert');
echo JHtml::_('image','administrator/components/com_sportsmanagement/assets/images/ok.png',
$imageTitle,'title= "'.$imageTitle.'"');    
}
else
{
$imageTitle = JText::_('noch nicht installiert');
echo JHtml::_('image','administrator/components/com_sportsmanagement/assets/images/error.png',
$imageTitle,'title= "'.$imageTitle.'"');      
}
 
?>
</td>
</tr>
<?php
$k=1 - $k;
}

?>

</table>

<!-- </fieldset> -->

<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />

<?php echo JHtml::_('form.token')."\n"; ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>  