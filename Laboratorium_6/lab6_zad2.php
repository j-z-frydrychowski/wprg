<?php
function numbers($given_number)
{
    if(is_numeric($given_number)) {
        $given_number = abs($given_number);
        while ($given_number > 0) {
            $sum += $given_number % 10;
            $given_number /= 10;
        }
        return $sum;
    }
    else{
        return "Parameter must be numeric!";
    }
}
echo (numbers("xd"));
?>
