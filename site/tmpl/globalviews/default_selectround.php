<?php
/**
 * Native Joomla 5/6 round selector layout.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage resultsranking
 * @file       default_selectround.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

$this->getDocument()->getWebAssetManager()->registerAndUseScript(
    'com_sportsmanagement.site.selectround',
    'components/com_sportsmanagement/assets/js/selectround.js',
    ['version' => 'auto'],
    ['defer' => true]
);
?>
<div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?>" id="selectround">
    <?php
    echo HTMLHelper::_(
        'select.genericlist',
        $this->matchdaysoptions,
        'select-round',
        'class="form-select w-auto ms-auto" data-jsm-selectround',
        'value',
        'text',
        $this->currenturl
    );
    ?>
</div>
