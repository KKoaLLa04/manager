<?php
namespace App\Domain\Class\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class ClassController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }
    public function index()
    {
        dd(123);
        // TODO: Implement index method.
    }
}
