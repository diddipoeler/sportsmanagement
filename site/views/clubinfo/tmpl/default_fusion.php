<?php

/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_fusion.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;

?>
<div class="<?php echo $this->divclassrow; ?>" id="default_fusion">
	<?php
	$this->notes = array();
	$this->notes[] = Text::_('Fusionen');
	echo $this->loadTemplate('jsm_notes');
	?>
	<div class="panel-group" id="<?php echo $this->club->name; ?>">
		<div class="panel panel-default">
			<div id="<?php echo $this->club->id; ?>" class="panel-collapse collapse in">
				<div class="panel-body">

					<div class="tree" style="display: flow-root;">
						<ul>
							<li>
								<?php
								if (!$this->config['show_bootstrap_tree'])
								{
								?>
									<span><i class="icon-folder-open"></i> aktueller Verein</span>
								<?php
								}
								?>
								<a href="#"><?PHP echo HTMLHelper::image($this->club->logo_big, $this->club->name, 'width="30"') . ' ' . $this->club->name; ?></a>
								<?php
								echo $this->familytree;
								?>
							</li>
						</ul>
					</div>

				</div>

			</div>

		</div>



	</div>

</div>