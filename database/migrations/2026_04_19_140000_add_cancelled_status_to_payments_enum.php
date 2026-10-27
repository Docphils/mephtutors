<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY status ENUM('Pending','Earned','Paid','Cancelled') NOT NULL");
        }
    }

    public function down(): void
    {
        DB::statement("UPDATE payments SET status = 'Pending' WHERE status = 'Cancelled'");

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY status ENUM('Pending','Earned','Paid') NOT NULL");
        }
    }
};
