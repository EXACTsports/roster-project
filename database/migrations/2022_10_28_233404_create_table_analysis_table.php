<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_analysis', function (Blueprint $table) {
            $table->id();
            $table->integer("freshman")->default(0);
            $table->integer("sophomores")->default(0);
            $table->integer("juniors")->default(0);
            $table->integer("seniors")->default(0);
            $table->float("avg_height")->default(0.0);
            $table->json("number_by_state");
            $table->json("number_by_position");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_analysis');
    }
};
