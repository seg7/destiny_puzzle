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
                ? str_pad(str_replace('"', '', json_encode($floor[$i][$j])), 7, ' ', STR_PAD_BOTH)
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
    for($p = 1; $p <= 3; $p++) {
        for ($i = 0; $i <= 2; $i++)
            for ($j = 0; $j <= 2; $j++)
                if (${'p'.$p}[$i][$j])
                    $floor[$i][$j][] = ${'p'.$p}[$i][$j];
        $floor = rotate90($floor, 3);
    }
    $floor = rotate90($floor,2);
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

    echo "Iteration: $i\n";
    echo "--------------------------------------------------\n";

    for($p = 1; $p <= 3; $p++) {
        echo "P$p:\n";
        print_puzzle(${'p'.$p});
    }

    $floor = populate_floor($p1, $p2, $p3);

    echo "Floor:\n";
    print_floor($floor);
    echo "--------------------------------------------------\n";

    count_colisions();
}

echo sprintf("Runs: %d, Collisions: %d, Percentage: %.2f%%\n", RUNS, $collisions, $collisions*100/RUNS);