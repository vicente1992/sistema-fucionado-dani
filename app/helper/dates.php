
<?php

use Carbon\Carbon;


if (!function_exists('count_date')) {
  function count_date($created_at)
  {

    $dt = Carbon::now()->toDateString();
    $days_crea = $created_at->diffInDaysFiltered(function (Carbon $date) {
      return !$date->isSunday();
    }, $dt);

    return $days_crea;
  }
}
