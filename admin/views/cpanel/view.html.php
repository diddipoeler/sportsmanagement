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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Layout\FileLayout;


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
        $document = Factory::getDocument();

        $project_id = $this->app->getUserState("$this->option.pid", '0');
        $model = $this->getModel();
        $my_text = '';

        $databasetool = JModelLegacy::getInstance("databasetool", "sportsmanagementModel");
        DEFINE('COM_SPORTSMANAGEMENT_MODEL_ERRORLOG', $databasetool);

        // für den import die jl tabellen lesen
        $jl_table_import = $databasetool->getJoomleagueTables();

        $params = ComponentHelper::getParams($this->option);
        $sporttypes = $params->get('cfg_sport_types');
        $sm_quotes = $params->get('cfg_quotes');
        $country = $params->get('cfg_country_associations');
        $install_agegroup = ComponentHelper::getParams($this->option)->get('install_agegroup', 0);
        $cfg_which_database = $params->get('cfg_which_database');
        if ($cfg_which_database) {
            $sporttypes = '';
            $sm_quotes = '';
            $country = '';
            $install_agegroup = '';
        }

        if ($model->getInstalledPlugin('jqueryeasy')) {
            $this->jquery = '0';
            if (!PluginHelper::isEnabled('system', 'jqueryeasy')) {
            }
        } else {
            $this->jquery = '1';
        }

        if ($model->getInstalledPlugin('plugin_googlemap3')) {
            $this->googlemap = '0';
            if (!PluginHelper::isEnabled('system', 'plugin_googlemap3')) {
            }
        } else {
            $this->googlemap = '0';
        }

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
                    $my_text .= '<span style="color:' . $model->existingInDbColor . '"><strong>';
                    $my_text .= Text::_('Installierte Sportarten') . '</strong></span><br />';
                    $my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_SPORT_TYPE_SUCCESS', strtoupper($type)) . '<br />';

                    $model->_success_text['Sportarten:'] = $my_text;

                    // es können aber auch neue positionen oder ereignisse dazu kommen
                    $insert_sport_type = $databasetool->insertSportType($type);

                    if ($country) {
                        foreach ($country as $keyc => $typec) {
                            $insert_agegroup = $databasetool->insertAgegroup($typec, $insert_sport_type);
                            if (!isset($model->_success_text['Altersgruppen:'])) {
                                $model->_success_text['Altersgruppen:'] = '';
                            }
                            $model->_success_text['Altersgruppen:'] = $model->_success_text['Altersgruppen:'] . $insert_agegroup;
                        }
                    }
                } else {
                    $my_text .= '<span style="color:' . $model->storeFailedColor . '"><strong>';
                    $my_text .= Text::_('Fehlende Sportarten') . '</strong></span><br />';
                    $my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_SPORT_TYPE_ERROR', strtoupper($type)) . '<br />';

                    $model->_success_text['Sportarten:'] = $my_text;

                    // es können aber auch neue positionen oder ereignisse dazu kommen
                    $insert_sport_type = $databasetool->insertSportType($type);
                    if (isset($model->_success_text['Sportart (' . $type . ')  :'])) {
                        $model->_success_text['Sportart (' . $type . ')  :'] .= $databasetool->my_text;
                    }

                    /**
                     * nur wenn in den optionen ja eingestellt ist, werden die altersgruppen installiert
                     */
                    if ($install_agegroup) {
                        if ($country) {
                            foreach ($country as $keyc => $typec) {
                                $insert_agegroup = $databasetool->insertAgegroup($typec, $insert_sport_type);
                                if (isset($model->_success_text['Altersgruppen:'])) {
                                    $model->_success_text['Altersgruppen:'] .= $insert_agegroup;
                                }
                            }
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
            $my_text = '<span style="color:' . $model->existingInDbColor . '"><strong>';
            $my_text .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_COUNTRIES_SUCCESS') . '</strong></span><br />';
            $model->_success_text['Länder:'] = $my_text;
        } else {
            $my_text = '<span style="color:' . $model->storeFailedColor . '"><strong>';
            $my_text .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_COUNTRIES_ERROR') . '</strong></span><br />';
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
        $this->importData = $model->_success_text;
        $this->importData2 = $databasetool->_success_text;

        if (!$project_id) {

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
        // Get a refrence of the page instance in joomla
        $document = Factory::getDocument();
        $task = $this->jinput->getCmd('task');

        $document->addScript(Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/js/sm_functions.js');

        if ($this->app->isAdmin()) {
            if ($task == '' && $this->option == 'com_sportsmanagement') {

            }
        } else {

        }

        $canDo = sportsmanagementHelper::getActions();
        JToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_MANAGER'), 'helloworld');

        if ($canDo->get('core.admin')) {
            if ($this->jquery) {
                $this->app->setUserState("$this->option.install", 'jqueryeasy');
                sportsmanagementHelper::ToolbarButton('default', 'upload', Text::_('COM_SPORTSMANAGEMENT_INSTALL_JQUERY'), 'githubinstall', 1);
            }

            if ($this->googlemap) {
                $this->app->setUserState("$this->option.install", 'plugin_googlemap3');
                sportsmanagementHelper::ToolbarButton('default', 'upload', Text::_('COM_SPORTSMANAGEMENT_INSTALL_GOOGLEMAP'), 'githubinstall', 1);
            }

            $bar = JToolbar::getInstance('toolbar');
            $bar->appendButton('Link', 'upload', Text::_('COM_SPORTSMANAGEMENT_GITHUB_UPDATE'), 'index.php?option=com_sportsmanagement&&view=githubinstall');

            $title = Text::_('JTOOLBAR_BATCH');
			// Instantiate a new FileLayout instance and render the batch button
			$layout = new FileLayout('joomla.toolbar.batch');
			$dhtml = $layout->render(array('title' => $title));
			Toolbar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'batch');
            
            ToolbarHelper::help('JHELP_COMPONENTS_SPORTSMANAGEMENT_CPANEL');
            
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
        $lang = Factory::getLanguage();
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
        <?php echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/icons/' . $image, null, $attribs); ?>
                    <span><?php echo $text; ?></span></a>
            </div>
        </div>
        <?php
    }

}
