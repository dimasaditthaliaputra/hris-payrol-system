<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel roles untuk manajemen role pengguna (Super Admin, HRD, Finance, Karyawan).
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();          // e.g. 'super_admin', 'hrd', 'finance', 'karyawan'
            $table->string('display_name');            // e.g. 'Super Admin', 'HRD', 'Finance', 'Karyawan'
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();                     // Soft delete sesuai arsitektur
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
