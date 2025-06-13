<?php
$matriks = 7;

for ($i = 0; $i < $matriks; $i++){
    for($j = 0; $j < $matriks; $j++){
        if($i == $j || $i + $j == $matriks - 1){
            echo "x ";
        }else{
            echo "O ";
        }
    }
    echo"\n";
}
?>