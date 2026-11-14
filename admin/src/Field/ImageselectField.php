<?php
/**
 * Legacy spelling bridge for the native Joomla 5/6 ImageSelect administrator field.
 *
 * PHP class names are case-insensitive: ImageselectField and ImageSelectField
 * must never be declared as two distinct classes on case-sensitive hosts.
 * Loading the canonical implementation here preserves both historical names.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

if (!class_exists(ImageSelectField::class, false)) {
    require_once __DIR__ . '/ImageSelectField.php';
}
