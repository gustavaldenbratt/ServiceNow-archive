<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sn_connections', function (Blueprint $table) {
            $table->id();
            $table->string('instance_url')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sn_connections');
    }
};
