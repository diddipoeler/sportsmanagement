<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      jsm_bootstrap.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage plugins
 */

/*
https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/jquery.js
https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-transition.js
https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-alert.js
https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-modal.js
https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-dropdown.js
https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-scrollspy.js
https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-tab.js
https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-tooltip.js
https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-popover.js
https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-button.js
https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-collapse.js
https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-carousel.js
https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-typeahead.js
*/

/**
 * System plugin
 * 1) onBeforeRender()
 * 2) onAfterRender()
 * 3) onAfterRoute()
 * 4) onAfterDispatch()
 * These events are triggered in 'JAdministrator' class in file 'application.php' at location
 * 'Joomla_base\administrator\includes'.
 * 5) onAfterInitialise()
 * This event is triggered in 'JApplication' class in file 'application.php' at location
 * 'Joomla_base\libraries\joomla\application'.
 */


// No direct access
defined('_JEXEC') or die('Restricted access');

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');


/**
 * PlgSystemjsm_bootstrap
 * 
 * @package 
 * @author abcde
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class PlgSystemjsm_bootstrap extends JPlugin
{

    var $config;

    /**
     * PlgSystemjsm_bootstrap::__construct()
     * 
     * @param mixed $subject
     * @param mixed $params
     * @return void
     */
    public function __construct(&$subject, $params)
    {
        parent::__construct($subject, $params);

        $app = JFactory::getApplication();
		$this->loadLanguage();
        $this->config = $params;
		$this->subject = $subject;
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'params <br><pre>'.print_r($params,true).'</pre>'),'');

        /*
        //add the classes for handling
        $classpath = JPATH_ADMINISTRATOR . DS . 'components' . DS .
        'com_sportsmanagement' . DS . 'libraries' . DS . 'cbootstrap.php';
        if (file_exists($classpath)) {
        JLoader::register('CBootstrap', $classpath);
        }
        */
    }

    /**
     * PlgSystemjsm_bootstrap::onBeforeRender()
     * 
     * @return void
     */
    public function onBeforeRender()
    {
        $app = JFactory::getApplication();
    }

    /**
     * PlgSystemjsm_bootstrap::onAfterRender()
     * 
     * @return void
     */
    public function onAfterRender()
    {
        $app = JFactory::getApplication();
    }

    /**
     * PlgSystemjsm_bootstrap::onAfterRoute()
     * 
     * @return void
     */
    public function onAfterRoute()
    {
        $app = JFactory::getApplication();
	    // Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        // JInput object
        $this->jinput = $app->input;
        $this->option = $this->jinput->getCmd('option');
$dontInclude = array(
'/media/jui/js/jquery.js',
'/media/jui/js/jquery.min.js',
//'/media/jui/js/jquery-noconflict.js',
'/media/jui/js/jquery-migrate.js',
'/media/jui/js/jquery-migrate.min.js',
'/media/jui/js/bootstrap.js',
'/media/jui/js/bootstrap.min.js',
'/media/system/js/core-uncompressed.js',
'/media/system/js/tabs-state.js',
'/media/system/js/core.js',
//'/media/system/js/mootools-core.js',
//'/media/system/js/mootools-core-uncompressed.js',
);

if ( $this->option == 'com_sportsmanagement' )
{
foreach($document->_scripts as $key => $script){
    if(in_array($key, $dontInclude)){
        unset($document->_scripts[$key]);
    }
}
JFactory::getDocument()->addScript('http://ajax.googleapis.com/ajax/libs/jquery/2.2.1/jquery.min.js');
}		    
    }

    /**
     * PlgSystemjsm_bootstrap::onAfterDispatch()
     * 
     * @return void
     */
    public function onAfterDispatch()
    {
        $app = JFactory::getApplication();

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'isEnabled <br><pre>'.print_r(JComponentHelper::isEnabled('com_k2', true),true).'</pre>'),'');
    
	    
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'isEnabled <br><pre>'.print_r(JComponentHelper::isEnabled('com_k2', true),true).'</pre>'),'');

        // Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        $load_bootstrap = $this->params->def('load_bootstrap', 1);
        $load_bootstrap_css = $this->params->def('load_bootstrap_css', 1);
        $load_bootstrap_version = $this->params->def('load_bootstrap_version', '3.3.6');
        $load_k2css = $this->params->def('load_k2css', 1);

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            // Joomla! 3.0 code here
            if ($load_bootstrap) {
                if (!$app->isAdmin()) {
                    //CBootstrap::load();
                    JFactory::getDocument()->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/' .
                        $load_bootstrap_version . '/js/bootstrap.min.js');
                }
            }
            
            if ($this->params->def('load_bootstrap_carousel', 1)) {
                if (!$app->isAdmin()) {
                    //CBootstrap::load();
                    JFactory::getDocument()->addScript('https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-carousel.js');
                }
            }
            
            if ($this->params->def('load_bootstrap_modal', 1)) {
                if (!$app->isAdmin()) {
                    //CBootstrap::load();
                    JFactory::getDocument()->addScript('https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-modal.js');
                }
            }
            
	if ($this->params->def('load_bootstrap_tab', 1)) {
                if (!$app->isAdmin()) {
                    //CBootstrap::load();
                    JFactory::getDocument()->addScript('https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-tab.js');
                }
            }
		
            if ($load_bootstrap_css) {
                if (!$app->isAdmin()) {
                    //CBootstrap::load();
                    JFactory::getDocument()->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/' .
                        $load_bootstrap_version . '/css/bootstrap.min.css');
                    JFactory::getDocument()->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/' .
                        $load_bootstrap_version . '/css/bootstrap-theme.min.css');
                }
            }
            
            if ($load_k2css) {
                /**
                 * wenn man die k2 komponente installiert hat, kann es zu problemen im frontend kommen.
                 * dazu gibt es diesen hilfreichen link: 
                 * http://www.optimumtheme.com/support/forum/k2-image-and-link-edit,-add-item-problem-solution.html
                 */

                // Check for component
                $db = JFactory::getDbo();
                $db->setQuery("SELECT enabled FROM #__extensions WHERE name = 'com_k2'");
                $is_enabled = $db->loadResult();
                //if (JComponentHelper::getComponent('com_k2', true)->enabled) {
                if ($is_enabled) {
                    if (!$app->isAdmin()) {
                        $css = JUri::base() . 'plugins' . DS . $this->config['type'] . DS . $this->
                            config['name'] . DS . 'css/customk2.css';
                        $document->addStyleSheet($css);
                    }
                }
            }

        } elseif (version_compare(JVERSION, '2.5.0', 'ge')) {
            // Joomla! 2.5 code here


            if ($load_bootstrap) {
                if (!$app->isAdmin()) {
                    //CBootstrap::load();
                    JFactory::getDocument()->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/' .
                        $load_bootstrap_version . '/js/bootstrap.min.js');
                }
            }
            
            if ($load_bootstrap_css) {
                if (!$app->isAdmin()) {
                    //CBootstrap::load();
                    JFactory::getDocument()->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/' .
                        $load_bootstrap_version . '/css/bootstrap.min.css');
                    JFactory::getDocument()->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/' .
                        $load_bootstrap_version . '/css/bootstrap-theme.min.css');
                }
            }
            
            if ($load_k2css) {
                /**
                 * wenn man die k2 komponente installiert hat, kann es zu problemen im frontend kommen.
                 * dazu gibt es diesen hilfreichen link: 
                 * http://www.optimumtheme.com/support/forum/k2-image-and-link-edit,-add-item-problem-solution.html
                 */

                // Check for component
                $db = JFactory::getDbo();
                $db->setQuery("SELECT enabled FROM #__extensions WHERE name = 'com_k2'");
                $is_enabled = $db->loadResult();
                //if (JComponentHelper::getComponent('com_k2', true)->enabled) {
                if ($is_enabled) {
                    if (!$app->isAdmin()) {
                        $css = JUri::base() . 'plugins' . DS . $this->config['type'] . DS . $this->
                            config['name'] . DS . 'css/customk2.css';
                        $document->addStyleSheet($css);
                    }
                }
            }
        }

    }

    /**
     * PlgSystemjsm_bootstrap::onAfterInitialise()
     * 
     * @return void
     */
    public function onAfterInitialise()
    {
        $app = JFactory::getApplication();
    }

}

?>
