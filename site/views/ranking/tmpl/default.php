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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

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
  
  ?>
  <script>

  </script>

  <div class="container" id="defaultranking">
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

    if ( $this->config['show_result_tabs'] == 'show_tabs' )
    {
    echo $this->loadTemplate('tabs');  
    }
    else
    {
  if ( $this->config['show_ranking'] )
  {
?>
<div class="row" id="ranking">
<?php    
/**
 * sollen überhaupt reiter angezeigt werden ?
 */
if ($this->config['show_table_1'] ||
        $this->config['show_table_2'] ||
        $this->config['show_table_3'] ||
        $this->config['show_table_4'] ||
        $this->config['show_table_5']) {
echo HTMLHelper::_('bootstrap.startTabSet', 'defaulttabsranking', array('active' => 'show_table_1' )); //start tab set    

if ($this->config['show_table_1']) {
echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_1', Text::_($this->config['table_text_1']));
echo $this->loadTemplate('ranking');  
echo HTMLHelper::_('bootstrap.endTab');  
}  
if ($this->config['show_table_2']) {
echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_2', Text::_($this->config['table_text_2']));
echo $this->loadTemplate('ranking_home');  
echo HTMLHelper::_('bootstrap.endTab');   
} 
if ($this->config['show_table_3']) {
echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_3', Text::_($this->config['table_text_3']));
echo $this->loadTemplate('ranking_away');  
echo HTMLHelper::_('bootstrap.endTab');   
} 
if ($this->config['show_table_4']) {
echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_4', Text::_($this->config['table_text_4']));
echo $this->loadTemplate('ranking_first');  
echo HTMLHelper::_('bootstrap.endTab');   
} 
if ($this->config['show_table_5']) {
echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_5', Text::_($this->config['table_text_5']));
echo $this->loadTemplate('ranking_second');  
echo HTMLHelper::_('bootstrap.endTab');   
}       
echo HTMLHelper::_('bootstrap.endTabSet'); //end tab set     
    
    
    
    ?>
</div>    

                    <?PHP
                } else {
echo HTMLHelper::_('bootstrap.startTabSet', 'defaulttabsranking', array('active' => 'ranking' )); //start tab set  					
echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', 'ranking', Text::_('COM_SPORTSMANAGEMENT_XML_RANKING_LAYOUT_TITLE'));					
echo $this->loadTemplate('ranking');
echo HTMLHelper::_('bootstrap.endTab');  					
echo HTMLHelper::_('bootstrap.endTabSet'); //end tab set 					
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
                    if ($this->rssfeeditems) {
                        echo $this->loadTemplate('rssfeed');
                    }
                }
    
  }    
    
echo $this->loadTemplate('jsminfo');
?>
</div>
<?PHP
}
?>	
