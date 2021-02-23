<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\CodeResponse;
use App\Exceptions\RequestException;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

config(['auth.defaults.guard' => 'user']);

class RefreshTokenUser extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
         
        try{
                // 检查此次请求中是否带有 token，如果没有则抛出异常。
                $this->checkForToken($request);

                // 检测用户的登录状态，如果正常则通过
                if ($this->auth->parseToken()->authenticate()) {
                    return $next($request);
                }
                throw new RequestException(CodeResponse::LOGIN_INVALID, $e->getMessage());

        }catch (UnauthorizedHttpException $e){
                throw new RequestException(CodeResponse::LOGIN_INVALID, $e->getMessage());
        }catch (TokenExpiredException $exception) {
            // 此处捕获到了 token 过期所抛出的 TokenExpiredException 异常，我们在这里需要做的是刷新该用户的 token 并将它添加到响应头中
                try {
                    // 刷新用户的 token
                    $token = $this->auth->refresh();
                // 使用一次性登录以保证此次请求的成功
                    Auth::guard('user')->onceUsingId($this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray()['sub']);
                } catch (JWTException $exception) {
                // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
                    throw new RequestException(CodeResponse::LOGIN_INVALID, $e->getMessage());
                }
        }catch(TokenInvalidException $e){
            throw new RequestException(CodeResponse::TOKEN_INVALID, 'token失效');
        }catch (JWTException $e)
        {
            throw new RequestException(CodeResponse::TOKEN_INVALID, 'token失效');
        }
 

        // 在响应头中返回新的 token
        return $this->setAuthenticationHeader($next($request), $token);
    }
}
