<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialventasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historialventas', function (Blueprint $table) {
            $table->bigIncrements('codh_ventas');

            $table->string('cod_empleado_fk', 2);
            $table->unsignedbigInteger('cod_venta_fk');

            $table->string('operacion');
            $table->timestamp('fecha_operacion');

            $table->foreign('cod_empleado_fk')
                  ->references('cod_empleado')
                  ->on('empleados')
                  ->onDelete('cascade'); 

            $table->foreign('cod_venta_fk')
                  ->references('cod_venta')
                  ->on('ventas')
                  ->onDelete('cascade');

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
        Schema::dropIfExists('historialventas');
    }
}
