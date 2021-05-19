<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Area;

class ReservationController extends Controller
{
    public function getReservations()
    {
        $array = ['error' => ''];
        $daysHelper = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

        // allowed areas for reservation
        $areas = Area::where('allowed', 1)->get();

        foreach ($areas as $area) {
            $dayList = explode(',', $area['days']);

            $dayGroups = [];

            // Adding the first day
            $lastDay = intval(current($dayList));
            $dayGroups[] = $daysHelper[$lastDay];
            array_shift($dayList);

            // Adding relevant days

            // Adding the last day
            $dayGroups[] = $daysHelper[end($dayList)];

            echo 'Area: ' . $area['title'] . "\n";
            print_r($dayGroups);
            echo "\n-------------";
        }

        $array['list'] = $areas;

        return $array;
    }
}
