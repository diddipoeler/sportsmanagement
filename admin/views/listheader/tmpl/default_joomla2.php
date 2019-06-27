<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_joomla2.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage listheader
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

$view = Factory::getApplication()->input->getCmd('view', 'cpanel');

?>
<div id="j-main-container" class="span10">

<?PHP
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo $this->loadTemplate('debug');
}
?>
<table>
		<tr>
			<td align="left" width="100%">
				<?php
				echo Text::_('JSEARCH_FILTER_LABEL');
				?>&nbsp;<input	type="text" name="filter_search" id="filter_search"
								value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
								class="text_area" onchange="$('adminForm').submit(); " />
                                
				<button onclick="this.form.submit(); "><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button onclick="document.getElementById('filter_search').value='';this.form.submit(); ">
					<?php
					echo Text::_('JSEARCH_FILTER_CLEAR');
					?>
				</button>
			</td>
            
            <?PHP
            
            switch ($view)
            {
            case 'projectteams':
               
            ?>
            <td nowrap='nowrap' align='right'><?php echo $this->lists['nationpt'].'&nbsp;&nbsp;'; ?></td>
            <?PHP
            
            break;
            default:    
            if ( isset($this->lists) )
            {
            foreach ( $this->lists as $key  => $value)
            {
            if ( !is_array($value) )
            {    
            ?>
            <td nowrap='nowrap' align='right'><?php echo $this->lists[$key].'&nbsp;&nbsp;'; ?></td>
            <?PHP
            }
            }
            }
            break;
            }
            
            switch ($view)
            {
            case 'projects':
            case 'persons':
            case 'predictiongames':
            ?>
            <td nowrap='nowrap' align='right'>
            <select name="filter_published" id="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php 
                echo HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true);
                ?>
			</select>
            </td>
            <?PHP
            break;    
            case 'smquotes':
            ?>
            <td nowrap='nowrap' align='right'>
            <select name="filter_category_id" id="filter_category_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo Text::_('JOPTION_SELECT_CATEGORY');?></option>
				<?php 
                echo HTMLHelper::_('select.options', HTMLHelper::_('category.options', 'com_sportsmanagement'), 'value', 'text', $this->state->get('filter.category_id'));
                ?>
			</select>
            </td>
            <td nowrap='nowrap' align='right'>
            <select name="filter_published" id="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php 
                echo HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true);
                ?>
			</select>
            </td>
            <?PHP
            break;
            }
            
            ?>
            
            <?PHP
            switch ($view)
            {
                case 'leagues':
                case 'jlextcountries':
            ?>
			<td align="center" colspan="4">
				<?php
                $startRange = ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('character_filter_start_hex', '0');
		$endRange = ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('character_filter_end_hex', '0');
		for ($i=$startRange; $i <= $endRange; $i++)
		{
            printf("<a href=\"javascript:searchPerson('%s')\">%s</a>&nbsp;&nbsp;&nbsp;&nbsp;",'&#'.$i.';','&#'.$i.';');
			}
				
				?>
			</td>
            <?PHP
            break;
            
            default:
            break;
            }
            
            
            
            ?>
		</tr>
	</table>
  