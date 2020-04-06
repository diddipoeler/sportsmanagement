<?PHP
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       jsminlinehockey.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage extension jsminlinehockey controllers
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementControllerjsminlinehockey
 * 
 * @package 
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllerjsminlinehockey extends AdminController
{

    /**
 * sportsmanagementControllerjsminlinehockey::__construct()
 * 
 * @return void
 */
    function __construct()
    {
        parent::__construct();
    }
    
    /**
     * sportsmanagementControllerjsminlinehockey::getmatches()
     * 
     * @return void
     */
    function getmatches()
    {
        $model = $this->getModel('jsminlinehockey');
        $clubs  = $model->getmatches();
        $msg = 'Spiele importiert';
        $link = 'index.php?option=com_sportsmanagement&view=projects'; 
        $this->setRedirect($link, $msg); 
    }

    /**
     * sportsmanagementControllerjsminlinehockey::getclubs()
     * 
     * @return void
     */
    function getclubs()
    {
        $model = $this->getModel('jsminlinehockey');
        $clubs  = $model->getClubs();
        $msg = 'Vereine importiert';
        $link = 'index.php?option=com_sportsmanagement&view=clubs'; 
        $this->setRedirect($link, $msg); 

    }

    /**
     * sportsmanagementControllerjsminlinehockey::save()
     * 
     * @return
     */
    function save() 
    {
        $app = Factory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $document = Factory::getDocument();
        $msg = '';

        $model = $this->getModel('jsminlinehockey');
        $post = $jinput->post->getArray(array());
        
        // first step - upload
        if (isset($post ['sent']) && $post ['sent'] == 1) {
               $upload = Factory::getApplication()->input->getVar('import_package', null, 'files', 'array');
            $tempFilePath = $upload ['tmp_name'];
            $dest = JPATH_SITE .DIRECTORY_SEPARATOR. 'tmp' .DIRECTORY_SEPARATOR. $upload ['name'];
               $extractdir = JPATH_SITE .DIRECTORY_SEPARATOR. 'tmp';
               $importFile = JPATH_SITE .DIRECTORY_SEPARATOR. 'tmp' .DIRECTORY_SEPARATOR. 'ish_bw_import.xls';
            
            if (File::exists($importFile)) {
                File::delete($importFile);
            }
            if (File::exists($tempFilePath)) {
                if (File::exists($dest)) {
                             File::delete($dest);
                }
                if (! File::upload($tempFilePath, $dest)) {
                             Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_CANT_UPLOAD'), Log::WARNING, 'jsmerror');
                             return;
                } else {
                    if (strtolower(File::getExt($dest)) == 'zip') {
                        $result = JArchive::extract($dest, $extractdir);
                        if ($result === false) {
                            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_EXTRACT_ERROR'), Log::WARNING, 'jsmerror');
                            return false;
                        }
                        File::delete($dest);
                        $src = Folder::files($extractdir, 'l98', false, true);
                        if (! count($src)) {
                            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_EXTRACT_NOJLG'), Log::WARNING, 'jsmerror');
                            // todo: delete every extracted file / directory
                            return false;
                        }
                        if (strtolower(File::getExt($src [0])) == 'xls') {
                            if (! @ rename($src [0], $importFile)) {
                                Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_ERROR_RENAME'), Log::WARNING, 'jsmerror');
                                return false;
                            }
                        } else {
                            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_TMP_DELETED'), Log::WARNING, 'jsmerror');
                            return;
                        }
                    } else {
                        if (strtolower(File::getExt($dest)) == 'xls' || strtolower(File::getExt($dest)) == 'ics') {
                            if (! @ rename($dest, $importFile)) {
                                Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_RENAME_FAILED'), Log::WARNING, 'jsmerror');
                                return false;
                            }
                        } else {
                            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_WRONG_EXTENSION'), Log::WARNING, 'jsmerror');
                            return false;
                        }
                    }
                }
            }
            
        }
        /**
* 
 * es wird keine excel verarbeitung mehr angeboten 
*/
        // $xml_file = $model->getData ();
        
    }

}

?>
