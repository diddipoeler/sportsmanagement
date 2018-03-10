<?php
/** Joomla Sports Management ein Programm zur Verwaltung f�r alle Sportarten
 * @version 1.0.26
 * @file components/sportsmanagement/views/allteams/tmpl/default_items.php
 * @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license This file is part of Joomla Sports Management.
 *
 * Joomla Sports Management is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Joomla Sports Management is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Joomla Sports Management. If not, see <http://www.gnu.org/licenses/>.
 *
 * Diese Datei ist Teil von Joomla Sports Management.
 *
 * Joomla Sports Management ist Freie Software: Sie k�nnen es unter den Bedingungen
 * der GNU General Public License, wie von der Free Software Foundation,
 * Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
 * ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
 *
 * Joomla Sports Management wird in der Hoffnung, dass es n�tzlich sein wird, aber
 * OHNE JEDE GEW�HRLEISTUNG, bereitgestellt; sogar ohne die implizite
 * Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
 * Siehe die GNU General Public License f�r weitere Details.
 *
 * Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
 * Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
 *
 * Note : All ini files need to be saved as UTF-8 without BOM
 */
defined('_JEXEC') or die('Restricted access');

//echo '<pre>'.print_r($this->items,true).'</pre>';
?>
<div class="table-responsive">        
    <table class="<?php echo $this->tableclass; ?>">

        <thead>
            <tr>
                <th class="" id="">
                    <?php echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_PLAYGROUND_CLUB_TEAMS', 'v.name', $this->sortDirection, $this->sortColumn); ?>
                </th>

                <?php
                if ($this->params->get('picture')) {
                    echo '<th class="" id="">';
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_IMAGE', 'v.picture', $this->sortDirection, $this->sortColumn);
                    echo '</th>';
                }

                if ($this->params->get('website')) {
                    echo '<th class="" id="">';
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_INTERNET', 'v.website', $this->sortDirection, $this->sortColumn);
                    echo '</th>';
                }

                if ($this->params->get('address')) {
                    echo '<th class="" id="">';
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_ADDRESS', 'c.address', $this->sortDirection, $this->sortColumn);
                    echo '</th>';
                }

                if ($this->params->get('zip_code')) {
                    echo '<th class="" id="">';
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_POSTAL_CODE', 'c.zipcode', $this->sortDirection, $this->sortColumn);
                    echo '</th>';
                }

                if ($this->params->get('city')) {
                    echo '<th class="" id="">';
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_TOWN', 'c.location', $this->sortDirection, $this->sortColumn);
                    echo '</th>';
                }

                if ($this->params->get('country')) {
                    echo '<th class="" id="">';
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_COUNTRY', 'c.country', $this->sortDirection, $this->sortColumn);
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
                        $routeparameter = array();
                        $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database', 0);
                        $routeparameter['s'] = JFactory::getApplication()->input->getInt('s', 0);
                        $routeparameter['p'] = $item->projectslug;
                        $routeparameter['tid'] = $item->slug;
                        $routeparameter['ptid'] = 0;
                        $link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);
                        echo JHtml::link($link, $item->name);
                    } else {
                        echo $item->name;
                    }

                    if (!JFile::exists(JPATH_SITE . DS . $item->picture)) {
                        $item->picture = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");
                    }
                    ?>
                </td>

                <?PHP
                if ($this->params->get('picture')) {
                    echo '<td>';
                    echo sportsmanagementHelperHtml::getBootstrapModalImage('allteams' . $item->id, $item->picture, $item->name, '20');
                    echo '</td>';
                }

                if ($this->params->get('website')) {
                    echo '<td>';
                    if($item->website){
                    echo JHtml::link($item->website, $item->website, array('target' => '_blank'));
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

        <tfoot><tr><td colspan="10"><?php //echo $this->pagination->getListFooter();  ?></td></tr></tfoot>
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
    <?php //echo $this->pagination->getListFooter();  ?>
</div>