<?php 
namespace App\Domain\Guardian\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\Guardian\Models\Guardian;

class GuardianRepository{
    public function __construct(){

    }

    public function getGuardian()
{
    return Guardian::where('access_type', AccessTypeEnum::GUARDIAN->value)
    ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
    ->withCount('students')
    ->paginate();


}


    public function addGuardian(array $data){
        return Guardian::create($data);
    }

    public function getOneGuardian(int $id)
{
    $one = Guardian::with('students')
        ->where('access_type', AccessTypeEnum::GUARDIAN->value)
        ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
        ->find($id);


    if (!$one) {
        return response()->json([
            'message' => 'Phụ huynh không tồn tại'
        ], 404); 
    }

    if ($one->status == StatusEnum::UN_ACTIVE->value) {
        return response()->json([
            'message' => 'Phụ huynh đang bị khoá'
        ], 403); 
    }

    return response()->json([
        'data' => $one,
        'message' => 'Lấy thông tin phụ huynh thành công'
    ]);
}

    public function updateGuardian(int $id, array $data){
        $one = Guardian::where('access_type', AccessTypeEnum::GUARDIAN->value)
        ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
        ->find($id);

        if (!$one) {
            return response()->json([
                'message' => 'Phụ huynh không tồn tại'
            ], 404); 
        }
    
        if ($one->is_deleted == DeleteEnum::DELETED->value) {
            return response()->json([
                'message' => 'Phụ huynh đang bị xóa'
            ], 403); 
        }

        $one->fill($data);
        $one->save();
        return $one;
    }

    public function lockGuardian(int $id){
        $one = Guardian::where('access_type', AccessTypeEnum::GUARDIAN->value)
        ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
        ->find($id);

        
        if (!$one) {
            return response()->json([
                'message' => 'Phụ huynh không tồn tại'
            ], 404); 
        }
    
        if ($one->is_deleted == DeleteEnum::DELETED->value) {
            return response()->json([
                'message' => 'Phụ huynh đang bị xóa'
            ], 403); 
        }

        if ($one->status == 1){
            $one->status = 0;
            $one->save();
            return $one;
        }
        return null;
    }

    public function unlockGuardian(int $id){
        $one = Guardian::where('access_type', AccessTypeEnum::GUARDIAN->value)
        ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
        ->find($id);

        if (!$one) {
            return response()->json([
                'message' => 'Phụ huynh không tồn tại'
            ], 404); 
        }
    
        if ($one->is_deleted == DeleteEnum::DELETED->value) {
            return response()->json([
                'message' => 'Phụ huynh đang bị xóa'
            ], 403); 
        }

        if ($one->status == 0){
            $one->status = 1;
            $one->save();
            return $one;
        }
        return null;
    }

    public function changePassword(int $id, array $data){
        $one = Guardian::where('access_type', AccessTypeEnum::GUARDIAN->value)
        ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
        ->find($id);

        if (!$one) {
            return response()->json([
                'message' => 'Phụ huynh không tồn tại'
            ], 404); 
        }
    
        if ($one->is_deleted == DeleteEnum::DELETED->value) {
            return response()->json([
                'message' => 'Phụ huynh đang bị xóa'
            ], 403); 
        }

        $one->fill($data);
        $one->save();
        return $one;
    }

    

}