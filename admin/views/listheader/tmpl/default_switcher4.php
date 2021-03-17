<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage listheader
 * @file       default_switcher4.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Uri\Uri;

$this->document->addStyleSheet(Uri::root() . 'media/system/css/fields/switcher.css');    
//$attr = 'id="' . $this->item->id . '"';
$readonly = false;
$disabled = false;
$input = '<input type="radio" id="%1$s" name="%2$s" value="%3$s" %4$s '.$this->switcher_onchange.'   >';
?>    			
<fieldset <?php echo $this->switcher_attr; ?>>
	<legend class="visually-hidden">
		<?php echo $label; ?>
	</legend>
	<div class="switcher<?php echo ($readonly || $disabled ? ' disabled' : ''); ?>">
	<?php foreach ($this->switcher_options as $i => $option) : ?>
		<?php
		// False value casting as string returns an empty string so assign it 0
		if (empty($this->switcher_value) && $option->value == '0')
		{
			$this->switcher_value = '0';
		}

		// Initialize some option attributes.
		$optionValue = (string) $option->value;
		$optionId    = $this->switcher_item_id  . $i;
		$attributes  = $optionValue == $this->switcher_value ? 'checked class="active"' : '';
		$attributes  .= $optionValue != $this->switcher_value && $readonly || $disabled ? ' disabled' : '';
		?>
		<?php echo sprintf($input, $optionId, $this->switcher_name, $this->escape($optionValue), $attributes); ?>
		<?php echo '<label for="' . $optionId . '">' . $option->text . '</label>'; ?>
	<?php endforeach; ?>
	<span class="toggle-outside"><span class="toggle-inside"></span></span>
	</div>
</fieldset>    