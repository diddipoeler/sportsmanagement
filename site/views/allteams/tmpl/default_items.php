<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_items.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage allteams
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
?>
<div class="<?php echo $this->divclassrow;?> table-responsive" id="allteams">      
	<table class="<?php echo $this->tableclass; ?>">

		<thead>
			<tr>
				<th class="" id="">
					<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_PLAYGROUND_CLUB_TEAMS', 'v.name', $this->sortDirection, $this->sortColumn); ?>
				</th>

				<?php
				if ($this->params->get('picture'))
				{
					echo '<th class="" id="">';
					echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_IMAGE', 'v.picture', $this->sortDirection, $this->sortColumn);
					echo '</th>';
				}

				if ($this->params->get('website'))
				{
					echo '<th class="" id="">';
					echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_INTERNET', 'v.website', $this->sortDirection, $this->sortColumn);
					echo '</th>';
				}

				if ($this->params->get('address'))
				{
					echo '<th class="" id="">';
					echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_ADDRESS', 'c.address', $this->sortDirection, $this->sortColumn);
					echo '</th>';
				}

				if ($this->params->get('zip_code'))
				{
					echo '<th class="" id="">';
					echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_POSTAL_CODE', 'c.zipcode', $this->sortDirection, $this->sortColumn);
					echo '</th>';
				}

				if ($this->params->get('city'))
				{
					echo '<th class="" id="">';
					echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_TOWN', 'c.location', $this->sortDirection, $this->sortColumn);
					echo '</th>';
				}

				if ($this->params->get('country'))
				{
					echo '<th class="" id="">';
					echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_COUNTRY', 'c.country', $this->sortDirection, $this->sortColumn);
					echo '</th>';
				}
				?>

			</tr>
		</thead>
		<?php foreach ($this->items as $i => $item)
		:
	?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<?php
					if ($item->projectslug)
					{
						$routeparameter = array();
						$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
						$routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
						$routeparameter['p'] = $item->projectslug;
						$routeparameter['tid'] = $item->slug;
						$routeparameter['ptid'] = 0;
						$link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);
						echo HTMLHelper::link($link, $item->name);
					}
					else
					{
						echo $item->name;
					}

					if (!File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $item->picture))
					{
						$item->picture = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");
					}
					?>
				</td>

				<?PHP
				if ($this->params->get('picture'))
				{
					echo '<td>';
					echo sportsmanagementHelperHtml::getBootstrapModalImage(
						'allteams' . $item->id, $item->picture, $item->name, '20', '', $this->modalwidth,
						$this->modalheight,
						$this->use_jquery_modal
					);
					echo '</td>';
				}

				if ($this->params->get('website'))
				{
					echo '<td>';

					if ($item->website)
					{
						echo HTMLHelper::link($item->website, $item->website, array('target' => '_blank'));
					}

					echo '</td>';
				}

				if ($this->params->get('address'))
				{
					echo '<td>';
					echo $item->address;
					echo '</td>';
				}

				if ($this->params->get('zip_code'))
				{
					echo '<td>';
					echo $item->zipcode;
					echo '</td>';
				}

				if ($this->params->get('city'))
				{
					echo '<td>';
					echo $item->location;
					echo '</td>';
				}

				if ($this->params->get('country'))
				{
					echo '<td>';
					echo JSMCountries::getCountryFlag($item->country);
					echo '</td>';
				}
				?>

			</tr>
		<?php endforeach; ?>

		<tfoot><tr><td colspan="10"><?php // Echo $this->pagination->getListFooter();  ?></td></tr></tfoot>
	</table>
</div>

<div class="pagination">
	<p class="counter">
		<?php echo $this->pagination->getPagesCounter(); ?>
	</p>
	<p class="counter">
		<?php echo $this->pagination->getResultsCounter(); ?>
	</p>
	<?php echo $this->pagination->getPagesLinks();
?>
	<?php // Echo $this->pagination->getListFooter();  ?>
</div>
