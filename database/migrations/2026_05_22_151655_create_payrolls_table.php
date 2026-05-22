<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('month');          // 1-12
            $table->year('year');
            $table->enum('status', ['draft', 'locked'])->default('draft');
            $table->decimal('total_expenditure', 18, 2)->default(0);
            $table->unsignedInteger('employee_count')->default(0);
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['month', 'year']); // Tidak boleh duplikat periode
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
