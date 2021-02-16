<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage playground
 * @file       default_playground.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="playground_default">
    <table class="table">
        <tr class="">
            <th colspan="2">
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_DATA');
				?>
            </th>
        </tr>
		<?php if (($this->config['show_shortname']) == 1) { ?>
            <tr>
                <th class="" width="">

					<?php
					echo Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_SHORT');
					?>

                </th>
                <td width="">
					<?php
					echo $this->playground->short_name;
					?>
                </td>
            </tr>
		<?php } ?>

		<?php
		if (($this->playground->address)
			|| ($this->playground->zipcode)
		)
		{
			?>
            <tr>
                <th class="" width=''><?php echo Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_ADDRESS'); ?></th>
                <td width=''>
					<?php
					echo JSMCountries::convertAddressString(
						'',
						$this->playground->address,
						'',
						$this->playground->zipcode,
						$this->playground->city,
						$this->playground->country,
						'COM_SPORTSMANAGEMENT_PLAYGROUND_ADDRESS_FORM'
					);
					?>
                </td>
            </tr>
			<?php
		}
		?>

		<?php
		if ($this->playground->website)
		{
			?>
            <tr>
                <th class="" width="">
					<?php echo Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_WEBSITE'); ?>
                </th>
                <td>
					<?php
					echo HTMLHelper::link($this->playground->website, $this->playground->website, array('target' => '_blank'));
					?>
                </td>
            </tr>
			<?php
		}
		?>

		<?php
		if ($this->playground->max_visitors)
		{
			?>
            <tr>
                <th class="" width="">

					<?php
					echo Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_MAX_VISITORS');
					?>

                </th>
                <td>
					<?php
					echo $this->playground->max_visitors;
					?>
                </td>
            </tr>
			<?php
		}
		?>
    </table>
</div>
<br/>
