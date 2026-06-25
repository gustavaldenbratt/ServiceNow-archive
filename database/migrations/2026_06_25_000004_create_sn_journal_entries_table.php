<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sn_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('sys_id')->unique();
            $table->string('record_sys_id');
            $table->string('table_name');
            $table->string('element');
            $table->text('value')->nullable();
            $table->string('sys_created_by')->nullable();
            $table->timestamp('sys_created_at')->nullable();
            $table->timestamps();

            $table->index(['record_sys_id', 'sys_created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sn_journal_entries');
    }
};
