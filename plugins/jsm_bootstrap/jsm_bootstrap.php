<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage plugins
 * @file       jsm_bootstrap.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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

https://datatables.net/download/release
https://datatables.net/
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

defined('_JEXEC') or die();
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');


/**
 * PlgSystemjsm_bootstrap
 *
 * @package
 * @author    abcde
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class PlgSystemjsm_bootstrap extends CMSPlugin
{

    var $config;

    /**
     * PlgSystemjsm_bootstrap::__construct()
     *
     * @param  mixed $subject
     * @param  mixed $params
     * @return void
     */
    public function __construct(&$subject, $params)
    {
        parent::__construct($subject, $params);

        $app = Factory::getApplication();
        $this->loadLanguage();
        $this->config = $params;
        $this->subject = $subject;
    }

    /**
     * PlgSystemjsm_bootstrap::onBeforeRender()
     *
     * @return void
     */
    public function onBeforeRender()
    {
        $app = Factory::getApplication();
    }

    /**
     * PlgSystemjsm_bootstrap::onAfterRender()
     *
     * @return void
     */
    public function onAfterRender()
    {
        $app = Factory::getApplication();
    }

    /**
     * PlgSystemjsm_bootstrap::onAfterRoute()
     *
     * @return void
     */
    public function onAfterRoute()
    {
        $app = Factory::getApplication();
        // Get a refrence of the page instance in joomla
        $document = Factory::getDocument();
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

        if ($this->option == 'com_sportsmanagement' ) {
            foreach($document->_scripts as $key => $script){
                if(in_array($key, $dontInclude)) {
                    unset($document->_scripts[$key]);
                }
            }
                Factory::getDocument()->addScript('http://ajax.googleapis.com/ajax/libs/jquery/2.2.1/jquery.min.js');
        }          
    }

    /**
     * PlgSystemjsm_bootstrap::onAfterDispatch()
     *
     * @return void
     */
    public function onAfterDispatch()
    {
        $app = Factory::getApplication();
        // Get a refrence of the page instance in joomla
        $document = Factory::getDocument();
        $load_bootstrap = $this->params->def('load_bootstrap', 1);
        $load_bootstrap_css = $this->params->def('load_bootstrap_css', 1);
        $load_bootstrap_version = $this->params->def('load_bootstrap_version', '3.3.6');
        $load_k2css = $this->params->def('load_k2css', 1);

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            // Joomla! 3.0 code here
            if ($load_bootstrap) {
                if (!$app->isClient('administrator')) {
                    //CBootstrap::load();
                    Factory::getDocument()->addScript(
                        'https://maxcdn.bootstrapcdn.com/bootstrap/' .
                        $load_bootstrap_version . '/js/bootstrap.min.js'
                    );
                }
            }
                        
            if ($this->params->def('load_datatables', 0)) {
                if (!$app->isClient('administrator')) {
					switch ($this->params->def('load_for_which_bootstrap', 0))
			{
				case 0:
				Factory::getDocument()->addStyleSheet('https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css');
				Factory::getDocument()->addScript('https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js');
				break;
				case 3:
				Factory::getDocument()->addScript('https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js');
                    Factory::getDocument()->addScript('https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap.min.js');
                    Factory::getDocument()->addStyleSheet('https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap.min.css');
				break;
				case 4:
				Factory::getDocument()->addStyleSheet('https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css');
				Factory::getDocument()->addScript('https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js');
				Factory::getDocument()->addScript('https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js');
				break;
			}

                    
                }
            }
		
		if ($this->params->def('load_responsive', 0)) {
                if (!$app->isClient('administrator')) {
					switch ($this->params->def('load_for_which_bootstrap', 0))
			{
				case 0:
				Factory::getDocument()->addStyleSheet('https://cdn.datatables.net/responsive/2.2.5/css/responsive.dataTables.min.css');
				Factory::getDocument()->addScript('https://cdn.datatables.net/responsive/2.2.5/js/dataTables.responsive.min.js');
				break;
				case 3:
				Factory::getDocument()->addScript('https://cdn.datatables.net/responsive/2.2.5/js/dataTables.responsive.min.js');
                    Factory::getDocument()->addScript('https://cdn.datatables.net/responsive/2.2.5/js/responsive.bootstrap.min.js');
                    Factory::getDocument()->addStyleSheet('https://cdn.datatables.net/responsive/2.2.5/css/responsive.bootstrap.min.css');
				break;
				case 4:
				Factory::getDocument()->addStyleSheet('https://cdn.datatables.net/responsive/2.2.5/css/responsive.bootstrap4.min.css');
				Factory::getDocument()->addScript('https://cdn.datatables.net/responsive/2.2.5/js/dataTables.responsive.min.js');
				Factory::getDocument()->addScript('https://cdn.datatables.net/responsive/2.2.5/js/responsive.bootstrap4.min.js');
				break;
			}

                    
                }
            }
            
            if ($this->params->def('load_fixedcolumns', 0)) {
                if (!$app->isClient('administrator')) {
					switch ($this->params->def('load_for_which_bootstrap', 0))
			{
				case 0:
				Factory::getDocument()->addStyleSheet('https://cdn.datatables.net/fixedcolumns/3.3.1/css/fixedColumns.dataTables.min.css');
				Factory::getDocument()->addScript('https://cdn.datatables.net/fixedcolumns/3.3.1/js/dataTables.fixedColumns.min.js');
				break;
				case 3:
				Factory::getDocument()->addScript('https://cdn.datatables.net/fixedcolumns/3.3.1/js/dataTables.fixedColumns.min.js');
                Factory::getDocument()->addStyleSheet('https://cdn.datatables.net/fixedcolumns/3.3.1/css/fixedColumns.bootstrap.min.css');
				break;
				case 4:
				Factory::getDocument()->addStyleSheet('https://cdn.datatables.net/fixedcolumns/3.3.1/css/fixedColumns.bootstrap4.min.css');
				Factory::getDocument()->addScript('https://cdn.datatables.net/fixedcolumns/3.3.1/js/dataTables.fixedColumns.min.js');
				break;
			}

                    
                }
            }
            
            if ($this->params->def('load_fixedheader', 0)) {
                if (!$app->isClient('administrator')) {
					switch ($this->params->def('load_for_which_bootstrap', 0))
			{
				case 0:
				Factory::getDocument()->addStyleSheet('https://cdn.datatables.net/fixedheader/3.1.7/css/fixedHeader.dataTables.min.css');
				Factory::getDocument()->addScript('https://cdn.datatables.net/fixedheader/3.1.7/js/dataTables.fixedHeader.min.js');
				break;
				case 3:
				Factory::getDocument()->addScript('https://cdn.datatables.net/fixedheader/3.1.7/js/dataTables.fixedHeader.min.js');
                Factory::getDocument()->addStyleSheet('https://cdn.datatables.net/fixedheader/3.1.7/css/fixedHeader.bootstrap.min.css');
				break;
				case 4:
				Factory::getDocument()->addStyleSheet('https://cdn.datatables.net/fixedheader/3.1.7/css/fixedHeader.bootstrap4.min.css');
				Factory::getDocument()->addScript('https://cdn.datatables.net/fixedheader/3.1.7/js/dataTables.fixedHeader.min.js');
				break;
			}

                    
                }
            }


if ($this->params->def('load_buttons', 0)) {
                if (!$app->isClient('administrator')) {
		switch ($this->params->def('load_for_which_bootstrap', 0))
		{
		case 0:
		Factory::getDocument()->addStyleSheet('https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css');
		Factory::getDocument()->addScript('https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js');
		break;
		case 3:
		Factory::getDocument()->addScript('https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js');
		Factory::getDocument()->addScript('https://cdn.datatables.net/buttons/1.6.2/js/buttons.bootstrap.min.js');
                Factory::getDocument()->addStyleSheet('https://cdn.datatables.net/buttons/1.6.2/css/buttons.bootstrap.min.css');
		break;
		case 4:
		Factory::getDocument()->addStyleSheet('https://cdn.datatables.net/buttons/1.6.2/css/buttons.bootstrap4.min.css');
		Factory::getDocument()->addScript('https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js');
		Factory::getDocument()->addScript('https://cdn.datatables.net/buttons/1.6.2/js/buttons.bootstrap4.min.js');
		break;
		}
			Factory::getDocument()->addScript('https://cdn.datatables.net/buttons/1.6.2/js/buttons.colVis.min.js');
			Factory::getDocument()->addScript('https://cdn.datatables.net/buttons/1.6.2/js/buttons.flash.min.js');
			Factory::getDocument()->addScript('https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js');
			Factory::getDocument()->addScript('https://cdn.datatables.net/buttons/1.6.2/js/buttons.print.min.js');
			
			Factory::getDocument()->addScript('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js');
			Factory::getDocument()->addScript('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.67/pdfmake.min.js');
			Factory::getDocument()->addScript('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.67/vfs_fonts.js');
			
			

                    
                }
            }





          
            if ($this->params->def('load_bootstrap_carousel', 1)) {
                if (!$app->isClient('administrator')) {
                    //CBootstrap::load();
                    Factory::getDocument()->addScript('https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-carousel.js');
                }
            }
          
            if ($this->params->def('load_bootstrap_modal', 1)) {
                if (!$app->isClient('administrator')) {
                    //CBootstrap::load();
                    Factory::getDocument()->addScript('https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-modal.js');
                }
            }
          
            if ($this->params->def('load_bootstrap_tab', 1)) {
                if (!$app->isClient('administrator')) {
                    //CBootstrap::load();
                    Factory::getDocument()->addScript('https://netdna.bootstrapcdn.com/twitter-bootstrap/2.0.4/js/bootstrap-tab.js');
                }
            }
      
            if ($load_bootstrap_css) {
                if (!$app->isClient('administrator')) {
                    //CBootstrap::load();
                    Factory::getDocument()->addStyleSheet(
                        'https://maxcdn.bootstrapcdn.com/bootstrap/' .
                        $load_bootstrap_version . '/css/bootstrap.min.css'
                    );
                    Factory::getDocument()->addStyleSheet(
                        'https://maxcdn.bootstrapcdn.com/bootstrap/' .
                        $load_bootstrap_version . '/css/bootstrap-theme.min.css'
                    );
                }
            }
          
            if ($load_k2css) {
                /**
                 * wenn man die k2 komponente installiert hat, kann es zu problemen im frontend kommen.
                 * dazu gibt es diesen hilfreichen link:
                 * http://www.optimumtheme.com/support/forum/k2-image-and-link-edit,-add-item-problem-solution.html
                 */

                // Check for component
                $db = Factory::getDbo();
                $db->setQuery("SELECT enabled FROM #__extensions WHERE name = 'com_k2'");
                $is_enabled = $db->loadResult();
                //if (JComponentHelper::getComponent('com_k2', true)->enabled) {
                if ($is_enabled) {
                    if (!$app->isClient('administrator')) {
                        $css = Uri::base() . 'plugins' .DIRECTORY_SEPARATOR. $this->config['type'] .DIRECTORY_SEPARATOR. $this
                            ->config['name'] .DIRECTORY_SEPARATOR. 'css/customk2.css';
                        $document->addStyleSheet($css);
                    }
                }
            }

        } elseif (version_compare(JVERSION, '2.5.0', 'ge')) {
            // Joomla! 2.5 code here


            if ($load_bootstrap) {
                if (!$app->isClient('administrator')) {
                    //CBootstrap::load();
                    Factory::getDocument()->addScript(
                        'https://maxcdn.bootstrapcdn.com/bootstrap/' .
                        $load_bootstrap_version . '/js/bootstrap.min.js'
                    );
                }
            }
          
            if ($load_bootstrap_css) {
                if (!$app->isClient('administrator')) {
                    //CBootstrap::load();
                    Factory::getDocument()->addStyleSheet(
                        'https://maxcdn.bootstrapcdn.com/bootstrap/' .
                        $load_bootstrap_version . '/css/bootstrap.min.css'
                    );
                    Factory::getDocument()->addStyleSheet(
                        'https://maxcdn.bootstrapcdn.com/bootstrap/' .
                        $load_bootstrap_version . '/css/bootstrap-theme.min.css'
                    );
                }
            }
          
            if ($load_k2css) {
                /**
                 * wenn man die k2 komponente installiert hat, kann es zu problemen im frontend kommen.
                 * dazu gibt es diesen hilfreichen link:
                 * http://www.optimumtheme.com/support/forum/k2-image-and-link-edit,-add-item-problem-solution.html
                 */

                // Check for component
                $db = Factory::getDbo();
                $db->setQuery("SELECT enabled FROM #__extensions WHERE name = 'com_k2'");
                $is_enabled = $db->loadResult();
                //if (JComponentHelper::getComponent('com_k2', true)->enabled) {
                if ($is_enabled) {
                    if (!$app->isClient('administrator')) {
                        $css = Uri::base() . 'plugins' .DIRECTORY_SEPARATOR. $this->config['type'] .DIRECTORY_SEPARATOR. $this
                            ->config['name'] .DIRECTORY_SEPARATOR. 'css/customk2.css';
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
        $app = Factory::getApplication();
    }

}

?>
