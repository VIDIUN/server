<?php

require_once(dirname(__FILE__) . '/../bootstrap.php');

var_dump(vIP2Location::ipToCountry($argv[1]));