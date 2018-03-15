<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      imagehandler.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage imagehandler
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');
//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'imageselect.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'imageselect.php');

class sportsmanagementControllerImagehandler extends JControllerLegacy
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
	 * logic for uploading an image
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function upload()
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');

		// Check for request forgeries
		JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

		//$file	= $jinput->getVar( 'userfile', '', 'files', 'array' );
        $file = $jinput->files->get('userfile');
		//$task	= $jinput->getVar( 'task' );
		$type	= $jinput->getVar( 'type' );
		$folder	= ImageSelectSM::getfolder($type);
		$field	= $jinput->getVar( 'field' );
		$linkaddress	= $jinput->getVar( 'linkaddress' );
		// Set FTP credentials, if given
		jimport( 'joomla.client.helper' );
		JClientHelper::setCredentialsFromRequest( 'ftp' );
		//$ftp = JClientHelper::getCredentials( 'ftp' );

		$base_Dir = JPATH_SITE . DS . 'images' . DS . $option . DS .'database'.DS. $folder . DS;
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jinput<pre>'.print_r($jinput,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' file<pre>'.print_r($file,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.$type),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.$folder),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.$base_Dir),'');

    //do we have an imagelink?
    if ( !empty( $linkaddress ) )
    {
    $file['name'] = basename($linkaddress);
    
if (preg_match("/dfs_/i", $linkaddress)) 
{
$filename = $file['name'];
}
else
{
//sanitize the image filename
$filename = ImageSelectSM::sanitize( $base_Dir, $file['name'] );
}

		
		$filepath = $base_Dir . $filename;
		
if ( !copy($linkaddress,$filepath) )
{
echo "<script> alert('".JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_COPY_FAILED' )."'); window.history.go(-1); </script>\n";
//$app->close();
}
else
{
//echo "<script> alert('" . JText::_( 'COPY COMPLETE'.'-'.$folder.'-'.$type.'-'.$filename.'-'.$field ) . "'); window.history.go(-1); window.parent.selectImage_".$type."('$filename', '$filename','$field'); </script>\n";
echo "<script>  window.parent.selectImage_".$type."('$filename', '$filename','$field');window.parent.SqueezeBox.close(); </script>\n";
//$app->close();
}

    
    }
    
		//do we have an upload?
		if ( empty( $file['name'] ) )
		{
			echo "<script> alert('".JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_IMAGE_EMPTY' )."'); window.history.go(-1); </script>\n";
			//$app->close();
		}

		//check the image
		$check = ImageSelectSM::check( $file );

		if ( $check === false )
		{
			$app->redirect( $_SERVER['HTTP_REFERER'] );
		}

		//sanitize the image filename
		$filename = ImageSelectSM::sanitize( $base_Dir, $file['name'] );
		$filepath = $base_Dir . $filename;

		//upload the image
		if ( !JFile::upload( $file['tmp_name'], $filepath ) )
		{
			echo "<script> alert('".JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED' )."'); window.history.go(-1); </script>\n";
			//$app->close();

		}
		else
		{
//			echo "<script> alert('" . JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE'.'-'.$folder.'-'.$type.'-'.$filename.'-'.$field ) . "'); window.history.go(-1); window.parent.selectImage_".$type."('$filename', '$filename','$field'); </script>\n";
//			echo "<script> alert('" . JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE' ) . "'); window.history.go(-1); window.parent.selectImage_".$type."('$filename', '$filename','$field'); </script>\n";
      echo "<script>  window.parent.selectImage_".$type."('$filename', '$filename','$field');window.parent.SqueezeBox.close(); </script>\n";
			//$app->close();
		}

	}

	/**
	 * logic to mass delete images
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function delete()
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
		// Set FTP credentials, if given
		jimport( 'joomla.client.helper' );
		JClientHelper::setCredentialsFromRequest( 'ftp' );

		// Get some data from the request
		$images	= $jinput->getVar( 'rm', array(), '', 'array' );
		$type	= $jinput->getVar( 'type' );

		$folder	= ImageSelectSM::getfolder( $type );

		if ( count( $images ) )
		{
			foreach ( $images as $image )
			{
				if ( $image !== JFilterInput::clean( $image, 'path' ) )
				{
					JError::raiseWarning( 100, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UNABLE_TO_DELETE' ) . ' ' . htmlspecialchars( $image, ENT_COMPAT, 'UTF-8' ) );
					continue;
				}

				$fullPath = JPath::clean( JPATH_SITE . DS . 'images' . DS . $option . DS .'database'.DS. $folder . DS . $image );
				$fullPaththumb = JPath::clean( JPATH_SITE . DS . 'images' . DS . $option . DS .'database'.DS. $folder . DS . 'small' . DS . $image );
				if ( is_file( $fullPath ) )
				{
					JFile::delete( $fullPath );
					if ( JFile::exists( $fullPaththumb ) )
					{
						JFile::delete( $fullPaththumb );
					}
				}
			}
		}

		$app->redirect( 'index.php?option='.$option.'&view=imagehandler&type=' . $type . '&tmpl=component' );
	}

}
?>
