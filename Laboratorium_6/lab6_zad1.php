<?php
function isprime($number){
    $i = 2;
    while($i <= sqrt($number)){
        if($number % $i == 0)
            return false;
        $i++;
    }
    return true;
}
function print_primes($starting_number, $max)
{
    if(is_numeric($starting_number) && is_numeric($max)) {
        if ($starting_number > 0 && $max > 0) {
            echo "$starting_number, $max\n";
            for ($i = $starting_number; $i <= $max; $i++) {
                if (isprime($i))
                    echo "$i ";
            }
        }
        else
            echo "Start and stop must be positive number! Given $starting_number $max";
    }
    else
        echo "Start and stop must be numeric!";
}
print_primes(-5,10);
?>