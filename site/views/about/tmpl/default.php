<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage about
 */
defined('_JEXEC') or die('Restricted access');


/*
  <object type="application/x-shockwave-flash" data="media/com_sportsmanagement/jl_images/joomleague_logo.swf" id="movie" width="410" height="200">
  <param name="movie" value="media/com_sportsmanagement/jl_images/joomleague_logo.swf" />
  <param name="bgcolor" value="#FFFFFF" />
  <param name="quality" value="high" />
  <param name="loop" value="false" />
  <param name="allowscriptaccess" value="samedomain" />
  </object>
 */
?>
<table class="about">
    <tr>
        <td align="center">
            <?PHP
            // Reference global application object
            $app = JFactory::getApplication();
            // JInput object
            $jinput = $app->input;
            $option = $jinput->getCmd('option');
            $backgroundimage = 'administrator/components/' . $option . '/assets/icons/logo_transparent.png';

            //echo $backgroundimage.'<br>';

            echo "<img class=\"\" style=\"\" src=\"" . $backgroundimage . "\" alt=\"\" width=\"200\">";
            ?>		
        </td>
    </tr>
</table>
<br />
<div class="componentheading">
    <?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT'); ?>
</div>
<table class="about">
    <tr>
        <td><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_TEXT'); ?></td>
    </tr>
</table>
<br />

<div class="componentheading">
    <?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_DIDDIPOELER'); ?>
</div>
<table class="about">
    <tr>
        <td>
            <b>
                <?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_TEXT_DIDDIPOELER'); ?>
            </b>
        </td>
        <td>
            <?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_TEXT_DESC_DIDDIPOELER'); ?>
        </td>
    </tr>
    <tr>
        <td>
            <b>
                <?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_WEBSITE_DIDDIPOELER'); ?>
            </b>
        </td>
        <td>
            <a href="<?php echo $this->about->diddipoelerpage; ?>" target="_blank">
                <?php echo $this->about->diddipoelerpage; ?>
            </a>
        </td>
    </tr>
    <tr>
        <td>
            <b>
                <?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_SUPPORT_FORUM_DIDDIPOELER'); ?>
            </b>
        </td>
        <td>
            <a href="<?php echo $this->about->diddipoelerforum; ?>" target="_blank">
                <?php echo "Fussballineuropa Forum"; ?>
            </a>
        </td>
    </tr> 

    <tr>
        <td>
            <b>
                <?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_GITHUB_DIDDIPOELER'); ?>
            </b><
        </td>
        <td>
            <a href="<?php echo $this->about->github; ?>" target="_blank">
                <?php echo 'Github sportsmanagement diddipoeler'; ?>
            </a>
        </td>
    </tr>

    <tr>
        <td>
            <b>
                <?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_SUPPORT_EMAIL_DIDDIPOELER'); ?>
            </b>
        </td>
        <td>
            <a href="mailto:<?php echo $this->about->diddipoeleremail; ?>" target="_blank">
                <?php echo $this->about->diddipoeleremail; ?>
            </a>
        </td>
    </tr>
</table>

<div class="componentheading">
    <?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_DETAILS'); ?>
</div>

<table class="about">
    <!--
            <tr>
                    <td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_TRANSLATIONS'); ?></b></td>
                    <td><?php echo $this->about->translations; ?></td>
            </tr>
            <tr>
                    <td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_REPOSITORY'); ?></b></td>
                    <td><?php echo $this->about->repository; ?></td>
            </tr>
            <tr>
                    <td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_VERSION'); ?></b></td>
                    <td><?php echo $this->about->version; ?></td>
            </tr>
            <tr>
                    <td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_AUTHOR'); ?></b></td>
                    <td><?php echo $this->about->author; ?></td>
            </tr>
    
            <tr>
                    <td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_WEBSITE'); ?></b></td>
                    <td>
                            <a href="<?php echo $this->about->page; ?>" target="_blank">
                                <?php echo $this->about->page; ?>
                            </a>
                    </td>
            </tr>
            <tr>
                    <td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_SUPPORT_FORUM'); ?></b></td>
                    <td>
                            <a href="<?php echo $this->about->forum; ?>" target="_blank">
                                <?php echo $this->about->forum; ?>
                            </a>
                    </td>
            </tr>
            <tr>
                    <td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_BUGS'); ?></b></td>
                    <td>
                            <a href="<?php echo $this->about->bugs; ?>" target="_blank">
                                <?php echo $this->about->bugs; ?></a>
                    </td>
            </tr>
            <tr>
                    <td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_WIKI'); ?></b></td>
                    <td>
                            <a href="<?php echo $this->about->wiki; ?>" target="_blank">
                                <?php echo $this->about->wiki; ?></a>
                    </td>
            </tr>	
            <tr>
                    <td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_DEVELOPERS'); ?></b></td>
                    <td><?php echo $this->about->developer; ?></td>
            </tr>
    
            <tr>
                    <td><b><?php //echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_SUPPORTERS');   ?></b></td>
                    <td><?php //echo $this->about->supporters;   ?></td>
            </tr>
            <tr>
                    <td><b><?php //echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_TRANSLATORS');   ?></b></td>
                    <td><?php //echo $this->about->translator;   ?></td>
            </tr>
    -->
    <tr>
        <td>
            <b>
                <?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_DESIGNER'); ?>
            </b>
        </td>
        <td>
            <?php echo $this->about->designer; ?>
        </td>
    </tr>
    <tr>
        <td>
            <b>
                <?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_DEVELOPERS'); ?>
            </b>
        </td>
        <td>
            <?php echo $this->about->developer; ?>
        </td>
    </tr>

    <!--    
            <tr>
                    <td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_ICONS'); ?></b></td>
                    <td><?php echo $this->about->icons; ?></td>
            </tr>
            <tr>
                    <td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_FLASH_STATISTICS'); ?></b></td>
                    <td><?php echo $this->about->flash; ?></td>
            </tr>
            <tr>
                    <td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_PHPTHUMB'); ?></b></td>
                    <td><?php echo $this->about->phpthumb; ?></td>
            </tr>	
    
            <tr>
                    <td><b><?php //echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_GRAPHIC_LIBRARY');   ?></b></td>
                    <td><?php //echo $this->about->graphic_library;   ?></td>
            </tr>
    -->
</table>
<br />

<div class="componentheading">
    <?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_LICENSE'); ?>
</div>

<table class="about">
    <tr>
        <td>
            <?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_LICENSE_TEXT'); ?>
        </td>
    </tr>
</table>

<!-- backbutton -->
<?php
//	echo $this->loadTemplate('backbutton');

/*
  if ($this->config['show_back_button'] > "0")
  {
  echo $this->loadTemplate('backbutton');
  }
  ?>
  <!-- footer -->
  <?php echo $this->loadTemplate('footer');
 */
?>