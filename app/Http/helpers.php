<?php

function active_class($path, $active = 'active') {

    return \Request::route()->getName() == $path ? $active : '';

}

function is_in_range($number, $start, $end){
	if($start>$end) {
		$t = $start;
		$start = $end;
		$end = $t;
	}

	return $start < $number && $end > $number;
}
