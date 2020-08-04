<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage smimageimports
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="table-responsive" id="editcell">
<legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_EXT_IMAGES_IMPORT'); ?></legend>

    <table class="<?php echo $this->table_data_class; ?>">

        <table>
            <tr>
                <td class="nowrap" align="left" width="100%">
					<?php
					echo Text::_('JSEARCH_FILTER_LABEL');
					?>
                    <input type="text" name="filter_search" id="filter_search"
                           value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                           class="text_area" onchange="$('adminForm').submit(); "/>

                    <button onclick="this.form.submit(); "><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                    <button onclick="document.getElementById('filter_search').value='';this.form.submit(); ">
						<?php
						echo Text::_('JSEARCH_FILTER_CLEAR');
						?>
                    </button>
                </td>

                <td class="nowrap" align="right"><?php echo $this->lists['folders']; ?></td>

                <td class="nowrap" align="right">
                    <select name="filter_published" id="filter_published" class="inputbox"
                            onchange="this.form.submit()">
                        <option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED'); ?></option>
						<?php
						echo HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true);
						?>
                    </select>
                </td>
            </tr>
        </table>
        <table class="<?php echo $this->table_data_class; ?>">
            <thead>
            <tr>
                <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
                <th width="20">
                    <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
                </th>
                <th>
					<?php
					//echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_IMAGE');
					echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_IMAGE'), 'name', $this->sortDirection, $this->sortColumn);

					?>
                </th>
                <th><?php
					//echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_PATH');
					echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_PATH'), 'folder', $this->sortDirection, $this->sortColumn);
					?>
                </th>
                <th><?php
					//echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_DIRECTORY');
					echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_DIRECTORY'), 'directory', $this->sortDirection, $this->sortColumn);
					?>
                </th>
                <th><?php
					//echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_FILE');
					echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_FILE'), 'file', $this->sortDirection, $this->sortColumn);
					?>
                </th>
                <th><?php echo Text::_('JSTATUS'); ?></th>
            </tr>
            </thead>

            <tfoot>
            <tr>
                <td colspan="7"><?php echo $this->pagination->getListFooter(); ?></td>
            </tr>
            </tfoot>

			<?PHP
			//$k = 0;
			//for ($i = 0, $n = count($this->items); $i < $n; $i++)
			//{
foreach ($this->items as $i => $this->item)
	{
		//$row        = &$this->items[$i];
$this->count_i = $i;				
			//	$row       =& $this->items[$i];
$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;	
$checked = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'smimageimports.', $canCheckin);
				//$published = HTMLHelper::_('grid.published', $row, $i, 'tick.png', 'publish_x.png', 'smimageimports.');
	
				?>
                <tr class="<?php echo "row"; ?>">
                    <td class="center"><?php echo $this->pagination->getRowOffset($this->count_i); ?></td>
                    <td class="center"><?php echo HTMLHelper::_('grid.id', $this->count_i, $this->item->id); ?></td>
                    <td><?php echo $this->item->name; ?></td>
                    <input type='hidden' name='picture[<?php echo $this->item->id; ?>]' value='<?php echo $this->item->name; ?>'/>
                    <td><?php echo $this->item->folder; ?></td>
                    <input type='hidden' name='folder[<?php echo $this->item->id; ?>]' value='<?php echo $this->item->folder; ?>'/>
                    <td><?php echo $this->item->directory; ?></td>
                    <input type='hidden' name='directory[<?php echo $this->item->id; ?>]'
                           value='<?php echo $this->item->directory; ?>'/>
                    <td><?php echo $this->item->file; ?></td>
                    <input type='hidden' name='file[<?php echo $this->item->id; ?>]' value='<?php echo $this->item->file; ?>'/>
                    <td class="center">
						<?php
						if ($this->item->published)
						{
$imageTitle = Text::_('bereits installiert');
$attribs['title'] = $imageTitle;              
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/ok.png',$imageTitle,$attribs);
						}
						else
						{
$imageTitle = Text::_('noch nicht installiert');
$attribs['title'] = $imageTitle;              
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/error.png',$imageTitle,$attribs);
						}

						?>
                    </td>
                </tr>
				<?php
				//$k = 1 - $k;
			}

			?>

        </table>
</div>
