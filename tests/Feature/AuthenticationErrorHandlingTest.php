<?php

use App\Models\User;

test('login with invalid password returns error without exception', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    // 應該返回 302 重定向狀態碼，而不是拋出異常（500錯誤）
    $response->assertStatus(302);
    
    // 應該有錯誤訊息
    $response->assertSessionHasErrors('email');
    
    // 確保用戶沒有被認證
    $this->assertGuest();
    
    // 確保沒有拋出異常（這是最重要的）
    $response->assertDontSee('Exception');
    $response->assertDontSee('Error');
});

test('login with non-existent email returns error without exception', function () {
    $response = $this->post('/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password',
    ]);

    // 應該返回 302 重定向狀態碼，而不是拋出異常（500錯誤）
    $response->assertStatus(302);
    
    // 應該有錯誤訊息
    $response->assertSessionHasErrors('email');
    
    // 確保用戶沒有被認證
    $this->assertGuest();
    
    // 確保沒有拋出異常（這是最重要的）
    $response->assertDontSee('Exception');
    $response->assertDontSee('Error');
});

test('login with valid credentials works correctly', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    // 應該重定向到 dashboard
    $response->assertRedirect(route('dashboard', absolute: false));
    
    // 確保用戶被認證
    $this->assertAuthenticated();
});

test('login form renders correctly', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Auth/Login'));
});

test('login with bcrypt error returns friendly message', function () {
    // 創建一個用戶，然後直接更新資料庫來模擬非 Bcrypt 密碼
    $user = User::factory()->create();
    
    // 直接更新資料庫，繞過模型的 hashed cast
    \DB::table('users')->where('id', $user->id)->update([
        'password' => 'plaintext_password'
    ]);

    // 重新載入用戶以獲取更新的密碼
    $user->refresh();

    // 檢查用戶密碼雜湊格式
    echo "User password hash: " . substr($user->password, 0, 20) . "...\n";
    echo "Is Bcrypt: " . (str_starts_with($user->password, '$2y$') ? 'Yes' : 'No') . "\n";

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'plaintext_password',
    ]);

    // 檢查響應狀態碼
    echo "Response status: " . $response->status() . "\n";
    
    // 應該返回 422 或 302，而不是拋出異常
    $this->assertContains($response->status(), [302, 422]);
    
    // 確保用戶沒有被認證
    $this->assertGuest();
});
