<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sn_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('sys_id')->unique();
            $table->string('record_sys_id');
            $table->string('table_name');
            $table->string('file_name');
            $table->string('content_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('storage_path');
            $table->timestamps();

            $table->index('record_sys_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sn_attachments');
    }
};
