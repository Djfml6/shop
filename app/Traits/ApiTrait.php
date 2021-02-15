<?php 
namespace App\Traits;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use App\Http\CodeResponse;


// 接口返回格式
trait ApiTrait
{

    protected function codeReturn(array $codeResponse, $data = '', $info = '')
    {
        list($code, $message) = $codeResponse;
        $res = ['code' => $code, 'message' => $info ?: $message, 'data' => $data];
        return response()->json($res);
    }


    protected function success($data = '')
    {
        return $this->codeReturn(CodeResponse::SUCESS, $data);
    }

    protected function fail(array $codeResponse = CodeResponse::FAIL, $info = '')
    {
        return $this->codeReturn($codeResponse, '', $info);
    }



}