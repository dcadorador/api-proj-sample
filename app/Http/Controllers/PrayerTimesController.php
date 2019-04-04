<?php

namespace App\Api\V1\Controllers;


use Carbon\Carbon;
use GeniusTS\PrayerTimes\Coordinates;
use GeniusTS\PrayerTimes\Prayer;
use Illuminate\Http\Request;

class PrayerTimesController extends ApiController
{
    public function index(Request $request)
    {
        $longitude = $request->input('longitude');
        $latitude = $request->input('latitude');

        if(!$request->has('longitude') || !$request->has('latitude')) {
            return response()->json(['error' => [
                $this->responseArray(1059,400)
            ]], 400);
        }

        $currentDate = $request->input('date', Carbon::now()->toDateString());
        $currentTime = $request->input('time', Carbon::now()->toTimeString());

        $prayer = new Prayer(new Coordinates($longitude, $latitude));

        // Return an \GeniusTS\PrayerTimes\Times instance
        $times = $prayer->times($currentDate);
        //$times->setTimeZone(0);

        $diff = Carbon::parse($currentTime);

        $prayerTimes = [
            'fajr',
            'sunrise',
            'duhr',
            'asr',
            'maghrib',
            'isha',
        ];

        $arr = [];

        foreach ($prayerTimes as $key => $val) {
            $prayer = $times->{$val}->addHours(3)->format('h:i a');
            $arr[$val] = $prayer;

            $prayerTime = Carbon::parse($prayer);

            $minuteDiff = $diff->diffInMinutes($prayerTime);

            // if current time is greater than prayer time
            // and minute diff is less than or eq to 30
            $result = $diff->greaterThanOrEqualTo($prayerTime) && $minuteDiff <= 30;

            if($result) break;
        }



        // not sure on the response
        return response()->json([
            'prayer' => $result,
            'prayer-time' => $prayer,
            'timings' => $arr
        ]);
    }

    public function customResponse($status)
    {
        return [
            'detail' => 'Longitude and latitude are required',
            'status' => $status
        ];
    }
}