#!/usr/bin/php
<?php

const RUNS = 100000;
const NORMAL = '1234';
const NUMBERED = [
    NORMAL,
    NORMAL,
    NORMAL,
];
const INCREMENTAL = [
    '1234',
    '2341',
    '1234',
];
const INCREMENTALALL = [
    '1234',
    '2341',
    '3412',
];
const NUMBERLETTERS = [
    '123A',
    '123B',
    '12BC',
];
const STRAT = NUMBERED;

function gen_puzzle($strat = NORMAL) {
    $puzzle = [
        [0, 0, 0],
        [0, 0, 0],
        [0, 0, 0],
    ];

    $randomKeys = array_rand(range(1, 9), 4);

    for ($i = 0; $i <= 3; $i++)
        $puzzle[intdiv($randomKeys[$i], 3)][$randomKeys[$i] % 3] = $strat[$i];
    return $puzzle;
}

function print_puzzle($puzzle) {
    for($i = 0; $i <= 2; $i++) {
        for ($j = 0; $j <= 2; $j++) {
            echo $puzzle[$i][$j]
                ?: iconv('cp437', 'utf8', chr(219));
            echo ' ';
        }
        echo "\n";
    }
}

function line_puzzle($puzzle, $line) {
    $s = '';
    for ($j = 0; $j <= 2; $j++) {
        $s .= $puzzle[$line-1][$j] ?: iconv('cp437', 'utf8', chr(219));
        $s .= ' ';
    }
    return $s;
}

function print_all($p1, $p2, $p3, $floor) {
    for($i = 0; $i <= 2; $i++)
        echo '                 ' . line_puzzle($p2, $i + 1) . "\n";
    echo "                   v            \n";
    for($i = 0; $i <= 2; $i++) {
        for ($j = 0; $j <= 2; $j++) {
            if($j === 0) {
                echo line_puzzle($p1, $i + 1);
                echo $i === 1 ? '> ' : '  ';
            }
            echo !empty($floor[$i][$j])
                ? str_pad(str_replace('"', '', json_encode($floor[$i][$j])), 7, ' ', STR_PAD_BOTH)
                : str_pad(iconv('cp437', 'utf8', chr(219)), 9, ' ', STR_PAD_BOTH);
            echo ' ';
            if($j === 2) {
                echo $i === 1 ? '< ' : '  ';
                echo line_puzzle($p3, $i + 1);
            }
        }
        echo "\n";
    }
    echo "                   ^            \n";
    echo "                 Entry          \n";
}

function rotate90($matrix, $times = 1, $clockwise = true) {
    for($t = 1; $t <= $times; $t++)
        $matrix = $clockwise
            ? call_user_func_array(
                'array_map',
                array(-1 => null) + array_reverse($matrix))
            : call_user_func_array(
                'array_map',
                array(-1 => null) + array_map('array_reverse', $matrix));
    return $matrix;
}

function populate_floor($p1, $p2, $p3) {
    $floor = [
        [[],[],[]],
        [[],[],[]],
        [[],[],[]],
    ];
    for($p = 1; $p <= 3; $p++)
        for ($i = 0; $i <= 2; $i++)
            for ($j = 0; $j <= 2; $j++)
                if (${'p'.$p}[$i][$j])
                    $floor[$i][$j][] = ${'p'.$p}[$i][$j];
    return $floor;
}

function has_colision($floor) {
    for($i = 0; $i <= 2; $i++)
        for ($j = 0; $j <= 2; $j++)
            if(@max(array_count_values($floor[$i][$j])) > 1)
                return 1;
    return 0;
}

$collisions = 0;
for($i = 1; $i <= RUNS; $i++) {

    for($p = 1; $p <= 3; $p++)
        ${'p'.$p} = gen_puzzle(STRAT[$p-1]);

/*
    //Common puzzle example
    $p1 = [
        [0, 1, 0],
        [2, 0, 3],
        [0, 4, 0],
    ];
    $p2 = [
        [0, 0, 1],
        [2, 3, 0],
        [0, 4, 0],
    ];
    $p3 = [
        [1, 0, 2],
        [0, 0, 0],
        [3, 0, 4],
    ];
*/

    $p1 = rotate90($p1, 1, false);
    $p3 = rotate90($p3);
    $floor = populate_floor($p1, $p2, $p3);
    $collisions += has_colision($floor);

    echo "Iteration: $i\n";
    echo "----------------------------------------------------------------------------\n";
    print_all($p1, $p2, $p3, $floor);
    echo "----------------------------------------------------------------------------\n";

}

echo sprintf("Strat: %s, Runs: %d, Collisions: %d, Percentage: %.2f%%\n",
    str_replace('"', '', json_encode(STRAT)), RUNS, $collisions, $collisions*100/RUNS);