<?php
// Цикл for

echo "Cicle for\n\n";

$a = 0;
$b = 0;

for ($i = 0; $i <= 5; $i++) {
   $a += 10;
   $b += 5;
   echo "Step $i. a = $a, b = $b\n";

}

echo "End of the loop: a = $a, b = $b\n";
echo "\n";

// Цикл while

echo "Cicle while\n\n";

$a1 = 0;
$b1 = 0;
$i = 0;

while ($i <= 5) {
   $a1 += 10;
   $b1 += 5;
   echo "Step $i. a1 = $a1, b1 = $b1\n";
   $i++;
}

echo "End of the loop: a1 = $a1, b1 = $b1\n";
echo "\n";

// Цикл do-while

echo "Cicle do-while\n\n";

$a2 = 0;
$b2 = 0;
$i = 0;

do {
   $a2 += 10;
   $b2 += 5;
   echo "Step $i. a2 = $a2, b2 = $b2\n";
   $i++;
} while ($i <= 5);

echo "End of the loop: a2 = $a2, b2 = $b2\n";