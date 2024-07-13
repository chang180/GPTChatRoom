<?php

namespace Tests\Feature\Jetstream;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Contracts\Auth\Authenticatable;
use Tests\TestCase;

class BrowserSessionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_other_browser_sessions_can_be_logged_out(): void
{
    // 创建一个用户，并设置密码为 'password'
    /** @var Authenticatable $user */
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    // 确保 actingAs 方法传递的是单个用户实例
    $this->actingAs($user);

    // 发送删除请求以登出其他浏览器会话
    $response = $this->delete('/user/other-browser-sessions', [
        'password' => 'password',
    ]);

    // 断言没有错误
    $response->assertSessionHasNoErrors();
}
}
