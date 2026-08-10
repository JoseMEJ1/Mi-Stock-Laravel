<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventorySnapshotsTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('branch_id')->nullable();
            $table->string('taken_by')->nullable();
            $table->timestamp('snapshot_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_snapshots');
    }
}
