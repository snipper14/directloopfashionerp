<?php

// app/Http/Middleware/SetDbActor.php
namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetDbActor
{
  public function handle($request, Closure $next)
  {
    DB::statement('SET @actor_id = ?', [optional(Auth::user())->id]);

    // $tz = 'Africa/Nairobi';
    // try {
    //   // Preferred: named time zone (requires MySQL time zone tables)
    //   DB::statement('SET time_zone = ?', [$tz]);
    // } catch (\Throwable $e) {
    //   // Fallback: numeric offset (Nairobi is UTC+03:00, no DST)
    //   $offset = Carbon::now($tz)->format('P'); // "+03:00"
    //   DB::statement('SET time_zone = ?', [$offset]);
    // }

    return $next($request);
  }
}
