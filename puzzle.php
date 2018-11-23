#!/usr/bin/php
<?php

CONST RUNS = 100000;

function gen_puzzle() {
    $puzzle = [
        [0, 0, 0],
        [0, 0, 0],
        [0, 0, 0],
    ];

    $randomKeys = array_rand(range(1, 9), 4);

    for ($i = 0; $i <= 3; $i++)
        $puzzle[intdiv($randomKeys[$i], 3)][$randomKeys[$i] % 3] = $i + 1;

    return $puzzle;
}

function print_puzzle($puzzle) {
    for($i = 0; $i <= 2; $i++) {
        for ($j = 0; $j <= 2; $j++) {
            echo $puzzle[$i][$j] > 0
                ? $puzzle[$i][$j]
                : iconv('cp437', 'utf8', chr(219));
            echo ' ';
        }
        echo "\n";
    }
}

function print_floor($floor) {
    echo "               P2           \n";
    echo "               |            \n";
    for($i = 0; $i <= 2; $i++) {
        for ($j = 0; $j <= 2; $j++) {
            if($i == 1 && $j == 0)
                echo 'P1- ';
            elseif($j == 0)
                echo '    ';
            echo !empty($floor[$i][$j])
                ? str_pad(json_encode($floor[$i][$j]), 7, ' ', STR_PAD_BOTH)
                : str_repeat(iconv('cp437', 'utf8', chr(219)), 7);
            echo ' ';
            echo $i == 1 && $j == 2
                ? '-P3'
                : '';
        }
        echo "\n";
    }
    echo "               |            \n";
    echo "             Entry          \n";
}

function rotate90($matrix, $times = 1) {
    for($t = 1; $t <= $times; $t++) {
        $mat = $matrix;
        array_unshift($mat, null);
        $mat = call_user_func_array('array_map', $mat);
        $matrix = array_map('array_reverse', $mat);
    }
    return $matrix;
}

function populate_floor($p1, $p2, $p3) {
    $floor = [
        [[],[],[]],
        [[],[],[]],
        [[],[],[]],
    ];
    $floor = rotate90($floor);
    for($i = 0; $i <= 2; $i++)
        for ($j = 0; $j <= 2; $j++)
            if($p1[$i][$j] > 0)
                $floor[$i][$j][] = $p1[$i][$j];
    $floor = rotate90($floor, 3);
    for($i = 0; $i <= 2; $i++)
        for ($j = 0; $j <= 2; $j++)
            if($p2[$i][$j] > 0)
                $floor[$i][$j][] = $p2[$i][$j];
    $floor = rotate90($floor, 3);
    for($i = 0; $i <= 2; $i++)
        for ($j = 0; $j <= 2; $j++)
            if($p3[$i][$j] > 0)
                $floor[$i][$j][] = $p3[$i][$j];
    $floor = rotate90($floor);
    return $floor;
}

function count_colisions() {
    global $collisions;
    global $floor;

    for($i = 0; $i <= 2; $i++)
        for ($j = 0; $j <= 2; $j++) {
            if(@max(array_count_values($floor[$i][$j])) > 1) {
                $collisions++;
                return;
            }
        }
}

for($i = 1; $i <= RUNS; $i++) {
    $p1 = gen_puzzle();
    $p2 = gen_puzzle();
    $p3 = gen_puzzle();

    echo "Iteration: $i\n";
    echo "--------------------------------------------------\n";

    echo "P1:\n";
    print_puzzle($p1);

    echo "P2:\n";
    print_puzzle($p2);

    echo "P3:\n";
    print_puzzle($p3);

    $floor = populate_floor($p1, $p2, $p3);

    echo "Floor:\n";
    print_floor($floor);
    echo "--------------------------------------------------\n";

    count_colisions();
}

echo sprintf("Runs: %d, Collisions: %d, Percentage: %.2f%%\n", RUNS, $collisions, $collisions*100/RUNS);