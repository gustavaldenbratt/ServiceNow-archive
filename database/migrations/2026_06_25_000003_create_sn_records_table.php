<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sn_records', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->string('sys_id');
            $table->string('number')->nullable();
            $table->timestamp('sys_created_at')->nullable();
            $table->timestamp('sys_updated_at')->nullable();
            $table->string('state')->nullable();
            $table->string('short_description')->nullable();
            $table->string('assigned_to')->nullable();
            $table->json('data');
            $table->timestamps();

            $table->unique(['table_name', 'sys_id']);
            $table->index(['table_name', 'sys_updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sn_records');
    }
};
