<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      uploaddraganddrop.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage imagehandler
 */
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/fileupload/vendor/jquery.ui.widget.js');
$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/fileupload/jquery.iframe-transport.js');
$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/fileupload/jquery.fileupload.js');
$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/fileupload/jquery.fileupload-process.js');
$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/fileupload/jquery.fileupload-image.js');
$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/fileupload/jquery.fileupload-audio.js');
$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/fileupload/jquery.fileupload-video.js');
$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/fileupload/jquery.fileupload-validate.js');

$this->document->addStyleSheet(Uri::root() .'administrator/components/com_sportsmanagement/assets/css/fileupload/style.css', 'text/css');
$this->document->addStyleSheet(Uri::root() .'administrator/components/com_sportsmanagement/assets/css/fileupload/jquery.fileupload.css', 'text/css');

?>
