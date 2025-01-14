<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user'); // Relación con usuarios
            $table->string('action', 255); // Acción realizada por el usuario
            $table->longText('description')->nullable(); // Detalle o contexto de la acción
            $table->string('ip_address', 45)->nullable(); // IP del usuario
            $table->text('user_agent')->nullable(); // Información del dispositivo/navegador
            $table->string('referrer', 255)->nullable(); // Página de referencia (opcional)
            $table->enum('severity', ['success', 'error', 'info', 'debug', 'warning', 'critical', 'notice', 'alert', 'emergency', 'trace']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
