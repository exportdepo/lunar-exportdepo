<?php

use App\Support\CustomerEmail;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $users = DB::table('users')->select('id', 'email')->orderBy('id')->get();
            $seen = [];
            $changes = [];

            // Check the entire set before writing anything. Never merge accounts.
            foreach ($users as $user) {
                $email = CustomerEmail::normalize($user->email);
                if (isset($seen[$email])) {
                    throw new RuntimeException(
                        "Customer email normalization collision: user IDs {$seen[$email]} and {$user->id}. Resolve manually before retrying."
                    );
                }
                $seen[$email] = $user->id;
                if ($email !== $user->email) {
                    $changes[$user->id] = $email;
                }
            }

            foreach ($changes as $id => $email) {
                DB::table('users')->where('id', $id)->update(['email' => $email]);
            }
        });
    }

    public function down(): void
    {
        // Original casing/whitespace cannot be reconstructed safely.
        // Keep normalized addresses; passwords and account IDs are unchanged.
    }
};
