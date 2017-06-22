<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Services\Api\V1\Api500EasyPayService;

class Api500EasyPayController extends BaseController
{
    protected $payService;

    public function __construct(Api500EasyPayService $Api500EasyPayService)
    {
        $this->payService = $Api500EasyPayService;
    }

    public function index() 
    {
        return 'test';
    }

    public function store(Request $request)
    {   
        $this->payService->send($request->all());              
    }   
}
