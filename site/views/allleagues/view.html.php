<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage allleagues
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

if (!defined('JSM_PATH')) {
    DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

// pr�ft vor Benutzung ob die gew�nschte Klasse definiert ist
if (!class_exists('sportsmanagementHelperHtml')) {
    //add the classes for handling
    $classpath = JPATH_SITE .DIRECTORY_SEPARATOR. JSM_PATH .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'html.php';
    JLoader::register('sportsmanagementHelperHtml', $classpath);
}

/**
 * sportsmanagementViewallleagues
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewallleagues extends sportsmanagementView
{

    protected $state = null;
    protected $item = null;
    protected $items = null;
    protected $pagination = null;

    /**
     * sportsmanagementViewallleagues::init()
     * 
     * @return void
     */
    function init() 
    {
        $inputappend = '';
        $this->tableclass = $this->jinput->getVar('table_class', 'table', 'request', 'string');
        $this->use_jquery_modal = $this->jinput->getVar('use_jquery_modal', '2', 'request', 'string');

        $starttime = microtime();
        $this->state = $this->get('State');
        $this->items = $this->get('Items');

        $this->pagination = $this->get('Pagination');

        //build the html options for nation
        $nation[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
        if ($res = JSMCountries::getCountryOptions()) {
            $nation = array_merge($nation, $res);
        }

        $lists['nation'] = $nation;
        $lists['nation2'] = JHtmlSelect::genericlist($nation, 'filter_search_nation', $inputappend . 'class="inputbox" style="width:140px; " onchange="this.form.submit();"', 'value', 'text', $this->state->get('filter.search_nation'));

        // Set page title
        $this->document->setTitle(Text::_('COM_SPORTSMANAGEMENT_ALLLEAGUES_PAGE_TITLE'));
        $form = new stdClass();
        $form->limitField = $this->pagination->getLimitBox();
        $this->filter = $this->state->get('filter.search');
        $this->form = $form;

        $this->sortDirection = $this->state->get('filter_order_Dir');
        $this->sortColumn = $this->state->get('filter_order');
        $this->lists = $lists;
    }

}

?>
