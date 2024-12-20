<?php

namespace App\Domain\Timetable\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Timetable;
use Illuminate\Http\Request;

class TimetableController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function getClassTeacherSubject()
    {

    }

    public function indexConfig()
    {
        $timetables = Timetable::query()->get();
        $timetables = $timetables->groupBy('time');
        $data       = [];
        foreach ($timetables as $key => $timetable) {
            $timetable  = $timetable->unique('period');
            $data[$key] = $timetable->map(function ($item) {
                return [
                    "period"    => $item->period,
                    "from_time" => $item->from_time,
                    "to_time"   => $item->to_time,
                ];
            })->toArray();
        }
        return $this->responseSuccess($data);
    }

    public function editConfig(Request $request)
    {
        $data       = $request->data;
        foreach ($data as $item) {
            $dataUpdate = [
                "from_time" => $item['from_time'],
                "to_time"   => $item['to_time'],
            ];
            Timetable::query()->where('period', $item['period'])
                ->where('time', $item['time'])->update($dataUpdate);
        }

        return $this->responseSuccess($data);
    }
}
