<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_details.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage matchreport
 */

defined('_JEXEC') or die('Restricted access'); 
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

?>
<!-- Details-->
<h2>
<?php 
echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_DETAILS'); 
?>
</h2>
<div class="<?php echo $this->divclassrow;?>" id="matchreport-details">
    <!-- Prev Match-->
    <?php
    if ($this->match->old_match_id > 0) {
        ?>
        <address>
         <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_OLD_MATCH'); ?></strong>
            <?php echo HTMLHelper::link(sportsmanagementHelperRoute::getNextMatchRoute($this->project->slug, $this->match->old_match_id), $this->oldmatchtext); ?>
            </address>
        <?php
    }
    ?>
    <!-- Next Match-->
    <?php
    if ($this->match->new_match_id > 0) {
        ?>
        <address>
         <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_NEW_MATCH'); ?></strong>
            <?php echo HTMLHelper::link(sportsmanagementHelperRoute::getNextMatchRoute($this->project->slug, $this->match->new_match_id), $this->newmatchtext); ?>
            </address>
        <?php
    }
    ?>    
    <!-- Date -->
    <?php
    $timestamp = strtotime($this->match->match_date);
    if ($this->config['show_match_date'] ) {
        if ($this->match->match_date != '0000-00-00 00:00:00' ) {
            ?>
              <div class="<?php echo $this->divclassrow;?>">
<div class="col-xs-<?php echo $this->config['extended_cols'];?> col-sm-<?php echo $this->config['extended_cols'];?> col-md-<?php echo $this->config['extended_cols'];?> col-lg-<?php echo $this->config['extended_cols'];?>">
<div class="col-xs-<?php echo $this->config['extended_description_cols'];?> col-sm-<?php echo $this->config['extended_description_cols'];?> col-md-<?php echo $this->config['extended_description_cols'];?> col-lg-<?php echo $this->config['extended_description_cols'];?>">
            
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_DATE'); ?></strong>
              </div>
<div class="col-xs-<?php echo $this->config['extended_description_cols'];?> col-sm-<?php echo $this->config['extended_description_cols'];?> col-md-<?php echo $this->config['extended_description_cols'];?> col-lg-<?php echo $this->config['extended_description_cols'];?>">
                            
                <?php echo HTMLHelper::date($this->match->match_date, Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_GAMES_DATE')); ?>
            </div>
                </div>
                </div>
            <?php
        }
        else
        {
            ?>
 <div class="<?php echo $this->divclassrow;?>">
<div class="col-xs-<?php echo $this->config['extended_cols'];?> col-sm-<?php echo $this->config['extended_cols'];?> col-md-<?php echo $this->config['extended_cols'];?> col-lg-<?php echo $this->config['extended_cols'];?>">
<div class="col-xs-<?php echo $this->config['extended_description_cols'];?> col-sm-<?php echo $this->config['extended_description_cols'];?> col-md-<?php echo $this->config['extended_description_cols'];?> col-lg-<?php echo $this->config['extended_description_cols'];?>">
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_DATE'); ?></strong>
              </div>
<div class="col-xs-<?php echo $this->config['extended_value_cols'];?> col-sm-<?php echo $this->config['extended_value_cols'];?> col-md-<?php echo $this->config['extended_value_cols'];?> col-lg-<?php echo $this->config['extended_value_cols'];?>">
    <?php echo ''; ?>
</div>
                </div>
                </div>
            <?php
        }
    }
    ?>

    <!-- Time -->
    <?php
    if ($this->config['show_match_time'] ) {
        if ($this->match->match_date != '0000-00-00 00:00:00' ) {
            ?>
             <div class="<?php echo $this->divclassrow;?>">
<div class="col-xs-<?php echo $this->config['extended_cols'];?> col-sm-<?php echo $this->config['extended_cols'];?> col-md-<?php echo $this->config['extended_cols'];?> col-lg-<?php echo $this->config['extended_cols'];?>">
<div class="col-xs-<?php echo $this->config['extended_description_cols'];?> col-sm-<?php echo $this->config['extended_description_cols'];?> col-md-<?php echo $this->config['extended_description_cols'];?> col-lg-<?php echo $this->config['extended_description_cols'];?>">
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TIME'); ?></strong>
              </div>
<div class="col-xs-<?php echo $this->config['extended_value_cols'];?> col-sm-<?php echo $this->config['extended_value_cols'];?> col-md-<?php echo $this->config['extended_value_cols'];?> col-lg-<?php echo $this->config['extended_value_cols'];?>">

    <?php echo sportsmanagementHelperHtml::showMatchTime($this->match, $this->config, $this->overallconfig, $this->project); ?>
            </div>
                </div>
                </div>
            <?php
        }
        else
        {
            ?>
<div class="<?php echo $this->divclassrow;?>">
<div class="col-xs-<?php echo $this->config['extended_cols'];?> col-sm-<?php echo $this->config['extended_cols'];?> col-md-<?php echo $this->config['extended_cols'];?> col-lg-<?php echo $this->config['extended_cols'];?>">
<div class="col-xs-<?php echo $this->config['extended_description_cols'];?> col-sm-<?php echo $this->config['extended_description_cols'];?> col-md-<?php echo $this->config['extended_description_cols'];?> col-lg-<?php echo $this->config['extended_description_cols'];?>">
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TIME'); ?></strong>
              </div>
<div class="col-xs-<?php echo $this->config['extended_value_cols'];?> col-sm-<?php echo $this->config['extended_value_cols'];?> col-md-<?php echo $this->config['extended_value_cols'];?> col-lg-<?php echo $this->config['extended_value_cols'];?>">
    <?php echo ''; ?>
</div>
                </div>
                </div>
            <?php
        }
    ?>

        <!-- present -->
        <?php if ($this->match->time_present > 0) : ?>
<div class="<?php echo $this->divclassrow;?>">
<div class="col-xs-<?php echo $this->config['extended_cols'];?> col-sm-<?php echo $this->config['extended_cols'];?> col-md-<?php echo $this->config['extended_cols'];?> col-lg-<?php echo $this->config['extended_cols'];?>">
<div class="col-xs-<?php echo $this->config['extended_description_cols'];?> col-sm-<?php echo $this->config['extended_description_cols'];?> col-md-<?php echo $this->config['extended_description_cols'];?> col-lg-<?php echo $this->config['extended_description_cols'];?>">
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_PRESENT'); ?></strong>
              </div>
<div class="col-xs-<?php echo $this->config['extended_value_cols'];?> col-sm-<?php echo $this->config['extended_value_cols'];?> col-md-<?php echo $this->config['extended_value_cols'];?> col-lg-<?php echo $this->config['extended_value_cols'];?>">        
        

    <?php echo $this->match->time_present; ?>
</div>
                </div>
                </div>
        <?php endif;
    
    }
    ?>

    <!-- match number -->
    <?php
    if ($this->config['show_match_number'] ) {
        if ($this->match->match_number > 0) : ?>
<div class="<?php echo $this->divclassrow;?>">
<div class="col-xs-<?php echo $this->config['extended_cols'];?> col-sm-<?php echo $this->config['extended_cols'];?> col-md-<?php echo $this->config['extended_cols'];?> col-lg-<?php echo $this->config['extended_cols'];?>">
<div class="col-xs-<?php echo $this->config['extended_description_cols'];?> col-sm-<?php echo $this->config['extended_description_cols'];?> col-md-<?php echo $this->config['extended_description_cols'];?> col-lg-<?php echo $this->config['extended_description_cols'];?>">
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_NUMBER'); ?></strong>
              </div>
<div class="col-xs-<?php echo $this->config['extended_value_cols'];?> col-sm-<?php echo $this->config['extended_value_cols'];?> col-md-<?php echo $this->config['extended_value_cols'];?> col-lg-<?php echo $this->config['extended_value_cols'];?>">       
    <?php echo $this->match->match_number; ?>
</div>
                </div>
                </div>
        
        <?php endif;
    }
    ?>
    <!-- playground -->
    <?php
    if ($this->config['show_match_playground'] ) {
        if ($this->match->playground_id > 0) : ?>
        <?php 
        $routeparameter = array();
        $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
        $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
        $routeparameter['p'] = $this->project->slug;
        $routeparameter['pgid'] = $this->match->playground_slug;
        $playground_link = sportsmanagementHelperRoute::getSportsmanagementRoute('playground', $routeparameter);

        ?>
<div class="<?php echo $this->divclassrow;?>">
<div class="col-xs-<?php echo $this->config['extended_cols'];?> col-sm-<?php echo $this->config['extended_cols'];?> col-md-<?php echo $this->config['extended_cols'];?> col-lg-<?php echo $this->config['extended_cols'];?>">
<div class="col-xs-<?php echo $this->config['extended_description_cols'];?> col-sm-<?php echo $this->config['extended_description_cols'];?> col-md-<?php echo $this->config['extended_description_cols'];?> col-lg-<?php echo $this->config['extended_description_cols'];?>">
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_PLAYGROUND'); ?></strong>
              </div>
<div class="col-xs-<?php echo $this->config['extended_value_cols'];?> col-sm-<?php echo $this->config['extended_value_cols'];?> col-md-<?php echo $this->config['extended_value_cols'];?> col-lg-<?php echo $this->config['extended_value_cols'];?>">        
    <?php 
    if (isset($this->playground->name) ) { 
        echo HTMLHelper::link($playground_link, $this->playground->name);
    }
    else
                    {
        echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_PLAYGROUND_NO_ASSIGN');    
    } 
    if ($this->config["show_playground_picture"] ) {
        echo sportsmanagementHelperHtml::getBootstrapModalImage(
            'matchpg'.$this->match->playground_id,
            $this->match->playground_picture,
            $this->match->playground_name,
            $this->config['playground_picture_width'],
            '',
            $this->modalwidth,
            $this->modalheight,
            $this->overallconfig['use_jquery_modal']
        );
    }                     
                    ?>
</div>
                </div>
                </div>
        
        <?php endif;
    }
    ?>
    <!-- referees -->
    <?php
    if ($this->config['show_match_referees'] ) {    
        if ($this->matchreferees ) {
            ?>
<div class="<?php echo $this->divclassrow;?>">
<div class="col-xs-<?php echo $this->config['extended_cols'];?> col-sm-<?php echo $this->config['extended_cols'];?> col-md-<?php echo $this->config['extended_cols'];?> col-lg-<?php echo $this->config['extended_cols'];?>">
<div class="col-xs-<?php echo $this->config['extended_description_cols'];?> col-sm-<?php echo $this->config['extended_description_cols'];?> col-md-<?php echo $this->config['extended_description_cols'];?> col-lg-<?php echo $this->config['extended_description_cols'];?>">
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_REFEREE'); ?></strong>
              </div>
<div class="col-xs-<?php echo $this->config['extended_value_cols'];?> col-sm-<?php echo $this->config['extended_value_cols'];?> col-md-<?php echo $this->config['extended_value_cols'];?> col-lg-<?php echo $this->config['extended_value_cols'];?>">                        
    <?php 
            
            $first = true;
    foreach ( $this->matchreferees as $referee ) : 
        $routeparameter = array();
        $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
        $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
        $routeparameter['p'] = $this->project->slug;
        $routeparameter['pid'] = $referee->person_slug;
        $referee_link = sportsmanagementHelperRoute::getSportsmanagementRoute('referee', $routeparameter);

        if (!$first) {
            echo ', ';
        }
        $referee_name = sportsmanagementHelper::formatName(null, $referee->firstname, $referee->nickname, $referee->lastname, $this->config["name_format"]);
        $link = HTMLHelper::link($referee_link, $referee_name);
        if ($this->config["show_referee_position"] == 1) { $link .= ' ('.$referee->position_name.')';
        }
        echo $link; 
        $first = false;
        if ($this->config["show_referee_picture"] ) {
                echo sportsmanagementHelperHtml::getBootstrapModalImage(
                    'matchreferee'.$referee->id,
                    $referee->picture,
                    $referee_name,
                    $this->config['referee_picture_width'],
                    '',
                    $this->modalwidth,
                    $this->modalheight,
                    $this->overallconfig['use_jquery_modal']
                );                        
        }                        
    endforeach;    
            
            ?>
</div>
                </div>
                </div>
                       
            <?php
        }
    }
    ?>
    <!-- crowd -->
    <?php
    if ($this->config['show_match_crowd'] ) {
        if ($this->match->crowd > 0 ) : ?>
<div class="<?php echo $this->divclassrow;?>">
<div class="col-xs-<?php echo $this->config['extended_cols'];?> col-sm-<?php echo $this->config['extended_cols'];?> col-md-<?php echo $this->config['extended_cols'];?> col-lg-<?php echo $this->config['extended_cols'];?>">
<div class="col-xs-<?php echo $this->config['extended_description_cols'];?> col-sm-<?php echo $this->config['extended_description_cols'];?> col-md-<?php echo $this->config['extended_description_cols'];?> col-lg-<?php echo $this->config['extended_description_cols'];?>">
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_ATTENDANCES'); ?></strong>
              </div>
<div class="col-xs-<?php echo $this->config['extended_value_cols'];?> col-sm-<?php echo $this->config['extended_value_cols'];?> col-md-<?php echo $this->config['extended_value_cols'];?> col-lg-<?php echo $this->config['extended_value_cols'];?>">
    <?php echo ': ' . number_format($this->match->crowd, 0, ',', '.'); ?>
            </div>
                </div>
                </div>
            
        <?php endif;
    }
    ?>

</div>
<br/>
