<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage playground
 * @file       default_picture.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Joomla\CMS\Language\Text;

if (empty($this->playground->picture)) {
    return;
}

$this->notes = [Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_CLUB_PICTURE')];
echo $this->loadTemplate('jsm_notes');

$picture = COM_SPORTSMANAGEMENT_PICTURE_SERVER . ltrim((string) $this->playground->picture, '/');
?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="playground_picture">
    <?php
    echo ModalImageHelper::render(
        'playground' . (int) $this->playground->id,
        $picture,
        (string) ($this->playground->name ?? ''),
        (int) ($this->config['playground_picture_width'] ?? 150),
        '',
        $this->modalwidth,
        $this->modalheight,
        (int) ($this->overallconfig['use_jquery_modal'] ?? 0)
    );
    ?>
</div>
