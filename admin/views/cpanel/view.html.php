<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage cpanel
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
//jimport('joomla.application.component.view');
// zur unterscheidung von joomla 2.5 und 3
//JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.view', JPATH_ADMINISTRATOR);

/**
 * sportsmanagementViewcpanel
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementViewcpanel extends sportsmanagementView {

    /**
     *  view display method
     * @return void
     */
    public function init() {
        $document = JFactory::getDocument();
        //$app = JFactory::getApplication();
        //$jinput = $app->input;
        //$option = $jinput->getCmd('option');

        $project_id = $this->app->getUserState("$this->option.pid", '0');
        $model = $this->getModel();
        $my_text = '';

        //$databasetool = JModelLegacy::getInstance("databasetool", "sportsmanagementModel");
        $databasetool = JModelLegacy::getInstance("databasetool", "sportsmanagementModel");
        DEFINE('COM_SPORTSMANAGEMENT_MODEL_ERRORLOG', $databasetool);

        //sportsmanagementHelper::isJoomlaVersion('2.5');
        //$this->app->enqueueMessage(sprintf(JText::_('COM_SPORTSMANAGEMENT_JOOMLA_VERSION'), COM_SPORTSMANAGEMENT_JOOMLAVERSION),'');
        //$this->app->enqueueMessage($this->layout,'Notice');
        // für den import die jl tabellen lesen
        $jl_table_import = $databasetool->getJoomleagueTables();

        $params = JComponentHelper::getParams($this->option);
        $sporttypes = $params->get('cfg_sport_types');
        $sm_quotes = $params->get('cfg_quotes');
        $country = $params->get('cfg_country_associations');
        $install_agegroup = JComponentHelper::getParams($this->option)->get('install_agegroup', 0);
        $cfg_which_database = $params->get('cfg_which_database');
        if ($cfg_which_database) {
            $sporttypes = '';
            $sm_quotes = '';
            $country = '';
            $install_agegroup = '';
        }
        // JPluginHelper::isEnabled( 'system', 'jqueryeasy' )
        if ($model->getInstalledPlugin('jqueryeasy')) {
            //$app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_JQUERY_AVAILABLE'),'Notice');
            $this->jquery = '0';
            if (!JPluginHelper::isEnabled('system', 'jqueryeasy')) {
                //$this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_JQUERY_NOT_ENABLED'),'Error');
            }
        } else {
            //$this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_JQUERY_NOT_AVAILABLE'),'Error');
            $this->jquery = '1';
        }

        if ($model->getInstalledPlugin('plugin_googlemap3')) {
            //$app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GOOGLEMAP_AVAILABLE'),'Notice');
            $this->googlemap = '0';
            if (!JPluginHelper::isEnabled('system', 'plugin_googlemap3')) {
                //$this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GOOGLEMAP_NOT_ENABLED'),'Error');
            }
        } else {
            //$this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GOOGLEMAP_NOT_AVAILABLE'),'Error');
            //$this->googlemap = '1';
            $this->googlemap = '0';
        }


        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($country,true).'</pre>'),'Notice');


        if ($sm_quotes) {
            // zitate
            $result = $databasetool->checkQuotes($sm_quotes);
            $model->_success_text['Zitate:'] = $result;
        }

        if ($sporttypes) {
            foreach ($sporttypes as $key => $type) {
                $checksporttype = $model->checksporttype($type);

                $checksporttype_strukt = $databasetool->checkSportTypeStructur($type);

                if ($checksporttype) {
                    //$app->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_SPORT_TYPE_SUCCESS',strtoupper($type)),'');    
                    $my_text .= '<span style="color:' . $model->existingInDbColor . '"><strong>';
                    $my_text .= JText::_('Installierte Sportarten') . '</strong></span><br />';
                    $my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_SPORT_TYPE_SUCCESS', strtoupper($type)) . '<br />';

                    $model->_success_text['Sportarten:'] = $my_text;

                    // es können aber auch neue positionen oder ereignisse dazu kommen
                    $insert_sport_type = $databasetool->insertSportType($type);

                    if ($country) {
                        foreach ($country as $keyc => $typec) {
                            $insert_agegroup = $databasetool->insertAgegroup($typec, $insert_sport_type);
                            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($insert_agegroup,true).'</pre>'),'');
                            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($model->_success_text,true).'</pre>'),'');
                            if (!isset($model->_success_text['Altersgruppen:'])) {
                                $model->_success_text['Altersgruppen:'] = '';
                            }
                            //$model->_success_text['Altersgruppen:'] .= $insert_agegroup;
                            $model->_success_text['Altersgruppen:'] = $model->_success_text['Altersgruppen:'] . $insert_agegroup;
                        }
                    }
                } else {
                    //$app->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_SPORT_TYPE_ERROR',strtoupper($type)),'Error');
                    $my_text .= '<span style="color:' . $model->storeFailedColor . '"><strong>';
                    $my_text .= JText::_('Fehlende Sportarten') . '</strong></span><br />';
                    $my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_SPORT_TYPE_ERROR', strtoupper($type)) . '<br />';

                    $model->_success_text['Sportarten:'] = $my_text;

                    // es können aber auch neue positionen oder ereignisse dazu kommen
                    $insert_sport_type = $databasetool->insertSportType($type);
                    if (isset($model->_success_text['Sportart (' . $type . ')  :'])) {
                        $model->_success_text['Sportart (' . $type . ')  :'] .= $databasetool->my_text;
                    }

                    /**
                     * nur wenn in den optionen ja eingestellt ist, werden die altersgruppen installiert
                     */
                    //$install_agegroup = JComponentHelper::getParams($this->option)->get('install_agegroup',0);
                    if ($install_agegroup) {
                        if ($country) {
                            foreach ($country as $keyc => $typec) {
                                $insert_agegroup = $databasetool->insertAgegroup($typec, $insert_sport_type);
                                //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($insert_agegroup,true).'</pre>'),'');
                                if (isset($model->_success_text['Altersgruppen:'])) {
                                    $model->_success_text['Altersgruppen:'] .= $insert_agegroup;
                                }
                            }
                            //$databasetool->_success_text['Altersgruppen:'] .= $databasetool->_success_text; 
                            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($databasetool->my_text,true).'</pre>'),'');
                        }
                    }
                }
            }
        }
        // Get data from the model
        $items = $this->get('Items');
        $pagination = $this->get('Pagination');

        if (!$cfg_which_database) {
            $checkassociations = $databasetool->checkAssociations();
        }

        $checkcountry = $model->checkcountry();
        if ($checkcountry) {
            //$app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_COUNTRIES_SUCCESS'),''); 
            $my_text = '<span style="color:' . $model->existingInDbColor . '"><strong>';
            $my_text .= JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_COUNTRIES_SUCCESS') . '</strong></span><br />';
            //$my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_COUNTRIES_SUCCESS',strtoupper($type)).'<br />';

            $model->_success_text['Länder:'] = $my_text;
        } else {
            //$app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_COUNTRIES_ERROR'),'Error');
            $my_text = '<span style="color:' . $model->storeFailedColor . '"><strong>';
            $my_text .= JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_COUNTRIES_ERROR') . '</strong></span><br />';
            //$my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_COUNTRIES_SUCCESS',strtoupper($type)).'<br />';

            $model->_success_text['Länder:'] = $my_text;

            $insert_countries = $databasetool->insertCountries();
            $model->_success_text['Länder:'] .= $insert_countries;
        }

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            
        } else {
            jimport('joomla.html.pane');
            $pane = JPane::getInstance('sliders');
            $this->pane = $pane;
        }

        $this->sporttypes = $sporttypes;
        $this->version = $model->getVersion();

        // diddipoeler erst mal abgeschaltet
        //$this->assign( 'githubrequest', $model->getGithubRequests() );
        $this->importData = $model->_success_text;
        $this->importData2 = $databasetool->_success_text;

        if (!$project_id) {
//            //override active menu class to remove active class from other submenu
//            $menuCssOverrideJs="jQuery(document).ready(function(){
//            jQuery('ul>li> a[href=\"index.php?option=com_sportsmanagement&view=project&layout=panel&id=\"]:last').removeClass('active');
//            });";
//            $document->addScriptDeclaration($menuCssOverrideJs);
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));
            return false;
        }
        // Assign data to the view
        $this->items = $items;
        $this->pagination = $pagination;
        $this->params = $params;
    }

    /**
     * Setting the toolbar
     */
    protected function addToolBar() {
        //$app = JFactory::getApplication(); 
        // Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        //$jinput = $app->input;
        //$option = $jinput->getCmd('option');
        $task = $this->jinput->getCmd('task');

//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);

        $document->addScript(JURI::root(true) . '/administrator/components/com_sportsmanagement/assets/js/sm_functions.js');

        if ($this->app->isAdmin()) {
            if ($task == '' && $this->option == 'com_sportsmanagement') {

            }
        } else {

        }

        $canDo = sportsmanagementHelper::getActions();
        JToolbarHelper::title(JText::_('COM_SPORTSMANAGEMENT_MANAGER'), 'helloworld');

        if ($canDo->get('core.admin')) {
            if ($this->jquery) {
                $this->app->setUserState("$this->option.install", 'jqueryeasy');
                sportsmanagementHelper::ToolbarButton('default', 'upload', JText::_('COM_SPORTSMANAGEMENT_INSTALL_JQUERY'), 'githubinstall', 1);
                //JToolbarHelper::custom('cpanel.jqueryinstall','upload','upload',JText::_('COM_SPORTSMANAGEMENT_INSTALL_JQUERY'),false);
            }

            if ($this->googlemap) {
                $this->app->setUserState("$this->option.install", 'plugin_googlemap3');
                sportsmanagementHelper::ToolbarButton('default', 'upload', JText::_('COM_SPORTSMANAGEMENT_INSTALL_GOOGLEMAP'), 'githubinstall', 1);
                //JToolbarHelper::custom('cpanel.jqueryinstall','upload','upload',JText::_('COM_SPORTSMANAGEMENT_INSTALL_JQUERY'),false);
            }

            $bar = JToolbar::getInstance('toolbar');
            $bar->appendButton('Link', 'upload', JText::_('COM_SPORTSMANAGEMENT_GITHUB_UPDATE'), 'index.php?option=com_sportsmanagement&&view=githubinstall');
            //}

            if (version_compare(JVERSION, '3.0.0', 'ge')) {
                JHtmlSidebar::setAction('index.php?option=com_sportsmanagement');
            }
            parent::addToolbar();
        }
    }

    /**
     * sportsmanagementViewcpanel::addIcon()
     * 
     * @param mixed $image
     * @param mixed $url
     * @param mixed $text
     * @param bool $newWindow
     * @param integer $width
     * @param integer $height
     * @param string $maxwidth
     * @return void
     */
    public function addIcon($image, $url, $text, $newWindow = false, $width = 0, $height = 0, $maxwidth = '100%') {
        $lang = JFactory::getLanguage();
        $newWindow = ( $newWindow ) ? ' target="_blank"' : '';
        $attribs = array();
        if ($width) {
            $attribs['width'] = $width;
            $attribs['height'] = $height;
        }
        ?>
        <div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
            <div class="icon">
                <a href="<?php echo $url; ?>"<?php echo $newWindow; ?>>
        <?php echo JHtml::_('image', 'administrator/components/com_sportsmanagement/assets/icons/' . $image, null, $attribs); ?>
                    <span><?php echo $text; ?></span></a>
            </div>
        </div>
        <?php
    }

}
