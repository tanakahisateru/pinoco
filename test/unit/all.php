<?php
require_once dirname(__FILE__) . '/../lib/lime_suit_util.php';
run_unit_test(dirname(__FILE__) . '/test_vars.php');
run_unit_test(dirname(__FILE__) . '/test_list.php');
run_unit_test(dirname(__FILE__) . '/test_lazy.php');
run_unit_test(dirname(__FILE__) . '/test_pdo.php');
run_unit_test(dirname(__FILE__) . '/test_pinoco.php');

