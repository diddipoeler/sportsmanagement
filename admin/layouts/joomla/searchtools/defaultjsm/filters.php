<?php
/**
 * SportsManagement Joomla 5/6 searchtools filter layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Form\FormHelper;

$data = $displayData;

// Load the form filters
$filters = $data['view']->filterForm->getGroup('filter');

$document = SportsManagementAdministratorApplicationResolver::resolve()->getDocument();
$wa = $document instanceof HtmlDocument ? $document->getWebAssetManager() : null;

?>
<?php if ($filters) : ?>
	<?php foreach ($filters as $fieldName => $field) : ?>
		<?php if ($fieldName !== 'filter_search') : ?>
			<?php $dataShowOn = ''; ?>
			<?php if ($field->showon) : ?>
				<?php if ($wa !== null) : ?>
					<?php $wa->useScript('showon'); ?>
				<?php endif; ?>
				<?php $dataShowOn = " data-showon='" . json_encode(FormHelper::parseShowOnConditions($field->showon, $field->formControl, $field->group)) . "'"; ?>
			<?php endif; ?>
			<div class="js-stools-field-filter"<?php echo $dataShowOn; ?>>
				<span class="visually-hidden"><?php echo $field->label; ?></span>
				<?php echo $field->input; ?>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>
