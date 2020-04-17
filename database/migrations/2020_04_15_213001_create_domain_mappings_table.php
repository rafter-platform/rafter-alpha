<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomainMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domain_mappings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('domain');
            $table->string('status')->default('inactive');
            $table->unsignedBigInteger('environment_id');

            $table->foreign('environment_id')
                ->references('id')->on('environments')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('domain_mappings');
    }
}
