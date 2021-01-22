<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage nextmatch
 * @file       default_preview.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<!-- START of match preview -->
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="nextmatch-preview">
    <div class="panel-group" id="accordionnextmatch">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordionnextmatch"
                       href="#nextpreview"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_PREVIEW'); ?></a>
                </h4>
            </div>
            <div id="nextpreview" class="panel-collapse collapse">
                <div class="panel-body">
					<?php

					if (!empty($this->match->preview))
					{
						?>
                        <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_PREVIEW'); ?></h2>
                        <table class="table">
                            <tr>
                                <td><?php
									$preview = $this->match->preview;
									$preview = HTMLHelper::_('content.prepare', $preview);

									if ($commentsDisabled)
									{
										$preview = preg_replace('#{jcomments\s+(off|lock)}#is', '', $preview);
									}

									echo $preview;
									?>
                                </td>
                            </tr>
                        </table>
                        <!-- END of match preview -->

						<?php
					}
					?>
                </div>
            </div>
        </div>
    </div>
</div>
