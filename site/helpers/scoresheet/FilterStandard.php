<?php

	//
	//  FPDM - Filter Standard
	//  NOTE: dummy filter for unfiltered streams!
	//
	defined('_JEXEC') or die('Restricted access');
	
	if(isset($FPDM_FILTERS)) array_push($FPDM_FILTERS,"Standard");

	class FilterStandard {

		function decode($data) {
			return $data;
		}

		function encode($data) {
			return $data;
		}
	}
?>
