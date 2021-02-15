<?php

namespace App\Listeners;

use App\Event\UserRegister;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\UserService;

class AddIntegral implements ShouldQueue
{
    
    /**
     * 处理用户登录事件
     */
    public function handleUserLogin($event)
    {

        $this->addIntegral($event);
    }

    public function handleUserRegister($event)
    {
        $this->addIntegral($event);
    }

    public function addIntegral($event)
    {
        $user = $event->getUser();
        $type = $event->getType();
        // 注册送积分
        $user_service = new UserService();
        $user_service->giveIntegral($user, $type);
    }



    public function subscribe($events)
    {
        $events->listen(
            'App\Event\UserLogin',
            'App\Listeners\AddIntegral@handleUserLogin'
        );

        $events->listen(
            'App\Event\UserRegister',
            'App\Listeners\AddIntegral@handleUserRegister'
        );
    }
}
