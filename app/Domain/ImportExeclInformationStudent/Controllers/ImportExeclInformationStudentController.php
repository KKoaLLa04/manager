<?php
namespace App\Domain\ImportExeclInformationStudent\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportExeclInformationStudentController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }
    public function index(Request $request)
    {
        $file = $request->file('file');
        $data = Excel::toArray([], $file)[0];
    }
}
