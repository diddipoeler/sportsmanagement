<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jltournamenttree
 */
 
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * sportsmanagementViewjltournamenttree
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewjltournamenttree extends JViewLegacy {

    /**
     * sportsmanagementViewjltournamenttree::display()
     * 
     * @param mixed $tpl
     * @return void
     */
    function display($tpl = null) {
        $option = JFactory::getApplication()->input->getCmd('option');
        // Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = JFactory::getURI();
        }
        $app = JFactory::getApplication();

        $this->app = JFactory::getApplication();
        $this->jinput = $this->app->input;
        //$this->projectid = JFactory::getApplication()->input->getInt( "p", 0 );
        sportsmanagementModelProject::setProjectID($this->jinput->getInt('p', 0), sportsmanagementModelProject::$cfg_which_database);
        $this->project = sportsmanagementModelProject::getProject();

//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project<br><pre>'.print_r($this->project,true).'</pre>'),'');

        if ($this->project->project_type == 'TOURNAMENT_MODE') {
            $model = $this->getModel();
            $bracket_request = JFactory::getApplication()->input->get();
            $this->logo = $bracket_request['tree_logo'];
            $this->color_from = $model->getColorFrom();
            $this->color_to = $model->getColorTo();
            $this->font_size = $model->getFontSize();

            if (!$this->font_size) {
                $this->font_size = '14';
            }

            if (!$this->color_from) {
                $this->color_from = '#FFFFFF';
            }
            if (!$this->color_to) {
                $this->color_to = '#0000FF';
            }


            $this->rounds = $model->getTournamentRounds();
            $this->projectname = $model->getTournamentName();
            $this->bracket_rounds = $model->getTournamentBracketRounds($this->rounds);
            $this->bracket_teams = $model->getTournamentMatches($this->rounds);
            $this->bracket_results = $model->getTournamentResults($this->rounds);
            $this->which_first_round = $model->getWhichShowFirstRound();
            $this->jl_tree_bracket_round_width = $model->getTreeBracketRoundWidth();
            $this->jl_tree_bracket_teamb_width = $model->getTreeBracketTeambWidth();
            $this->jl_tree_bracket_width = $model->getTreeBracketWidth();


// Add Script
//$document->addScript(JURI::base().'components/com_sportsmanagement/extensions/jltournamenttree/assets/js/jquery-1.7.2.min.js');
//$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/'.$this->jl_tree_jquery_version.'/jquery.min.js');
//$document->addScript('https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js');
//$document->addScript(JURI::base().'components/com_sportsmanagement/extensions/jltournamenttree/assets/js/jquery-ui-1.8.21.custom.min.js');
            $document->addScript(JURI::base() . 'components/' . $option . '/assets/js/jquery.json-2.3.min.js');
            $document->addScript(JURI::base() . 'components/' . $option . '/assets/js/jquery.bracket-3.js');

// Add customstyles
            $stylelink = '<link rel="stylesheet" href="' . JURI::base() . 'components/' . $option . '/assets/css/jquery.bracket-3.css' . '" type="text/css" />' . "\n";
            $document->addCustomTag($stylelink);
//$stylelink = '<link rel="stylesheet" href="'.JURI::base().'components/com_sportsmanagement/extensions/jltournamenttree/assets/css/jquery-ui-1.8.16.custom.css'.'" type="text/css" />' ."\n";
//$document->addCustomTag($stylelink);
            $stylelink = '<link rel="stylesheet" href="' . JURI::base() . 'components/' . $option . '/assets/css/jquery.bracket-site.css' . '" type="text/css" />' . "\n";
            $document->addCustomTag($stylelink);


            /*
              $style = 'div.jQBracket {'
              . '  font-family: "Arial";'
              . '  font-size: 14px;'
              . '  float: left;'
              . '  clear: both;'
              . '  position: relative;'
              . '  background-color: #333333;'
              . '  background: -webkit-gradient(linear, 0% 0%, 0% 100%, from('.$bracket_request['color_from'].'), to('.$bracket_request['color_to'].'));'
              . '  background: -moz-linear-gradient(-90deg, '.$bracket_request['color_from'].', '.$bracket_request['color_to'].');'
              . '  }';

              $document->addStyleDeclaration( $style );
             */
        }


        parent::display($tpl);
    }

}

?>