<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teamstree
 */
defined('_JEXEC') or die('Restricted access');

if ($this->familyteamstree) {
                    $class_collapse = 'collapse in';
                } else {
                    $class_collapse = 'collapse';
                }

foreach( $this->familyteamstree as $rowclub => $rowvalue )
{
?>
<a href="#fusion<?php echo $rowclub; ?>" class="btn btn-info btn-block" data-toggle="collapse">
                    <strong>
                        <?php echo JText::_($this->familyclub[$rowclub]->name); ?>
                    </strong>
                </a>
                <div id="fusion<?php echo $rowclub; ?>" class="<?PHP echo $class_collapse; ?>">
                    <div class="tree">

                        <ul>
                            <li>
                                <?php
                                if (!$this->config['show_bootstrap_tree']) {
                                    ?>
                                    <span><i class="icon-folder-open"></i> aktueller Verein</span>	
                                    <?php
                                }
  $color = array_key_exists($rowclub, $this->findclub) ? 'lawngreen' : '';
                                ?>	
                              <span style="background-color:<?php echo $color;?>;">
                                <a href="<?php echo $this->familyclub[$rowclub]->clublink;?>"><?PHP echo JHTML::image($this->familyclub[$rowclub]->logo_big, $this->familyclub[$rowclub]->club_name, 'width="30"') . ' ' . $this->familyclub[$rowclub]->club_name; ?></a>
                                </span>	
                              <?php
                                echo $rowvalue;
                                ?>
                            </li>
                        </ul>
                    </div>
                </div>
<?php
}
?>
