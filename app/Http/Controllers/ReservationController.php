<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Unit;

use App\Models\Area;
use App\Models\Reservation;
use App\Models\AreaDisabledDay;

class ReservationController extends Controller
{
    public function getReservations()
    {
        $array = ['error' => '', 'list' => []];
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
            foreach ($dayList as $day) {
                if (intval($day) != $lastDay + 1) {
                    $dayGroups[] = $daysHelper[$lastDay];
                    $dayGroups[] = $daysHelper[$day];
                }

                $lastDay = intval($day);
            }

            // Adding the last day
            $dayGroups[] = $daysHelper[end($dayList)];

            // Joining dates (day1 - day2)
            $dates = '';
            $close = 0;

            foreach ($dayGroups as $group) {
                if ($close === 0) {
                    $dates .= $group;
                } else {
                    $dates .= '-' . $group . ',';
                }

                $close = 1 - $close;
            }

            $dates = explode(',', $dates);
            array_pop($dates);

            // adding time
            $start = date('H:i', strtotime($area['start_time']));
            $end = date('H:i', strtotime($area['end_time']));

            foreach ($dates as $dKey => $dValue) {
                $dates[$dKey] .= ' ' . $start . ' às ' . $end;
            }

            $array['list'][] = [
                'id' => $area['id'],
                'cover' => asset('storage/' . $area['cover']),
                'title' => $area['title'],
                'dates' => $dates
            ];
        }
        return $array;
    }

    public function setReservation($id, Request $request)
    {
        $array = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i:s',
            'property' => 'required'
        ]);

        if (! $validator->fails()) {
            $date = $request->input('date');
            $time = $request->input('time');
            $property = $request->input('property');

            $unit = Unit::find($property);
            $area = Area::find($id);

            if ($unit && $area) {
                $can = true;

                $weekDay = date('w', strtotime($date));

                // Verificar se está dentro da disponibilidade padrão
                $allowedDays = explode(',', $area['days']);

                if (! in_array($weekDay, $allowedDays)) {
                    $can = false;
                } else {
                    $start = strtotime($area['start_time']);
                    $end = strtotime('-1 hour', strtotime($area['end_time']));
                    $revtime = strtotime($time);

                    if ($revtime < $start || $revtime > $end) {
                        $can = false;
                    }
                }

                // verificar se está dentro dos disabled days
                $existingDisabledDays = AreaDisabledDay::where('id_area', $id)
                ->where('day', $date)
                ->count();

                if ($existingDisabledDays > 0) {
                    $can = false;
                }

                // verificar se não existe outra reserva no mesmo dia / hora
                $existingReservations = Reservation::where('id_area', $id)
                ->where('reservation_date', $date . ' ' . $time)
                ->count();

                if ($existingReservations > 0) {
                    $can = false;
                }

                if ($can) {

                    $newReservation = new Reservation();
                    $newReservation->id_unit = $property;
                    $newReservation->id_area = $id;
                    $newReservation->reservation_date = $date . ' ' . $time;
                    $newReservation->save();

                } else {
                    $array['error'] = 'Reserva não permitida neste dia / horário';
                    return $array;
                }
            } else {
                $array['error'] = 'Dados incorretos';
                return $array;
            }

        } else {
            $array['error'] = $validator->errors()->first();
            return $array;
        }

        return $array;
    }

    public function getDisabledDates($id)
    {
        $array = ['error' => '', 'list' => []];

        $area = Area::find($id);

        if ($area) {

            // default disabled days
            $disabledDays = AreaDisabledDay::where('id_area', $id)->get();
            foreach ($disabledDays as $disabledDay) {
                $array['list'][] = $disabledDay['day'];
            }

            // get disabled days through allowed days
            $allowedDays = explode(',', $area['days']);
            $offDays = [];

            for ($i = 0; $i <= 6; $i++) {
                if (! in_array($i, $allowedDays)) {
                    $offDays[] = $i;
                }
            }

            // list disabled days to 3 months ahead
            $start = time();
            $end = strtotime('+3 months');

            for ($current = $start; $current < $end; $current = strtotime('+1 day', $current)) {
                $wd = date('w', $current);

                if (in_array($wd, $offDays)) {
                    $array['list'][] = date('Y-m-d', $current);
                }
            }

        } else {
            $array['error'] = 'Área inexistente';
            return $array;
        }


        return $array;
    }

}
