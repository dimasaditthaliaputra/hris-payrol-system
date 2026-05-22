<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom role_id dan kolom tambahan ke tabel users.
     * CATATAN: Migration ini harus dijalankan SETELAH create_roles_table.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Foreign key ke tabel roles (nullable = user bisa belum punya role saat pertama dibuat)
            $table->foreignId('role_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('roles')
                  ->nullOnDelete();

            // Kolom tambahan untuk profil user
            $table->string('phone', 20)->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('phone');
            $table->softDeletes();  // Soft delete untuk master data users
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'phone', 'is_active', 'deleted_at']);
        });
    }
};
