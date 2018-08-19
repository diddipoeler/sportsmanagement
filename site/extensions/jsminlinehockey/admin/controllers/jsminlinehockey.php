<?PHP

defined('_JEXEC') or die( 'Restricted access' );

//jimport( 'joomla.application.component.controller' );
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');


class sportsmanagementControllerjsminlinehockey extends JControllerAdmin
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
	   //$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication ();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
		$document = JFactory::getDocument ();
		// Check for request forgeries
		//JFactory::getApplication()->input->checkToken () or die ( 'COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN' );
		$msg = '';

		// $app = JFactory::getApplication();
		$model = $this->getModel ( 'jsminlinehockey' );
        //$post = JFactory::getApplication()->input->get ( 'post' );
        $post = $jinput->post->getArray(array());
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');
        
        // first step - upload
		if (isset ( $post ['sent'] ) && $post ['sent'] == 1) 
        {
			$upload = JFactory::getApplication()->input->getVar ( 'import_package', null, 'files', 'array' );
            $tempFilePath = $upload ['tmp_name'];
            $dest = JPATH_SITE . DS . 'tmp' . DS . $upload ['name'];
			$extractdir = JPATH_SITE . DS . 'tmp';
			$importFile = JPATH_SITE . DS . 'tmp' . DS . 'ish_bw_import.xls';
            
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' upload<br><pre>'.print_r($upload,true).'</pre>'),'');
            
            if (JFile::exists ( $importFile )) 
            {
				JFile::delete ( $importFile );
			}
            if (JFile::exists ( $tempFilePath )) 
            {
				if (JFile::exists ( $dest )) {
					JFile::delete ( $dest );
				}
				if (! JFile::upload ( $tempFilePath, $dest )) {
					JError::raiseWarning ( 500, JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_CANT_UPLOAD' ) );
					return;
				} else {
					if (strtolower ( JFile::getExt ( $dest ) ) == 'zip') {
						$result = JArchive::extract ( $dest, $extractdir );
						if ($result === false) {
							JError::raiseWarning ( 500, JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_EXTRACT_ERROR' ) );
							return false;
						}
						JFile::delete ( $dest );
						$src = JFolder::files ( $extractdir, 'l98', false, true );
						if (! count ( $src )) {
							JError::raiseWarning ( 500, 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_EXTRACT_NOJLG' );
							// todo: delete every extracted file / directory
							return false;
						}
						if (strtolower ( JFile::getExt ( $src [0] ) ) == 'xls') {
							if (! @ rename ( $src [0], $importFile )) {
								JError::raiseWarning ( 21, JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_ERROR_RENAME' ) );
								return false;
							}
						} else {
							JError::raiseWarning ( 500, JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_TMP_DELETED' ) );
							return;
						}
					} else {
						if (strtolower ( JFile::getExt ( $dest ) ) == 'xls' || strtolower ( JFile::getExt ( $dest ) ) == 'ics') {
							if (! @ rename ( $dest, $importFile )) {
								JError::raiseWarning ( 21, JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_RENAME_FAILED' ) );
								return false;
							}
						} else {
							JError::raiseWarning ( 21, JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_WRONG_EXTENSION' ) );
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
