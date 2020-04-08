<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage globalviews
 * @file       deafault_extrafields.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
?>

<div class="row-fluid">
<h4>
<?php echo Text::_('COM_SPORTSMANAGEMENT_EXTRA_FIELDS'); ?>
</h4>
</div>

<?php
if (isset($this->extrafields))
{
	foreach ($this->extrafields as $field)
	{
		$value = $field->fvalue;
		$field_type = $field->field_type;


		if (!empty($value)) // && !$field->backendonly)
		{
			?>
			<div class="col-xs-<?php echo $this->config['extended_cols'];?> col-sm-<?php echo $this->config['extended_cols'];?> col-md-<?php echo $this->config['extended_cols'];?> col-lg-<?php echo $this->config['extended_cols'];?>">
			<div class="col-xs-<?php echo $this->config['extended_description_cols'];?> col-sm-<?php echo $this->config['extended_description_cols'];?> col-md-<?php echo $this->config['extended_description_cols'];?> col-lg-<?php echo $this->config['extended_description_cols'];?>">
			<strong><?php echo Text::_($field->name); ?></strong>
			</div>
			<div class="col-xs-<?php echo $this->config['extended_value_cols'];?> col-sm-<?php echo $this->config['extended_value_cols'];?> col-md-<?php echo $this->config['extended_value_cols'];?> col-lg-<?php echo $this->config['extended_value_cols'];?>">
			<?php
			switch (Factory::getApplication()->input->getVar('view'))
			{
				case 'clubinfo':
					$title = $this->club->name;
				break;
			}


			switch ($field_type)
			{
				case 'link':
					echo HTMLHelper::_('link', $field->fvalue, $title,  array( "target" => "_blank" ));
					break;
				default:
					echo Text::_($field->fvalue);
					break;
			}


			?>
			</div>
			</div>
			<?php
		}
	}
}
