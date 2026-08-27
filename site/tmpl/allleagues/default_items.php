<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage allleagues
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

<div class="<?php echo $this->divclassrow; ?> table-responsive" id="allleagues">
    <table class="<?php echo $this->tableclass; ?>">
        <thead>
        <tr>
            <th class="" id="">
                <?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ALL_LEAGUES', 'v.name', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th class="" id="">
                <?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_IMAGE', 'v.picture', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th class="" id="">
                <?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_COUNTRY', 'v.country', $this->sortDirection, $this->sortColumn); ?>
            </th>
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
                    if ($item->country)
                    {
                        $link = SiteRouteHelper::view('allprojects', [
                            'filter_search_nation' => $item->country,
                            'filter_search_leagues' => $item->id,
                        ]);
                        echo HTMLHelper::link($link, $item->name);
                    }
                    else
                    {
                        echo $item->name;
                    }
                    ?>
                </td>
                <td>
                    <?php
                    echo ModalImageHelper::render(
                        'allleagues' . (int) $item->id,
                        $pictureUrl($picture),
                        (string) $item->name,
                        20,
                        '',
                        $this->modalwidth,
                        $this->modalheight,
                        $this->use_jquery_modal
                    );
                    ?>
                </td>
                <td>
                    <?php echo CountryPresentationHelper::flag((string) $item->country); ?>
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
