<?php
/**
 * Native Joomla 5/6 JoomLeague import progress layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$requestUrl = htmlspecialchars((string) $this->request_url, ENT_QUOTES, 'UTF-8');
$task = htmlspecialchars((string) $this->task, ENT_QUOTES, 'UTF-8');
$step = (int) $this->step;
$totals = (int) $this->totals;
$barValue = (float) $this->bar_value;
?>
<form action="<?php echo $requestUrl; ?>" method="post" id="adminForm" name="adminForm">
    <p class="nowarning"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_INPROGRESS'); ?></p>
    <div class="joomlaupdate_spinner"></div>

    <div id="progressbar">
        <div class="progress-label">
            <?php echo $task; ?>
        </div>
    </div>

    <input type="hidden" name="step" value="<?php echo $step; ?>">
    <input type="hidden" name="totals" value="<?php echo $totals; ?>">

    <?php if ($barValue < 100) : ?>
        <meta http-equiv="refresh" content="1; URL=<?php echo $requestUrl; ?>">
    <?php endif; ?>
</form>
