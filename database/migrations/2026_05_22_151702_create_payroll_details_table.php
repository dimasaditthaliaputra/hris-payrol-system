<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            // Komponen pendapatan (snapshot saat generate)
            $table->decimal('basic_salary', 15, 2)->default(0);           // Gaji Pokok
            $table->decimal('total_allowance', 15, 2)->default(0);        // Total Tunjangan
            $table->decimal('overtime_pay', 15, 2)->default(0);           // Uang Lembur
            $table->decimal('incentive', 15, 2)->default(0);              // Insentif Bulanan
            $table->decimal('thr', 15, 2)->default(0);                    // THR (jika ada di periode ini)

            // Komponen potongan (snapshot saat generate)
            $table->decimal('total_deduction', 15, 2)->default(0);        // Total Potongan Manual
            $table->decimal('bpjs_kesehatan', 15, 2)->default(0);         // BPJS Kesehatan
            $table->decimal('bpjs_ketenagakerjaan', 15, 2)->default(0);   // BPJS Ketenagakerjaan
            $table->decimal('pph21', 15, 2)->default(0);                  // PPh 21
            $table->decimal('cash_advance_installment', 15, 2)->default(0); // Cicilan Kasbon

            // Hasil akhir
            $table->decimal('gross_salary', 15, 2)->default(0);           // Total Pendapatan Kotor
            $table->decimal('total_cuts', 15, 2)->default(0);             // Total Potongan
            $table->decimal('net_salary', 15, 2)->default(0);             // Gaji Bersih

            // Metadata pendukung
            $table->integer('working_days')->default(0);      // Hari absen hadir
            $table->decimal('overtime_hours', 8, 2)->default(0); // Total jam lembur

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['payroll_id', 'employee_id']); // Satu karyawan satu record per payroll
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};
