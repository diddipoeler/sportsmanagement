<?php
/**
 * Native Joomla 5/6 administrator club logo history layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$renderLogo = static function (object $item) use ($escape): string {
    $logoPath = ltrim((string) ($item->logo_big ?? ''), '/');

    if ($logoPath === '') {
        return '';
    }

    $logoUrl = Uri::root() . $logoPath;
    $modalId = 'jsm-club-logo-' . (int) ($item->id ?? 0);
    $title = 'Logo';
    $footer = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
        . Text::_('JCANCEL')
        . '</button>';

    $preview = '<a href="#' . $escape($modalId) . '" title="' . $escape($title) . '"'
        . ' data-bs-toggle="modal" data-bs-target="#' . $escape($modalId) . '">'
        . '<img src="' . $escape($logoUrl) . '" alt="' . $escape($title) . '" style="width:auto;height:20px">'
        . '</a>';

    $modal = HTMLHelper::_(
        'bootstrap.renderModal',
        $modalId,
        [
            'title' => $title,
            'url' => $logoUrl,
            'height' => 200,
            'width' => 100,
            'bodyHeight' => 60,
            'modalWidth' => 80,
            'footer' => $footer,
        ]
    );

    return $preview . $modal;
};

$form = new Form('clublogohistory');
$form->addFormPath(JPATH_COMPONENT . '/models/forms');
$form->loadFile('clublogohistory', false);
?>
<p>Hier können Sie die Wappen zu den Vereinen hinterlegen.</p>

<table class="table">
    <tr>
        <td><?php echo $form->renderFieldset('picture'); ?></td>
        <td><?php echo $form->renderFieldset('seasons'); ?></td>
    </tr>
</table>

<table class="table table-striped" id="clublogos">
    <thead>
        <tr>
            <th>id</th>
            <th>seasonname</th>
            <th>logo_big</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (($this->logohistory ?? []) as $item) : ?>
            <tr>
                <td><?php echo (int) ($item->id ?? 0); ?></td>
                <td><?php echo $escape($item->seasonname ?? ''); ?></td>
                <td><?php echo $renderLogo($item); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
