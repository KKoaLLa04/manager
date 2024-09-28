<?php
namespace App\Domain\SchoolYear\Controllers;

use App\Domain\SchoolYear\Models\SchoolYear;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Domain\SchoolYear\Repository\SchoolYearAddRepository;
use App\Domain\SchoolYear\Repository\SchoolYearEditRepository;
use App\Domain\SchoolYear\Repository\SchoolYearIndexRepository;
use App\Domain\SchoolYear\Repository\SchoolYearDetailRepository;
use App\Domain\SchoolYear\Repository\SchoolYearDeleteRepository;
use App\Domain\SchoolYear\Requests\SchoolYearAddRequest;
use App\Domain\SchoolYear\Requests\SchoolYearEditRequest;

class SchoolYearController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }


    public function index()
    {

        $SchoolYearIndexRepository = new SchoolYearIndexRepository();

        $check = $SchoolYearIndexRepository->handle();

        if($check){
            return response()->json(['status' => 'success', 'data' => $check->toArray()]);
        }else{
            return response()->json(['status' => 'error', 'data' => []]);
        }

    }


    public function detail(int $id)
    {

        $SchoolYearDetailRepository = new SchoolYearDetailRepository();

        $check = $SchoolYearDetailRepository->handle($id);

        if($check){
            return response()->json(['status' => 'success', 'data' => $check->toArray()]);
        }else{
            return response()->json(['status' => 'error', 'data' => []]);
        }

    }


    public function delete(int $id)
    {

        $SchoolYearDeleteRepository = new SchoolYearDeleteRepository();

        $user_id = 1;

        $check = $SchoolYearDeleteRepository->handle($id, $user_id);

        if($check){
            $data = SchoolYear::find($id);
            return response()->json(['message' => 'Xóa năm học '.$data->name.' thành công', 'status' => 'success']);
        }else{
            $data = SchoolYear::find($id);
            return response()->json(['message' => 'Xóa năm học '.$data->name.' thất bại', 'status' => 'error']);
        }

    }



    public function add(SchoolYearAddRequest $request)
    {

        $SchoolYearAddRepository = new SchoolYearAddRepository();
        $user_id = 1;

        $check = $SchoolYearAddRepository->handle($user_id, $request);

        if($check){
            $data = SchoolYear::all();
            $data = $data->last();
            return response()->json(['message' => 'Thêm năm học '.$data->name.' thành công', 'status' => 'success', 'data' => $data]);
            // return 1;
        }else{
            $data = SchoolYear::all();
            $data = $data->last();
            return response()->json(['status' => 'error', 'data' => [], 'message' => 'Thêm năm học '.$data->name.' thất bại']);
            // return 0;
        }

    }

    public function edit(int $id, SchoolYearEditRequest $request)
    {

        $SchoolYearEditRepository = new SchoolYearEditRepository();
        $user_id = 1;
        $check = $SchoolYearEditRepository->handle($id, $user_id, $request);

        if($check){
            $data = SchoolYear::find($id);
            return response()->json(['message' => 'Sửa năm học '.$data->name.' thành công', 'status' => 'success', 'data' => $data]);
        }else{
            $data = SchoolYear::find($id);
            return response()->json(['message' => 'Sửa năm học '.$data->name.' thất bại', 'status' => 'error', 'data' => []]);
            // return 0;
        }

    }




}
