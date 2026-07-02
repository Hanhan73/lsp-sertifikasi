<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE other_receivables MODIFY COLUMN status ENUM('outstanding', 'cicilan', 'lunas') NOT NULL DEFAULT 'outstanding'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE other_receivables MODIFY COLUMN status ENUM('outstanding', 'lunas') NOT NULL DEFAULT 'outstanding'");
    }
};