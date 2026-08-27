<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage allteams
 * @file       default_items.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

$componentParams = ComponentHelper::getParams('com_sportsmanagement');
$useExternalPictureServer = (bool) $this->params->get('cfg_dbprefix')
    || (bool) $this->params->get('cfg_which_database')
    || $this->databaseSelector === 1;
$pictureServer = $useExternalPictureServer
    ? trim((string) $this->params->get('cfg_which_database_server', ''))
    : Uri::root();
if ($pictureServer === '') {
    $pictureServer = Uri::root();
}
$pictureUrl = static function (string $picture) use ($pictureServer): string {
    $picture = trim($picture);

    if ($picture === '') {
        return '';
    }

    return preg_match('#^https?://#i', $picture)
        ? $picture
        : rtrim($pictureServer, '/') . '/' . ltrim($picture, '/');
};
?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="allteams">
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

        <?php foreach ($this->items as $i => $item) : ?>
            <?php
            $picture = trim((string) ($item->picture ?? ''));
            if (
                $picture === ''
                || (
                    !$useExternalPictureServer
                    && !preg_match('#^https?://#i', $picture)
                    && !is_file(JPATH_SITE . '/' . ltrim($picture, '/'))
                )
            ) {
                $picture = trim((string) $componentParams->get('ph_logo_big', ''));
            }
            ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td>
                    <?php
                    if ($item->projectslug)
                    {
                        $link = SiteRouteHelper::view('teaminfo', [
                            'cfg_which_database' => $this->databaseSelector,
                            's' => $this->input->getInt('s', 0),
                            'p' => $item->projectslug,
                            'tid' => $item->slug,
                            'ptid' => 0,
                        ]);
                        echo HTMLHelper::link($link, $item->name);
                    }
                    else
                    {
                        echo $item->name;
                    }
                    ?>
                </td>

                <?php
                if ($this->params->get('picture'))
                {
                    echo '<td>';
                    echo ModalImageHelper::render(
                        'allteams' . (int) $item->id,
                        $pictureUrl($picture),
                        (string) $item->name,
                        20,
                        '',
                        $this->modalwidth,
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
                        echo HTMLHelper::link(
                            (string) $item->website,
                            (string) $item->website,
                            ['target' => '_blank', 'rel' => 'noopener']
                        );
                    }

                    echo '</td>';
                }

                if ($this->params->get('address'))
                {
                    echo '<td>' . $item->address . '</td>';
                }

                if ($this->params->get('zip_code'))
                {
                    echo '<td>' . $item->zipcode . '</td>';
                }

                if ($this->params->get('city'))
                {
                    echo '<td>' . $item->location . '</td>';
                }

                if ($this->params->get('country'))
                {
                    echo '<td>' . CountryPresentationHelper::flag((string) $item->country) . '</td>';
                }
                ?>
            </tr>
        <?php endforeach; ?>

        <tfoot>
        <tr>
            <td colspan="10"></td>
        </tr>
        </tfoot>
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
