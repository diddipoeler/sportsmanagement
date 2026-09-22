<?php
/**
 * Native Joomla 5/6 prediction rules layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (!$this->predictionGame) {
    echo '<div class="alert alert-warning">' . Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING') . '</div>';
    return;
}
?>
<div class="row-fluid">
    <?php
    echo $this->loadTemplate('predictionheading');
    echo $this->loadTemplate('sectionheader');
    echo $this->loadTemplate('info');
    echo $this->loadTemplate('jsminfo');
    ?>
</div>
