<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sn_table_configs', function (Blueprint $table) {
            $table->id();
            $table->string('table_name')->unique();
            $table->date('fetch_since')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_synced_cursor')->nullable();
            $table->string('last_run_status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sn_table_configs');
    }
};
