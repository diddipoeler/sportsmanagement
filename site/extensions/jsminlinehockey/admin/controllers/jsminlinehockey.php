<?PHP

defined('_JEXEC') or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;

class sportsmanagementControllerjsminlinehockey extends AdminController
{


function __construct()
    {
        parent::__construct();

        
    }
    
    function getmatches()
    {
    $model = $this->getModel ( 'jsminlinehockey' );
    $clubs  = $model->getmatches();
    $msg = 'Spiele importiert';
    $link = 'index.php?option=com_sportsmanagement&view=projects'; 
$this->setRedirect ( $link, $msg ); 

    
    }


    function getclubs()
    {
    $model = $this->getModel ( 'jsminlinehockey' );
    $clubs  = $model->getClubs();
    $msg = 'Vereine importiert';
    $link = 'index.php?option=com_sportsmanagement&view=clubs'; 
$this->setRedirect ( $link, $msg ); 

    }

    function save() {
	   //$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication ();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
		$document = Factory::getDocument ();
		// Check for request forgeries
		//Factory::getApplication()->input->checkToken () or die ( 'COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN' );
		$msg = '';

		$model = $this->getModel ( 'jsminlinehockey' );
        $post = $jinput->post->getArray(array());
        
        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');
        
        // first step - upload
		if (isset ( $post ['sent'] ) && $post ['sent'] == 1) 
        {
			$upload = Factory::getApplication()->input->getVar ( 'import_package', null, 'files', 'array' );
            $tempFilePath = $upload ['tmp_name'];
            $dest = JPATH_SITE . DS . 'tmp' . DS . $upload ['name'];
			$extractdir = JPATH_SITE . DS . 'tmp';
			$importFile = JPATH_SITE . DS . 'tmp' . DS . 'ish_bw_import.xls';
            
            $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' upload<br><pre>'.print_r($upload,true).'</pre>'),'');
            
            if (File::exists ( $importFile )) 
            {
				File::delete ( $importFile );
			}
            if (File::exists ( $tempFilePath )) 
            {
				if (File::exists ( $dest )) {
					File::delete ( $dest );
				}
				if (! File::upload ( $tempFilePath, $dest )) {
					JLog::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_CANT_UPLOAD'), JLog::WARNING, 'jsmerror');
					return;
				} else {
					if (strtolower ( File::getExt ( $dest ) ) == 'zip') {
						$result = JArchive::extract ( $dest, $extractdir );
						if ($result === false) {
							JLog::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_EXTRACT_ERROR'), JLog::WARNING, 'jsmerror');
							return false;
						}
						File::delete ( $dest );
						$src = Folder::files ( $extractdir, 'l98', false, true );
						if (! count ( $src )) {
							JLog::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_EXTRACT_NOJLG'), JLog::WARNING, 'jsmerror');
							// todo: delete every extracted file / directory
							return false;
						}
						if (strtolower ( File::getExt ( $src [0] ) ) == 'xls') {
							if (! @ rename ( $src [0], $importFile )) {
								JLog::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_ERROR_RENAME'), JLog::WARNING, 'jsmerror');
								return false;
							}
						} else {
							JLog::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_TMP_DELETED'), JLog::WARNING, 'jsmerror');
							return;
						}
					} else {
						if (strtolower ( File::getExt ( $dest ) ) == 'xls' || strtolower ( File::getExt ( $dest ) ) == 'ics') {
							if (! @ rename ( $dest, $importFile )) {
								JLog::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_RENAME_FAILED'), JLog::WARNING, 'jsmerror');
								return false;
							}
						} else {
							JLog::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_WRONG_EXTENSION'), JLog::WARNING, 'jsmerror');
							return false;
						}
					}
				}
			}
            
            }
        
        $xml_file = $model->getData ();
        
        
        }
        
    
    

    
    

}

?>
