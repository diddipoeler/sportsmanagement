<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_items.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage allclubs
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\File;

?>
<div class="row-fluid table-responsive">        
    <table class="<?php echo $this->tableclass; ?>">
        <thead>
            <tr>
                <th class="" id="">
                    <?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ALL_CLUBS', 'v.name', $this->sortDirection, $this->sortColumn); ?>
                </th>
                <?PHP
                if ($this->user->id) {
                    ?>
                    <th class="" id="">
                        <?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_CLUBINFO_UNIQUE_ID', 'v.unique_id', $this->sortDirection, $this->sortColumn); ?>
                    </th>	
                    <?PHP
                }

                if ($this->params->get('picture')) {
                    echo '<th class="" id="">';
                    echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_IMAGE', 'v.picture', $this->sortDirection, $this->sortColumn);
                    echo '</th>';
                }

                if ($this->params->get('website')) {
                    echo '<th class="" id="">';
                    echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_INTERNET', 'v.website', $this->sortDirection, $this->sortColumn);
                    echo '</th>';
                }

                if ($this->params->get('address')) {
                    echo '<th class="" id="">';
                    echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_ADDRESS', 'c.address', $this->sortDirection, $this->sortColumn);
                    echo '</th>';
                }

                if ($this->params->get('zip_code')) {
                    echo '<th class="" id="">';
                    echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_POSTAL_CODE', 'c.zipcode', $this->sortDirection, $this->sortColumn);
                    echo '</th>';
                }

                if ($this->params->get('city')) {
                    echo '<th class="" id="">';
                    echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_TOWN', 'c.location', $this->sortDirection, $this->sortColumn);
                    echo '</th>';
                }

                if ($this->params->get('country')) {
                    echo '<th class="" id="">';
                    echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_COUNTRY', 'c.country', $this->sortDirection, $this->sortColumn);
                    echo '</th>';
                }
                ?>                                

            </tr>
        </thead>
        <?php foreach ($this->items as $i => $item) : ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td>
                    <?php
                    if ($item->projectslug) {
                        $link = sportsmanagementHelperRoute::getClubInfoRoute($item->projectslug, $item->slug);
                        echo HTMLHelper::link($link, $item->name);
                    } else {
                        echo $item->name;
                    }

                    if (!File::exists(JPATH_SITE .DIRECTORY_SEPARATOR. $item->logo_big)) {
                        $item->logo_big = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");
                    }
                    ?>
                </td>
                <?PHP
                if ($this->user->id) {
                    ?>
                    <td>
                        <?php
                        echo $item->unique_id;
                        ?>
                    </td>	
                    <?PHP
                }
                ?>
                <?PHP
                if ($this->params->get('picture')) {
                    echo '<td>';
                    echo sportsmanagementHelperHtml::getBootstrapModalImage('allclub' . $item->id, $item->logo_big, $item->name, '20','',$this->modalwidth,
$this->modalheight,
$this->use_jquery_modal);
                    echo '</td>';
                }

                if ($this->params->get('website')) {
                    echo '<td>';
                    if ($item->website) {
                        echo HTMLHelper::link($item->website, $item->website, array('target' => '_blank'));
                    }
                    echo '</td>';
                }

                if ($this->params->get('address')) {
                    echo '<td>';
                    echo $item->address;
                    echo '</td>';
                }

                if ($this->params->get('zip_code')) {
                    echo '<td>';
                    echo $item->zipcode;
                    echo '</td>';
                }

                if ($this->params->get('city')) {
                    echo '<td>';
                    echo $item->location;
                    echo '</td>';
                }

                if ($this->params->get('country')) {
                    echo '<td>';
                    echo JSMCountries::getCountryFlag($item->country);
                    echo '</td>';
                }
                ?>
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
