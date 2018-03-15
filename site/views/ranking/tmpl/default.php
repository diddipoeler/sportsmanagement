<?php
/** SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 * @version   1.0.05
 * @file      deafult.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage ranking
 */

  defined('_JEXEC') or die('Restricted access');

  JHtml::_('behavior.switcher');
  JHtml::_('behavior.modal');

  // Make sure that in case extensions are written for mentioned (common) views,
  // that they are loaded i.s.o. of the template of this view
  $templatesToLoad = array('globalviews');
  sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
  $this->kmlpath = JURI::root().'tmp'.DS.$this->project->id.'-ranking.kml';
  $this->kmlfile = $this->project->id.'-ranking.kml';

  if( version_compare(JSM_JVERSION,'4','eq') )
  {
  echo $this->loadTemplate('joomla_vier');
  }
  else
  {

  if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
  {
  $my_text = 'config <pre>'.print_r($this->config,true).'</pre>';
  $my_text .= 'project<pre>'.print_r($this->project,true).'</pre>';
  $my_text .= 'teams<pre>'.print_r($this->teams,true).'</pre>';

  //$my_text .= 'player view teams <pre>'.print_r($this->teams,true).'</pre>';
  //$my_text .= 'player view person_position <pre>'.print_r($this->person_position,true).'</pre>';
  //$my_text .= 'player view person_parent_positions <pre>'.print_r($this->person_parent_positions,true).'</pre>';
  //$my_text .= 'stats <br><pre>'.print_r($this->stats,true).'</pre>';
  //$my_text .= 'gamesstats <br><pre>'.print_r($this->gamesstats,true).'</pre>';
  //
  //$my_text .= 'historyPlayer <br><pre>'.print_r($this->historyPlayer,true).'</pre>';
  //
  //$my_text .= 'person_position <pre>'.print_r($this->person_position,true).'</pre>';
  //$my_text .= 'person_parent_positions <pre>'.print_r($this->person_parent_positions,true).'</pre>';
  //$my_text .= 'position_name <pre>'.print_r($this->teamPlayer->position_name,true).'</pre>';

  sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,'sportsmanagementViewRankingdefault',__LINE__,$my_text);

  }


  ?>
  <script>



  </script>

  <div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>">
  <?php

  if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
  {
  echo $this->loadTemplate('debug');
  }

  echo $this->loadTemplate('projectheading');

  if ($this->config['show_sectionheader'])
  {
  echo $this->loadTemplate('sectionheader');
  }

  if ( $this->config['show_rankingnav'] )
  {
  echo $this->loadTemplate('rankingnav');
  }

  if ( $this->config['show_ranking'] )
  {
  /**
 * sollen überhaup reiter angezeigt werden ?
 */
if ($this->config['show_table_1'] ||
        $this->config['show_table_2'] ||
        $this->config['show_table_3'] ||
        $this->config['show_table_4'] ||
        $this->config['show_table_5']) {
    ?>
    <!-- This is a list with tabs names. -->
    <div class="panel with-nav-tabs panel-default">
        <div class="panel-heading">
            <!-- Tabs-Navs -->
            <ul class="nav nav-tabs" role="tablist">
    <?PHP
    if ($this->config['show_table_1']) {
        ?>
                    <li role="presentation" class="active"><a href="#<?PHP echo JText::_($this->config['table_text_1']); ?>" role="tab" data-toggle="tab"><?PHP echo JText::_($this->config['table_text_1']); ?></a>
                    </li>
                    <?PHP
                }

                if ($this->config['show_table_2']) {
                    ?>
                    <li role="presentation" class=""><a href="#<?PHP echo JText::_($this->config['table_text_2']); ?>" role="tab" data-toggle="tab"><?PHP echo JText::_($this->config['table_text_2']); ?></a>
                    </li>
                    <?PHP
                }

                if ($this->config['show_table_3']) {
                    ?>
                    <li role="presentation" class=""><a href="#<?PHP echo JText::_($this->config['table_text_3']); ?>" role="tab" data-toggle="tab"><?PHP echo JText::_($this->config['table_text_3']); ?></a>
                    </li>
                    <?PHP
                }

                if ($this->config['show_table_4']) {
                    ?>
                    <li role="presentation" class=""><a href="#<?PHP echo JText::_($this->config['table_text_4']); ?>" role="tab" data-toggle="tab"><?PHP echo JText::_($this->config['table_text_4']); ?></a>
                    </li>
                    <?PHP
                }

                if ($this->config['show_table_5']) {
                    ?>
                    <li role="presentation" class=""><a href="#<?PHP echo JText::_($this->config['table_text_5']); ?>" role="tab" data-toggle="tab"><?PHP echo JText::_($this->config['table_text_5']); ?></a>
                    </li>
                    <?PHP
                }
                ?>
            </ul>
        </div>
        <!-- Tab-Inhalte -->
        <div class="panel-body">
            <div class="tab-content">

                <?PHP
                if ($this->config['show_table_1']) {
                    ?>
                    <div role="tabpanel" class="tab-pane fade in active" id="<?PHP echo JText::_($this->config['table_text_1']); ?>">
        <?PHP
        echo $this->loadTemplate('ranking');
        ?>
                    </div>
                    <?PHP
                }

                if ($this->config['show_table_2']) {
                    ?>
                    <div role="tabpanel" class="tab-pane fade" id="<?PHP echo JText::_($this->config['table_text_2']); ?>">
                        <?PHP
                        echo $this->loadTemplate('ranking_home');
                        ?>
                    </div>
                    <?PHP
                }

                if ($this->config['show_table_3']) {
                    ?>
                    <div role="tabpanel" class="tab-pane fade" id="<?PHP echo JText::_($this->config['table_text_3']); ?>">
                        <?PHP
                        echo $this->loadTemplate('ranking_away');
                        ?>
                    </div>
                    <?PHP
                }

                if ($this->config['show_table_4']) {
                    ?>
                    <div role="tabpanel" class="tab-pane fade" id="<?PHP echo JText::_($this->config['table_text_4']); ?>">
                        <?PHP
                        echo $this->loadTemplate('ranking_first');
                        ?>
                    </div>
                    <?PHP
                }

                if ($this->config['show_table_5']) {
                    ?>
                    <div role="tabpanel" class="tab-pane fade" id="<?PHP echo JText::_($this->config['table_text_5']); ?>">
                        <?PHP
                        echo $this->loadTemplate('ranking_second');
                        ?>
                    </div>
                    <?PHP
                }
                ?>
            </div>

        </div>

    </div>

                    <?PHP
                } else {
                    echo $this->loadTemplate('ranking');
                }

                }

                if ($this->config['show_colorlegend']) {
                    echo $this->loadTemplate('colorlegend');
                }

                if ($this->config['show_explanation']) {
                    echo $this->loadTemplate('explanation');
                }

                if ($this->config['show_pagnav']) {
                    echo $this->loadTemplate('pagnav');
                }

                if ($this->config['show_projectinfo']) {
                    echo $this->loadTemplate('projectinfo');
                }

                if ($this->config['show_notes']) {
                    echo $this->loadTemplate('notes');
                }

                if ($this->config['show_ranking_maps']) {
                    echo $this->loadTemplate('googlemap');
                }

                if ($this->config['show_help']) {
                    echo $this->loadTemplate('hint');
                }

                if ($this->overallconfig['show_project_rss_feed']) {
                    //if ( !empty($this->rssfeedoutput) )
//       {
//       echo $this->loadTemplate('rssfeed-table'); 
//       }
                    if ($this->rssfeeditems) {
                        echo $this->loadTemplate('rssfeed');
                    }
                }
                ?>
<div>
<?PHP
echo $this->loadTemplate('backbutton');
echo $this->loadTemplate('footer');
?>
</div>
</div>
<?PHP
}
?>	
