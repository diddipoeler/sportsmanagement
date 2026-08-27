<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage allclubs
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
$pictureServer = \defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')
    ? (string) COM_SPORTSMANAGEMENT_PICTURE_SERVER
    : Uri::root();
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
<div class="row-fluid table-responsive">
    <table class="<?php echo $this->tableclass; ?>">
        <thead>
        <tr>
            <th class="" id="">
                <?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ALL_CLUBS', 'v.name', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <?php
            if ($this->user->id)
            {
                ?>
                <th class="" id="">
                    <?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_CLUBINFO_UNIQUE_ID', 'v.unique_id', $this->sortDirection, $this->sortColumn); ?>
                </th>
                <?php
            }

            if ($this->params->get('picture'))
            {
                echo '<th class="" id="">';
                echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_IMAGE', 'v.logo_big', $this->sortDirection, $this->sortColumn);
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
                echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_ADDRESS', 'v.address', $this->sortDirection, $this->sortColumn);
                echo '</th>';
            }

            if ($this->params->get('zip_code'))
            {
                echo '<th class="" id="">';
                echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_POSTAL_CODE', 'v.zipcode', $this->sortDirection, $this->sortColumn);
                echo '</th>';
            }

            if ($this->params->get('city'))
            {
                echo '<th class="" id="">';
                echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_TOWN', 'v.location', $this->sortDirection, $this->sortColumn);
                echo '</th>';
            }

            if ($this->params->get('country'))
            {
                echo '<th class="" id="">';
                echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_COUNTRY', 'v.country', $this->sortDirection, $this->sortColumn);
                echo '</th>';
            }

            if ($this->params->get('phone'))
            {
                echo '<th class="" id="">';
                echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_PHONE', 'v.phone', $this->sortDirection, $this->sortColumn);
                echo '</th>';
            }

            if ($this->params->get('email'))
            {
                echo '<th class="" id="">';
                echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_MAIL', 'v.email', $this->sortDirection, $this->sortColumn);
                echo '</th>';
            }
            ?>
        </tr>
        </thead>
        <?php foreach ($this->items as $i => $item) : ?>
            <?php
            $logo = trim((string) ($item->logo_big ?? ''));
            if (
                $logo === ''
                || (!preg_match('#^https?://#i', $logo) && !is_file(JPATH_SITE . '/' . ltrim($logo, '/')))
            ) {
                $logo = trim((string) $componentParams->get('ph_logo_big', ''));
            }
            ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td>
                    <?php
                    if ($item->projectslug)
                    {
                        $link = SiteRouteHelper::view('clubinfo', [
                            'p' => $item->projectslug,
                            'cid' => $item->slug,
                        ]);
                        echo HTMLHelper::link($link, $item->name);
                    }
                    else
                    {
                        echo $item->name;
                    }
                    ?>
                </td>
                <?php if ($this->user->id) : ?>
                    <td><?php echo $item->unique_id; ?></td>
                <?php endif; ?>

                <?php
                if ($this->params->get('picture'))
                {
                    echo '<td>';
                    echo ModalImageHelper::render(
                        'allclub' . (int) $item->id,
                        $pictureUrl($logo),
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
                        echo HTMLHelper::link($item->website, $item->website, ['target' => '_blank', 'rel' => 'noopener']);
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

                if ($this->params->get('phone'))
                {
                    echo '<td>' . $item->phone . '</td>';
                }

                if ($this->params->get('email'))
                {
                    echo '<td>' . $item->email . '</td>';
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
