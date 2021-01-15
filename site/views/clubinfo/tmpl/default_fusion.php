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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

if ($this->familytree)
					{
						$class_collapse = 'collapse in';
					}
					else
					{
						$class_collapse = 'collapse';
					}
					?>
<div class="<?php echo $this->divclassrow; ?>" itemscope="" itemtype="http://schema.org/SportsClub" id="default_clubinfo">
                    <a href="#fusion" class="btn btn-info btn-block" data-toggle="collapse">
                        <strong>
							<?php echo Text::_('Fusionen'); ?>
                        </strong>
                    </a>
                    <div id="fusion" class="<?PHP echo $class_collapse; ?>">
                        <div class="tree">

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
					<?php
