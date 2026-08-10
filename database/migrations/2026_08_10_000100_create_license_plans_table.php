<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Crear la colección/tablas sin crear índices únicos aquí para evitar duplicados
        Schema::create('license_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // NO crear unique() aquí: se gestiona de forma idempotente más abajo
            $table->string('code');
            $table->text('description')->nullable();
            $table->integer('max_users')->default(1);
            $table->integer('max_branches')->default(1);
            $table->decimal('price_monthly', 12, 2)->default(0);
            $table->decimal('price_semester', 12, 2)->default(0);
            $table->decimal('price_annual', 12, 2)->default(0);
            $table->json('features')->nullable();
            $table->json('modules')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            // eliminada la llamada a sparse_and_unique para evitar doble creación de índices
        });

        // Crear el índice único de forma idempotente (sólo si no existe).
        try {
            // Intentar usar la conexión mongodb si está configurada
            $connection = DB::connection('mongodb');
            $collection = $connection->getCollection('license_plans');

            $indexExists = false;
            // listIndexes devuelve un iterable de objetos de índice
            foreach ($collection->listIndexes() as $index) {
                if (method_exists($index, 'getName')) {
                    $name = $index->getName();
                } elseif (is_array($index) && isset($index['name'])) {
                    $name = $index['name'];
                } else {
                    $name = null;
                }

                if ($name === 'code_1' || $name === 'unique_code_index') {
                    $indexExists = true;
                    break;
                }
            }

            if (! $indexExists) {
                // Crear índice único con nombre explícito para evitar futuros conflictos
                $collection->createIndex(['code' => 1], ['unique' => true, 'name' => 'code_1']);
            }
        } catch (\Exception $e) {
            // Si no hay conexión mongodb o falla, no detener la migración; registrar el error opcionalmente
            // Esto permite que la migración siga siendo compatible con otros drivers o entornos de prueba.
            // 
            // En entornos de producción conviene que la creación del índice sea verificada y que
            // si hay un error la migración falle para evitar inconsistencias. Ajustar según necesidad.
        }
    }

    public function down(): void
    {
        // Intentar eliminar el índice si existe (idempotente), luego borrar la tabla/colección
        try {
            $connection = DB::connection('mongodb');
            $collection = $connection->getCollection('license_plans');

            foreach ($collection->listIndexes() as $index) {
                $name = null;
                if (method_exists($index, 'getName')) {
                    $name = $index->getName();
                } elseif (is_array($index) && isset($index['name'])) {
                    $name = $index['name'];
                }

                if ($name === 'code_1' || $name === 'unique_code_index') {
                    try {
                        $collection->dropIndex($name);
                    } catch (\Exception $e) {
                        // ignorar errores al eliminar índice
                    }
                }
            }
        } catch (\Exception $e) {
            // ignorar si la conexión/operación no es válida en este entorno
        }

        Schema::dropIfExists('license_plans');
    }
};
