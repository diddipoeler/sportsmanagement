<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       smimageimports.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * sportsmanagementModelsmimageimports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelsmimageimports extends ListModel
{

    var $_identifier = "pictures";

    /**
     * sportsmanagementModelsmimageimports::__construct()
     * 
     * @param  mixed $config
     * @return void
     */
    public function __construct($config = array()) 
    {
        $config['filter_fields'] = array(
            'name',
            'file',
            'folder',
            'directory'
        );
        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since 1.6
     */
    protected function populateState($ordering = null, $direction = null) 
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        // Initialise variables.
        $app = Factory::getApplication('administrator');

        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);

        $image_folder = $this->getUserStateFromRequest($this->context . '.filter.image_folder', 'filter_image_folder', '');
        $this->setState('filter.image_folder', $image_folder);

        // List state information.
        parent::populateState('obj.name', 'asc');
    }

    /**
     * sportsmanagementModelsmimageimports::getListQuery()
     * 
     * @return
     */
    protected function getListQuery() 
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');

        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
        // Select some fields
        $query->select('obj.*');
        // From the hello table
        $query->from('#__sportsmanagement_pictures as obj');
        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');

        if (is_numeric($this->getState('filter.state'))) {
            $query->where('obj.published = ' . $this->getState('filter.state'));
        }
        if ($this->getState('filter.search')) {
            $query->where('LOWER(obj.name) LIKE ' . $this->_db->Quote('%' . $this->getState('filter.search') . '%'));
        }
        if ($this->getState('filter.image_folder')) {
            $query->where('obj.folder LIKE ' . $this->_db->Quote('' . $this->getState('filter.image_folder') . ''));
        }

        $query->order(
            $db->escape($this->getState('list.ordering', 'name')) . ' ' .
            $db->escape($this->getState('list.direction', 'ASC'))
        );

        return $query;
    }

    /**
     * sportsmanagementModelsmimageimports::getXMLFolder()
     * 
     * @return
     */
    function getXMLFolder() 
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection();
        // Create a new query object.
        $query = $db->getQuery(true);
        $query->select(array('folder as id', 'folder as name'))
            ->from('#__sportsmanagement_pictures')
            ->order('folder ASC')
            ->group('folder ASC');

        $db->setQuery($query);
        if (!$result = $db->loadObjectList()) {
            sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
            return array();
        }

        return $result;
    }

    /**
     * sportsmanagementModelsmimageimports::getimagesxml()
     * 
     * @return
     */
    function getimagesxml() 
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        // sind neue bilder pakete vorhanden ?
        //$content = file_get_contents('https://raw2.github.com/diddipoeler/sportsmanagement/master/admin/helpers/xml_files/pictures.xml');
        //$datei = "https://raw2.github.com/diddipoeler/sportsmanagement/master/admin/helpers/xml_files/pictures.xml";
        // das sollte vielleicht noch in die konfiguration und nicht als hardcodierte zeile
        // wenn im github was geändert wird, gibt es immer einen abbruch
        //$datei = "https://raw.githubusercontent.com/diddipoeler/sportsmanagement/master/admin/helpers/xml_files/pictures.xml";

        $datei = ComponentHelper::getParams($option)->get('cfg_images_server', '');

        if (function_exists('curl_version')) {
            $curl = curl_init();
            //Define header array for cURL requestes
            $header = array('Contect-Type:application/xml');
            curl_setopt($curl, CURLOPT_URL, $datei);
            curl_setopt($curl, CURLOPT_VERBOSE, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            //curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);


            if (curl_errno($curl)) {

            } else {
                $content = curl_exec($curl);
                curl_close($curl);
            }

        } else if (file_get_contents(__FILE__) && ini_get('allow_url_fopen')) {
            $content = file_get_contents($datei);
        } else {
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'), 'Error');
        }


        if ($content) {
            //--Löschen einer einzelnen Datei
            if (File::delete(JPATH_COMPONENT_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'xml_files' .DIRECTORY_SEPARATOR. 'pictures.xml')) {
                //echo 'Die Datei wurde gelöscht.';
            }

            //Parsen
            //$doc = DOMDocument::loadXML($content);
            $doc = new DOMDocument();
            $doc->loadXML($content);
            $doc->save(JPATH_COMPONENT_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'xml_files' .DIRECTORY_SEPARATOR. 'pictures.xml');
        }
    }

    /**
     * sportsmanagementModelsmimageimports::getXMLFiles()
     * 
     * @return
     */
    function getXMLFiles() 
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $query = Factory::getDbo()->getQuery(true);
        $files = array();
        $path = JPATH_COMPONENT_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'xml_files' .DIRECTORY_SEPARATOR. 'pictures.xml';
        //        $xml = Factory::getXMLParser( 'Simple' );
        //       $xml->loadFile($path); 

        if(version_compare(JVERSION, '4', 'ge')) {
            $xml = simplexml_load_file($path);
        } else {
            $xml = Factory::getXML($path);
        }
        
        $i = 0;

        foreach ($xml->children() as $picture) {
            $folder = (string) $picture->picture->attributes()->folder;
            $directory = (string) $picture->picture->attributes()->directory;
            $file = (string) $picture->picture->attributes()->file;

            $picturedescription = (string) $picture->picture;

            $temp = new stdClass();
            $temp->id = $i;
            $temp->picture = $picturedescription;
            $temp->folder = $folder;
            $temp->directory = $directory;
            $temp->file = $file;
            $export[] = $temp;
            $files = array_merge($export);
            $i++;

            $query->clear();
            $query->select('id');
            $query->from('#__sportsmanagement_pictures');
            $query->where('name LIKE ' . Factory::getDbo()->Quote('' . $picturedescription . ''));
            Factory::getDbo()->setQuery($query);
            if (!Factory::getDbo()->loadResult()) {
                $temp = new stdClass();
                $temp->name = $picturedescription;
                $temp->file = $file;
                $temp->directory = $directory;
                $temp->folder = $folder;
                $temp->published = 0;
                $result = Factory::getDbo()->insertObject('#__sportsmanagement_pictures', $temp);
            }
        }

        return $files;
    }

}

?>
