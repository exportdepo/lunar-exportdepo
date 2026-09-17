<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Lunar\Admin\Models\Staff;
use Illuminate\Support\Facades\Auth;

class CheckStaffCommand extends Command
{
    protected $signature = 'check:staff';

    protected $description = 'Debug: admin@exportdepo.test staff record and auth state';

    public function handle(): int
    {
        $this->newLine();
        $this->info('--- staff ---');
        $staff = Staff::where('email', 'admin@exportdepo.test')->first();

        $this->line('staff_id      = ' . ($staff?->id ?? 'null'));
        $this->line('email         = ' . ($staff?->email ?? 'null'));
        $this->line('hasAdminRole  = ' . ($staff?->hasRole('admin') ? 1 : 0));
        $this->line('password_raw  = ' . ($staff?->getRawOriginal('password') ?? 'null'));
        $this->line('needsRehash   = ' . (Hash::needsRehash($staff?->password ?? '') ? 1 : 0));
        $this->line('check(password) = ' . (Hash::check('password', $staff?->password ?? '') ? 1 : 0));

        $this->newLine();
        $this->info('--- guard(staff) ---');
        $guardUser = Auth::guard('staff')->user();
        $this->line('guard-staff-user-id = ' . ($guardUser?->id ?? 'null'));

        // Filament panel provider / manager ile ilişkili permuted role check
        $this->newLine();
        $this->info('--- panel roles / permissions ---');
        if ($staff) {
            $this->line('role_list = ' . implode(', ', $staff->getRoleNames()->toArray() ?: ['(none)']));
        }

        return Command::SUCCESS;
    }
}
