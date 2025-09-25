<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

abstract class Controller
{
    public $paginate_rows = 10;
    public $now;

    public function __construct()
    {
        $this->now = Carbon::now('Asia/Taipei');
    }
}
