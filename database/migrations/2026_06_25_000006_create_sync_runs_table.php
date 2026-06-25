<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_runs', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->string('status')->default('pending');
            $table->unsignedInteger('records_processed')->default(0);
            $table->unsignedInteger('total')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['table_name', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_runs');
    }
};
