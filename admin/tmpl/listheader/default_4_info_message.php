<?php
/**
 * Native Joomla 5/6 administrator information message for the list header.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

$message = isset($this->jsmmessage) ? (string) $this->jsmmessage : '';

if ($message === '') {
    return;
}
?>
<div class="alert alert-success" role="alert">
    <?php echo $message; ?>
</div>
