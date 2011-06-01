<?php
function run_unit_test($test, $display_each_result=false)
{
    echo $test . ":\n";
    $p = popen('php ' . $test, 'r');
    $buf = "";
    while($b = fread($p, 1024)) {
        $buf .= $b;
    }
    fclose($p);
    $rs = explode("\n", $buf);
    foreach($rs as $r) {
        if(!$display_each_result && preg_match('/^ok/i', $r)) {
            continue;
        }
        echo $r . "\n";
    }
}

