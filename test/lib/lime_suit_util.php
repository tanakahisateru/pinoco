<?php
require_once dirname(__FILE__) . '/lime.php';

function _run_unit_test_innner($test)
{
    include $test;
    
    $all_results = lime_test::to_array();
    $numtests = count($all_results);
    lime_test::reset();
    for($i = 0; $i < $numtests; $i++) {
        restore_error_handler();
        restore_exception_handler();
    }
    
    return $all_results;
}

function run_unit_test($test, $display_each_result=false)
{
    echo $test . ":\n";
    if(!$display_each_result) {
        ob_start();
    }
    $all_results = _run_unit_test_innner($test);
    if(!$display_each_result) {
        ob_end_clean();
    }
    foreach($all_results as $results) {
        $passed = $results['stats']['passed'];
        $skipped = $results['stats']['skipped'];
        $failed = $results['stats']['failed'];
        $total = $results['stats']['total'];
        printf("%d/%d tests succeseeded.", count($passed), $total);
        if(!empty($skipped)) {
            printf(" (%d tests skipped.)", count($skipped));
        }
        echo "\n";
        
        foreach($failed as $n) {
            $message = $results['tests'][$n]['message'];
            $status = $results['tests'][$n]['status'];
            printf("not ok %d%s\n", $n, $message);
            printf('    Failed test (%s at line %d)',
                str_replace(getcwd(), '.', $results['tests'][$n]['file']),
                $results['tests'][$n]['line']
            );
            echo "\n";
            if($results['tests'][$n]['error']) {
                echo $results['tests'][$n]['error'];
                echo "\n";
            }
        }
        
        foreach($skipped as $n) {
            $message = $results['tests'][$n]['message'];
            $status = $results['tests'][$n]['status'];
            printf("skipped %d%s\n", $n, $message);
            printf('    Skipped test (%s at line %d)',
                str_replace(getcwd(), '.', $results['tests'][$n]['file']),
                $results['tests'][$n]['line']
            );
            echo "\n";
        }
        echo "\n";
    }
}

