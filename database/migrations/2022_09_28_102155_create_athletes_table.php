<?php

use App\Models\Roster;
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
        Schema::create('athletes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Roster::class); // Do not use foreign key constraints
            $table->string('name');
            $table->string('image_url')->nullable();
            $table->string('position')->nullable();
            $table->string('year')->nullable(); // {Red Shirt} Freshman, Sophomore, Junior, Senior
            $table->string('home_town')->nullable();
            $table->json('extra'); // Add anything else you can gather from the roster as key / value pairs in json
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
        Schema::dropIfExists('athletes');
    }
};
