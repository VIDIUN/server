<?php
/**
 * @package infra
 * @subpackage Conversion
 */

require_once '../vOperator.php';
require_once '../vOperatorSets.php';


$op_a1 = new vOperator();
$op_a1->id = 1;
$op_a1->extra = 'A extra params 1 ';
$op_a1->command = 'A command line data 1';

$op_a2 = new vOperator();
$op_a2->id = 2;
$op_a2->extra = 'A extra params 2';
$op_a2->command = 'A command line data 2';

$set_a = array($op_a1, $op_a2);

$op_b1 = new vOperator();
$op_b1->id = 1;
$op_b1->extra = 'B extra params 1 ';
$op_b1->command = 'B command line data 1';

$op_b2 = new vOperator();
$op_b2->id = 2;
$op_b2->extra = 'B extra params 2';
$op_b2->command = 'B command line data 2';

$set_b = array($op_b1, $op_b2);

$obj = new vOperatorSets();
$obj->addSet($set_a);
$obj->addSet($set_b);

echo $obj->getSerialized();
