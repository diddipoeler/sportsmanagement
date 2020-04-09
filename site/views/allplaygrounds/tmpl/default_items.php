<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage allplaygrounds
 * @file       default_items.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;

?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="allplaygrounds">
    <table class="<?php echo $this->tableclass; ?>">

        <thead>
        <tr>
            <th class="" id="">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_PLAYGROUNDS', 'v.name', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th class="" id="">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_IMAGE', 'v.picture', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th class="" id="">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_INTERNET', 'v.website', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th class="" id="">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_ADDRESS', 'v.address', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th class="" id="">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_POSTAL_CODE', 'v.zipcode', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th class="" id="">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_TOWN', 'v.city', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th class="" id="">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_COUNTRY', 'v.country', $this->sortDirection, $this->sortColumn); ?>
            </th>

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
						$routeparameter                       = array();
						$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
						$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
						$routeparameter['p']                  = $item->projectslug;
						$routeparameter['pgid']               = $item->slug;
						$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('playground', $routeparameter);
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
                <td>
					<?PHP
					echo sportsmanagementHelperHtml::getBootstrapModalImage(
						'allplayground' . $item->id, $item->picture, $item->name, '20', '', $this->modalwidth,
						$this->modalheight,
						$this->use_jquery_modal
					)
					?>


                </td>
                <td>
					<?php echo HTMLHelper::link($item->website, $item->website, array('target' => '_blank')); ?>
                </td>
                <td>
					<?php echo $item->address; ?>
                </td>
                <td>
					<?php echo $item->zipcode; ?>
                </td>
                <td>
					<?php echo $item->city; ?>
                </td>
                <td>
					<?php echo JSMCountries::getCountryFlag($item->country); ?>
                </td>
            </tr>
		<?php endforeach; ?>
    </table>
</div>

<div class="pagination">
    <p class="counter">
		<?php echo $this->pagination->getPagesCounter(); ?>
    </p>
    <p class="counter">
		<?php echo $this->pagination->getResultsCounter(); ?>
    </p>
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>
