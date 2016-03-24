<?php

function active_class($path, $active = 'active') {

    return \Request::route()->getName() == $path ? $active : '';

}
