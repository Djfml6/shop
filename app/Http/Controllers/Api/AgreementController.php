<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AgreementService;
use Illuminate\Http\Request;
use App\Models\Agreement;

class AgreementController extends Controller
{
    public function show(Agreement $agreements){

        return $this->success($agreements);
    }
}
