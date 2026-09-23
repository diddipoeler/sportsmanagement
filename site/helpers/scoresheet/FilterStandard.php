<?php
/**
 * FPDM raw stream compatibility filter.
 *
 * @version    2.10
 * @author     Olivier Plathey
 * @copyright  Copyright (C) Olivier Plathey
 * @license    FPDF License
 */
\defined('_JEXEC') or die;

//
//  FPDM - Filter Standard
//  NOTE: dummy filter for unfiltered streams!
//

if (isset($FPDM_FILTERS)) array_push($FPDM_FILTERS, "Standard");

class FilterStandard
{

	function decode($data)
	{
		return $data;
	}

	function encode($data)
	{
		return $data;
	}
}

?>
