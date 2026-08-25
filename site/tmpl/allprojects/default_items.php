<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage allprojects
 * @file       default_items.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="allprojects-items">
    <table class="<?php echo $this->tableclass; ?>">
        <thead>
        <tr>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ALL_PROJECTS', 'v.name', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_IMAGE', 'v.picture', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ALL_PROJECTS_LEAGUE_NAME', 'l.name', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ALL_PROJECTS_SEASON', 's.name', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_COUNTRY', 'v.country', $this->sortDirection, $this->sortColumn); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) : ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td>
                    <?php
                    if (!empty($item->slug)) {
                        $routeparameter = [
                            'cfg_which_database' => $this->input->getInt('cfg_which_database', 0),
                            's' => $this->input->getInt('s', 0),
                            'p' => $item->slug,
                            'type' => 0,
                            'r' => 0,
                            'from' => 0,
                            'to' => 0,
                            'division' => 0,
                        ];
                        $link = SiteRouteHelper::view($this->template, $routeparameter);
                        echo HTMLHelper::link($link, $this->escape($item->name));
                    } else {
                        echo $this->escape($item->name);
                    }

                    $picture = (string) ($item->picture ?? '');
                    $localPicture = JPATH_SITE . DIRECTORY_SEPARATOR . ltrim($picture, '/\\');
                    if ($picture === '' || !is_file($localPicture)) {
                        $picture = sportsmanagementHelper::getDefaultPlaceholder('clublogobig');
                    }
                    ?>
                </td>
                <td>
                    <?php
                    echo sportsmanagementHelperHtml::getBootstrapModalImage(
                        'allproject' . (int) $item->id,
                        $picture,
                        (string) $item->name,
                        '20',
                        '',
                        $this->modalwidth,
                        $this->modalheight,
                        $this->use_jquery_modal
                    );
                    ?>
                </td>
                <td><?php echo $this->escape($item->leaguename); ?></td>
                <td><?php echo $this->escape($item->seasonname); ?></td>
                <td><?php echo JSMCountries::getCountryFlag($item->country); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="pagination">
    <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
    <p class="counter"><?php echo $this->pagination->getResultsCounter(); ?></p>
    <?php echo $this->pagination->getPagesLinks(); ?>
</div>
