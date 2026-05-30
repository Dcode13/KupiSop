<?php

if (! function_exists('rupiah')) {
    /**
     * Format angka menjadi mata uang Rupiah, mis. 25000 -> "Rp 25.000".
     */
    function rupiah($value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }
}
