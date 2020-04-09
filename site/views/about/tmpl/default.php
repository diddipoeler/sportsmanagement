<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage about
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

?>
<table class="table table-responsive about">
    <tr>
        <td align="center">
			<?PHP
			// Reference global application object
			$app = Factory::getApplication();

			// JInput object
			$jinput          = $app->input;
			$option          = $jinput->getCmd('option');
			$backgroundimage = 'administrator/components/' . $option . '/assets/icons/logo_transparent.png';

			// Echo $backgroundimage.'<br>';

			echo "<img class=\"\" style=\"\" src=\"" . $backgroundimage . "\" alt=\"\" width=\"200\">";
			?>
        </td>
    </tr>
</table>
<br/>
<div class="componentheading">
	<?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT'); ?>
</div>
<table class="table table-responsive about">
    <tr>
        <td><?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_TEXT'); ?></td>
    </tr>
</table>
<br/>

<div class="componentheading">
	<?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_DIDDIPOELER'); ?>
</div>
<table class="table table-responsive about">
    <tr>
        <td>
            <b>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_TEXT_DIDDIPOELER'); ?>
            </b>
        </td>
        <td>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_TEXT_DESC_DIDDIPOELER'); ?>
        </td>
    </tr>
    <tr>
        <td>
            <b>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_WEBSITE_DIDDIPOELER'); ?>
            </b>
        </td>
        <td>
            <a href="<?php echo $this->about->diddipoelerpage; ?>" target="_blank">
				<?php echo $this->about->diddipoelerpage; ?>
            </a>
        </td>
    </tr>
    <tr>
        <td>
            <b>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_SUPPORT_FORUM_DIDDIPOELER'); ?>
            </b>
        </td>
        <td>
            <a href="<?php echo $this->about->diddipoelerforum; ?>" target="_blank">
				<?php echo "Fussballineuropa Forum"; ?>
            </a>
        </td>
    </tr>

    <tr>
        <td>
            <b>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_GITHUB_DIDDIPOELER'); ?>
            </b>
        </td>
        <td>
            <a href="<?php echo $this->about->github; ?>" target="_blank">
				<?php echo 'Github sportsmanagement diddipoeler'; ?>
            </a>
        </td>
    </tr>

    <tr>
        <td>
            <b>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_SUPPORT_EMAIL_DIDDIPOELER'); ?>
            </b>
        </td>
        <td>
            <a href="mailto:<?php echo $this->about->diddipoeleremail; ?>" target="_blank">
				<?php echo $this->about->diddipoeleremail; ?>
            </a>
        </td>
    </tr>
</table>

<div class="componentheading">
	<?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_DETAILS'); ?>
</div>

<table class="table table-responsive about">

    <tr>
        <td>
            <b>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_DESIGNER'); ?>
            </b>
        </td>
        <td>
			<?php echo $this->about->designer; ?>
        </td>
    </tr>
    <tr>
        <td>
            <b>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_DEVELOPERS'); ?>
            </b>
        </td>
        <td>
			<?php echo $this->about->developer; ?>
        </td>
    </tr>


</table>
<br/>

<div class="componentheading">
	<?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_LICENSE'); ?>
</div>

<table class="table table-responsive about">
    <tr>
        <td>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_LICENSE_TEXT'); ?>
        </td>
    </tr>
</table>

<!-- backbutton anfang -->
<?php

?>
<!-- backbutton ende -->
