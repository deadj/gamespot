<?php

use App\Console\Commands\OrderDeliveryRepair;
use Illuminate\Support\Facades\Schedule;

Schedule::command(OrderDeliveryRepair::class)->everyTenMinutes();