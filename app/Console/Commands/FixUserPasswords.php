<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class FixUserPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:user-passwords {--default-password=password : 預設密碼}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修復用戶密碼雜湊，將非 Bcrypt 密碼更新為 Bcrypt';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $defaultPassword = $this->option('default-password');
        
        $this->info('開始檢查用戶密碼雜湊...');
        
        $users = User::all();
        $fixedCount = 0;
        
        foreach ($users as $user) {
            // 檢查密碼是否使用 Bcrypt 算法
            if (!str_starts_with($user->password, '$2y$')) {
                $this->line("修復用戶: {$user->email}");
                
                // 更新密碼為 Bcrypt 雜湊
                $user->password = Hash::make($defaultPassword);
                $user->save();
                
                $fixedCount++;
            }
        }
        
        if ($fixedCount > 0) {
            $this->info("✅ 成功修復 {$fixedCount} 個用戶的密碼雜湊");
            $this->warn("⚠️  這些用戶的密碼已重設為: {$defaultPassword}");
            $this->warn("⚠️  請通知用戶使用新密碼登入，或使用忘記密碼功能重設密碼");
        } else {
            $this->info("✅ 所有用戶的密碼雜湊都是正確的 Bcrypt 格式");
        }
        
        return Command::SUCCESS;
    }
}
