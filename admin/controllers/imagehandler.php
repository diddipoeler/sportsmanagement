<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage imagehandler
 * @file       imagehandler.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Client\ClientHelper;

JLoader::import('components.com_sportsmanagement.helpers.imageselect', JPATH_SITE);

/**
 * sportsmanagementControllerImagehandler
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllerImagehandler extends JSMControllerAdmin
{
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra task
	}

	
    
    /**
     * sportsmanagementControllerImagehandler::uploadprojectteams()
     * 
     * @return void
     */
    function uploadprojectteams()
	{
		// Reference global application object
		$app = Factory::getApplication();
$datainput = Factory::getApplication()->input->getArray();
	
		$type = '';
		$msg  = '';
        $updatemodal  = true;
        
echo "<script>console.log('uploadprojectteams type: " . $datainput['type'] . "' );</script>";

if ( $datainput['imagelist'] )
		{
			$updatemodal  = false;
		}

		// Check for request forgeries
		//Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));

		// $file = $this->jsmjinput->getVar( 'userfile', '', 'files', 'array' );
		$file = $this->jsmjinput->files->get('userfile');

		// $task = $this->jsmjinput->getVar( 'task' );
		$type        = $datainput['type'];
		$folder      = ImageSelectSM::getfolder($type);
		$field       = $datainput['field'];
		$fieldid     = $datainput['fieldid'];
		$linkaddress = $datainput['linkaddress'];
        $pid       = $datainput['pid'];
        $mid       = $datainput['mid'];
        
        // Set FTP credentials, if given
		ClientHelper::setCredentialsFromRequest('ftp');
		echo "<script> window.closeModal = function(){
    jQuery('upload" . $fieldid . "').modal('hide');
}; </script>\n";
		$base_Dir = JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $this->jsmoption . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;

echo "<script>console.log('Debug Objects type: " . $type . "' );</script>";
echo "<script>console.log('Debug Objects folder: " . $folder . "' );</script>";
echo "<script>console.log('Debug Objects pid: " . $pid . "' );</script>";
echo "<script>console.log('Debug Objects mid: " . $mid . "' );</script>";
echo "<script>console.log('Debug Objects base_dir: " . $base_Dir . "' );</script>";
        
if (empty($file['name']))
		{
			echo "<script> alert('" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_IMAGE_EMPTY') . "'); window.history.go(-1); </script>\n";

			// $app->close();
		}

/*
		// Check the image
		$check = ImageSelectSM::check($file);

		if ($check === false)
		{
			$app->redirect($_SERVER['HTTP_REFERER']);
		}
*/
		// Sanitize the image filename
		$filename = ImageSelectSM::sanitize($base_Dir, $file['name']);
		$filepath = $base_Dir . $filename;

		// Upload the image
		if (!File::upload($file['tmp_name'], $filepath))
		{
			echo "<script> alert('" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED') . "'); window.history.go(-1); </script>\n";

			// $app->close();
			$msg  = Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED');
			$type = 'error';
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE'), '');

			//			echo "<script> alert('" . Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE'.'-'.$folder.'-'.$type.'-'.$filename.'-'.$field ) . "'); window.history.go(-1); window.parent.selectImage_".$type."('$filename', '$filename','$field'); </script>\n";
			//			echo "<script> alert('" . Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE' ) . "'); window.history.go(-1); window.parent.selectImage_".$type."('$filename', '$filename','$field'); </script>\n";
			if ( $updatemodal )
            {
            echo "<script>window.parent.selectImage_" . $type . "('$filename', '$filename','$field','$fieldid');window.closeModal();window.parent.jQuery('.modal.in').modal('hide'); </script>\n";
			}
            else
            {
            echo "<script>window.closeModal();window.parent.jQuery('.modal.in').modal('hide');parent.location.reload(); </script>\n";    
            }
            $msg  = Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE');
			$type = 'notice';

			// $app->close();
		}        
              
    }
    
	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'imagehandler', $prefix = 'sportsmanagementModel', $config = Array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	/**
	 * sportsmanagementControllerImagehandler::saveimageplayer()
	 * 
	 * @return void
	 */
	function saveimageplayer()
	{
	$data                        = array();
		$data['player_id']                  = Factory::getApplication()->input->getInt('player_id');
		$data['picture']                 = Factory::getApplication()->input->get('picture');
	$model               = $this->getModel();
	if (!$resultupdate = $model->saveimageplayer($data))
		{
			$result = "0" . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_SAVE_IMAGE_FALSE') . ': ' . $resultupdate;
		}
		else
		{
			$result = 'Nachricht' . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_SAVE_IMAGE');
		}

		echo json_encode($result);
		Factory::getApplication()->close();

	}
	
		/**
		 * sportsmanagementControllerImagehandler::saveimageclub()
		 * 
		 * @return void
		 */
		function saveimageclub()
	{
	$data                        = array();
		$data['club_id']                  = Factory::getApplication()->input->getInt('club_id');
		$data['picture']                 = Factory::getApplication()->input->get('picture');
	$model               = $this->getModel();
	if (!$resultupdate = $model->saveimageclub($data))
		{
			$result = "0" . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_SAVE_IMAGE_FALSE') . ': ' . $resultupdate;
		}
		else
		{
			$result = 'Nachricht' . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_SAVE_IMAGE');
		}

		echo json_encode($result);
		Factory::getApplication()->close();

	}
	
	/**
	 * sportsmanagementControllerImagehandler::saveimageteamplayer()
	 * 
	 * @return void
	 */
	function saveimageteamplayer()
	{
	$data                        = array();
		$data['teamplayer_id']                  = Factory::getApplication()->input->getInt('teamplayer_id');
		$data['picture']                 = Factory::getApplication()->input->get('picture');
	$model               = $this->getModel();
	if (!$resultupdate = $model->saveimageteamplayer($data))
		{
			$result = "0" . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_SAVE_IMAGE_FALSE') . ': ' . $resultupdate;
		}
		else
		{
			$result = 'Nachricht' . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_SAVE_IMAGE');
		}

		echo json_encode($result);
		Factory::getApplication()->close();

	}
	
	
	/**
	 * logic for uploading an image
	 *
	 * @access public
	 * @return void
	 * @since  0.9
	 */
	function upload()
	{
		// Reference global application object
		$app = Factory::getApplication();
$datainput = Factory::getApplication()->input->getArray();

if ( !array_key_exists( 'linkaddress', $datainput ) );
{
$datainput['linkaddress'] = '';    
}
		// JInput object
		// $jinput = $app->input;
		// $option = $this->jsmjinput->getCmd('option');

		$type = '';
		$msg  = '';
        $updatemodal  = true;
        
echo "<script>console.log('Debug Objects type: " . $datainput['type'] . "' );</script>";

		if ( $datainput['imagelist'] )
		{
			$updatemodal  = false;
		}

		// Check for request forgeries
		Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));

		// $file = $this->jsmjinput->getVar( 'userfile', '', 'files', 'array' );
		$file = $this->jsmjinput->files->get('userfile');

		// $task = $this->jsmjinput->getVar( 'task' );
		$type        = $datainput['type'];
		$folder      = ImageSelectSM::getfolder($type);
		$field       = $datainput['field'];
		$fieldid     = $datainput['fieldid'];
		$linkaddress = $datainput['linkaddress'];
        $pid       = $datainput['pid'];
        $mid       = $datainput['mid'];
        
        
        switch ($type)
		{
		case "projectimages":
				//return "projectimages/".$data['pid'];
                $folder .= "/".$pid;
                $updatemodal  = false;
				break;  
          case "matchreport":
				//return "projectimages/".$data['pid'];
                $folder .= "/".$mid;
                $updatemodal  = false;
				break; 
		//default:
		//$updatemodal  = false;
		//break; 
          }

		// Set FTP credentials, if given
		ClientHelper::setCredentialsFromRequest('ftp');
		echo "<script> window.closeModal = function(){
    jQuery('upload" . $fieldid . "').modal('hide');
}; </script>\n";
		$base_Dir = JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $this->jsmoption . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;

echo "<script>console.log('Debug Objects type: " . $type . "' );</script>";
echo "<script>console.log('Debug Objects folder: " . $folder . "' );</script>";
echo "<script>console.log('Debug Objects pid: " . $pid . "' );</script>";
echo "<script>console.log('Debug Objects mid: " . $mid . "' );</script>";
echo "<script>console.log('Debug Objects base_dir: " . $base_Dir . "' );</script>";

		// Do we have an imagelink?
		if (!empty($linkaddress))
		{
			$file['name'] = basename($linkaddress);

			if (preg_match("/dfs_/i", $linkaddress))
			{
				$filename = $file['name'];
			}
			else
			{
				// Sanitize the image filename
				$filename = ImageSelectSM::sanitize($base_Dir, $file['name']);
			}

			$filepath = $base_Dir . $filename;

			if (!copy($linkaddress, $filepath))
			{
				echo "<script> alert('" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_COPY_FAILED') . "'); window.history.go(-1); </script>\n";

				// $app->close();
			}
			else
			{
				// Echo "<script> alert('" . Text::_( 'COPY COMPLETE'.'-'.$folder.'-'.$type.'-'.$filename.'-'.$field ) . "'); window.history.go(-1); window.parent.selectImage_".$type."('$filename', '$filename','$field'); </script>\n";
				echo "<script>  window.parent.selectImage_" . $type . "('$filename', '$filename','$field');window.parent.SqueezeBox.close(); </script>\n";

				// $app->close();
			}
		}

		// Do we have an upload?
		if (empty($file['name']))
		{
			echo "<script> alert('" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_IMAGE_EMPTY') . "'); window.history.go(-1); </script>\n";

			// $app->close();
		}
/*
		// Check the image
		$check = ImageSelectSM::check($file);

		if ($check === false)
		{
			$app->redirect($_SERVER['HTTP_REFERER']);
		}
*/
		// Sanitize the image filename
		$filename = ImageSelectSM::sanitize($base_Dir, $file['name']);
		$filepath = $base_Dir . $filename;

		// Upload the image
		if (!File::upload($file['tmp_name'], $filepath))
		{
			echo "<script> alert('" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED') . "'); window.history.go(-1); </script>\n";

			// $app->close();
			$msg  = Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED');
			$type = 'error';
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE'), '');

			//			echo "<script> alert('" . Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE'.'-'.$folder.'-'.$type.'-'.$filename.'-'.$field ) . "'); window.history.go(-1); window.parent.selectImage_".$type."('$filename', '$filename','$field'); </script>\n";
			//			echo "<script> alert('" . Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE' ) . "'); window.history.go(-1); window.parent.selectImage_".$type."('$filename', '$filename','$field'); </script>\n";
			if ( $updatemodal )
            {
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
echo "<script>window.parent.selectImage_" . $type . "('$filename', '$filename','$field','$fieldid');window.parent.Joomla.Modal.getCurrent().close(); </script>\n";                
}               
else
{
echo "<script>window.parent.selectImage_" . $type . "('$filename', '$filename','$field','$fieldid');window.closeModal();window.parent.jQuery('.modal.in').modal('hide'); </script>\n";
}
            
			}
            else
            {
                
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
echo "<script>window.closeModal();window.parent.Joomla.Modal.getCurrent().close();parent.location.reload(); </script>\n";    
}
else
{
echo "<script>window.closeModal();window.parent.jQuery('.modal.in').modal('hide');parent.location.reload(); </script>\n";        
}
                
                
            }
            $msg  = Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE');
			$type = 'notice';

			// $app->close();
		}

	}

	/**
	 * logic to mass delete images
	 *
	 * @access public
	 * @return void
	 * @since  0.9
	 */
	function delete()
	{
		// Reference global application object
		$app = Factory::getApplication();
		ClientHelper::setCredentialsFromRequest('ftp');

		// Get some data from the request
		$images = $this->jsmjinput->getVar('rm', array(), '', 'array');
		$type   = $this->jsmjinput->getVar('type');

		$folder = ImageSelectSM::getfolder($type);

		if (count($images))
		{
			foreach ($images as $image)
			{
				$fullPath      = Path::clean(JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $this->jsmoption . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $image);
				$fullPaththumb = Path::clean(JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $this->jsmoption . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . 'small' . DIRECTORY_SEPARATOR . $image);

				if (is_file($fullPath))
				{
					File::delete($fullPath);

					if (File::exists($fullPaththumb))
					{
						File::delete($fullPaththumb);
					}
				}
			}
		}

		$app->redirect('index.php?option=' . $this->jsmoption . '&view=imagehandler&type=' . $type . '&tmpl=component');
	}

}
