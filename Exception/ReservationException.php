<?php

namespace App\Exception;

use Exception;
use Throwable;

class ReservationException extends Exception
{
    
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        // Appel du constructeur de la classe parente (Exception)
        parent::__construct($message, $code, $previous);
    }


}
