<?php

namespace App\Domain\Guardian\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\Guardian\Models\Guardian;
use App\Models\Student;
use Exception;

class GuardianOfGuardianRespository
{
    public function __construct() {}

    public function getOneGuardian($id){
        $one = Guardian::with('students')
        ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
        ->find($id);

    
    if (!$one) {
        return [
            'msg' => 'Phụ huynh không tồn tại',
            'data' => null,
        ];
    }

    
    if ($one->status == StatusEnum::UN_ACTIVE->value) {
        return [
            'msg' => 'Phụ huynh đang bị khoá',
            'data' => null,
        ];
    }

    
    $guardianData = [
        'id' => $one->id,
        'fullname' => $one->fullname,
        'email' => $one->email,
        'phone' => $one->phone,
        'gender' => $one->gender,
        'dob' => strtotime($one->dob),
        'career' => $one->career,
        'address' => $one->address,
    ];

    return [
        'data' => $guardianData,
    ];
    }

    public function updateGuardianProfile($data, $id){
        $one = Guardian::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->find($id);

        if (!$one) {
            return response()->json([
                'message' => 'Phụ huynh không tồn tại'
            ], 404);
        }

        if ($one->is_deleted == DeleteEnum::DELETED->value) {
            return response()->json([
                'message' => 'Phụ huynh đã bị khóa'
            ], 403);
        }

        $one->fill($data);
        $one->save();
        return $one;
    }

    
}
