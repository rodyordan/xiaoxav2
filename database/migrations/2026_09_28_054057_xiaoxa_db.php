<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol', function (Blueprint $table) {
            $table->id('id_rol');
            $table->string('nombre_rol', 50);
        });

        Schema::create('tipo_documento', function (Blueprint $table) {
            $table->id('id_tipo_documento');
            $table->string('nombre_tipo_documento', 50);
        });

        Schema::create('departamento', function (Blueprint $table) {
            $table->id('id_departamento');
            $table->string('nombre_departamento', 100);
        });

        Schema::create('provincia', function (Blueprint $table) {
            $table->id('id_provincia');
            $table->string('nombre_provincia', 100);
            $table->unsignedBigInteger('id_departamento');
            
            $table->foreign('id_departamento')
                  ->references('id_departamento')
                  ->on('departamento');
        });

        Schema::create('distrito', function (Blueprint $table) {
            $table->id('id_distrito');
            $table->string('nombre_distrito', 100);
            $table->unsignedBigInteger('id_provincia');
            
            $table->foreign('id_provincia')
                  ->references('id_provincia')
                  ->on('provincia');
        });

        Schema::create('usuario', function (Blueprint $table) {
            $table->id('id_usuario');

            $table->string('nombres', 50);
            $table->string('apellidos', 50);

            $table->string('correo', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('contrasena', 255);

            $table->string('numero_documento', 20)->nullable();
            $table->string('telefono', 20)->nullable();

            $table->unsignedBigInteger('id_tipo_documento')->nullable();
            $table->unsignedBigInteger('id_rol')->default(2);

            $table->rememberToken();
            $table->timestamps();

            $table->foreign('id_tipo_documento')
                  ->references('id_tipo_documento')->on('tipo_documento');

            $table->foreign('id_rol')
                  ->references('id_rol')->on('rol');
        });

        Schema::create('categoria', function (Blueprint $table) {
            $table->id('id_categoria');
            $table->string('nombre_categoria', 50);
            $table->boolean('estado_categoria')->default(1);
            $table->timestamps();
        });

        Schema::create('producto', function (Blueprint $table) {
            $table->id('id_producto');

            $table->string('nombre_producto', 150);
            $table->decimal('precio', 10, 2);
            $table->decimal('precio_oferta', 10, 2)->nullable();

            $table->json('detalles')->nullable();

            $table->string('marca', 50)->nullable();
            $table->boolean('estado_producto')->default(1);

            $table->unsignedBigInteger('id_categoria')->nullable();

            $table->timestamps();

            $table->foreign('id_categoria')->references('id_categoria')->on('categoria');
        });

        Schema::create('producto_variante', function (Blueprint $table) {
            $table->id('id_variante');

            $table->unsignedBigInteger('id_producto');
            $table->string('talla', 50);
            $table->string('color', 50)->nullable();
            $table->string('color_hex', 7)->nullable();
            $table->integer('stock')->default(0);
            $table->string('sku', 50)->unique();

            $table->timestamps();

            $table->foreign('id_producto')->references('id_producto')->on('producto');
        });

        Schema::create('producto_variante_imagen', function (Blueprint $table) {
            $table->id('id_imagen');

            $table->unsignedBigInteger('id_variante');
            $table->string('imagen', 255);
            $table->integer('orden')->default(0);

            $table->timestamps();

            $table->foreign('id_variante')
                  ->references('id_variante')
                  ->on('producto_variante')
                  ->onDelete('cascade');
        });

        Schema::create('cupones', function (Blueprint $table) {
            $table->id('id_cupon');
            $table->string('codigo_cupon', 50)->unique();
            $table->decimal('monto_cupon', 6, 2);
            $table->decimal('monto_compra_minima', 6, 2);
            $table->date('fecha_vencimiento');
            $table->boolean('estado_cupon')->default(1);
        });

        Schema::create('tipo_entrega', function (Blueprint $table) {
            $table->id('id_tipo_entrega');
            $table->string('nombre_tipo_entrega', 100);
        });

        Schema::create('pedido', function (Blueprint $table) {
            $table->id('id_pedido');

            $table->string('numero_pedido', 20)->unique();
            $table->dateTime('fecha_pedido')->useCurrent();

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total_pedido', 10, 2);

            $table->enum('estado_pedido', [
                'Pendiente',
                'En camino',
                'Listo para recoger',
                'Entregado'
            ])->default('Pendiente');

            $table->string('payment_id')->nullable();

            $table->unsignedBigInteger('id_departamento')->nullable();
            $table->unsignedBigInteger('id_provincia')->nullable();
            $table->unsignedBigInteger('id_distrito')->nullable();

            $table->text('direccion_entrega')->nullable();
            $table->string('tiempo_entrega', 100)->nullable();
            $table->string('archivo_adjunto', 255)->nullable();

            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->unsignedBigInteger('id_cupon')->nullable();
            $table->unsignedBigInteger('id_tipo_entrega')->nullable();

            $table->timestamps();

            $table->foreign('id_usuario')
                ->references('id_usuario')->on('usuario');
            $table->foreign('id_cupon')
                ->references('id_cupon')->on('cupones');
            $table->foreign('id_tipo_entrega')
                ->references('id_tipo_entrega')->on('tipo_entrega');
            
            $table->foreign('id_departamento')->references('id_departamento')->on('departamento');
            $table->foreign('id_provincia')->references('id_provincia')->on('provincia');
            $table->foreign('id_distrito')->references('id_distrito')->on('distrito');
        });

        Schema::create('cupones_usados', function (Blueprint $table) {
            $table->id('id_cupon_usado');

            $table->unsignedBigInteger('id_cupon');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_pedido');
            $table->dateTime('usado_en')->useCurrent();

            $table->unique(['id_cupon', 'id_usuario']);

            $table->timestamps();

            $table->foreign('id_cupon')
                  ->references('id_cupon')->on('cupones')
                  ->onDelete('cascade');

            $table->foreign('id_usuario')
                  ->references('id_usuario')->on('usuario')
                  ->onDelete('cascade');

            $table->foreign('id_pedido')
                  ->references('id_pedido')->on('pedido')
                  ->onDelete('cascade');
        });

        Schema::create('detalle_pedido', function (Blueprint $table) {
            $table->id('id_detalle_pedido');

            $table->unsignedBigInteger('id_pedido');
            $table->unsignedBigInteger('id_variante');

            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);

            $table->foreign('id_pedido')->references('id_pedido')->on('pedido')->onDelete('cascade');
            $table->foreign('id_variante')->references('id_variante')->on('producto_variante');
        });

        Schema::create('carrito', function (Blueprint $table) {
            $table->id('id_carrito');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario');
        });

        Schema::create('detalle_carrito', function (Blueprint $table) {
            $table->id('id_detalle_carrito');

            $table->unsignedBigInteger('id_carrito');
            $table->unsignedBigInteger('id_variante');

            $table->integer('cantidad')->default(1);
            $table->timestamps();

            $table->unique(['id_carrito', 'id_variante']);

            $table->foreign('id_carrito')->references('id_carrito')->on('carrito')->onDelete('cascade');
            $table->foreign('id_variante')->references('id_variante')->on('producto_variante');
        });

        Schema::create('favoritos', function (Blueprint $table) {
            $table->id('id_favorito');

            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_producto');

            $table->timestamps();

            $table->unique(['id_usuario', 'id_producto']);

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario');
            $table->foreign('id_producto')->references('id_producto')->on('producto');
        });

        Schema::create('ventas', function (Blueprint $table) {
            $table->id('id_venta');
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('venta_detalles', function (Blueprint $table) {
            $table->id('id_venta_detalle');
            $table->foreignId('id_venta')->constrained('ventas', 'id_venta')->cascadeOnDelete();
            $table->foreignId('id_variante')->constrained('producto_variante', 'id_variante');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id('id_banner');
            $table->string('titulo', 100)->unique();
            $table->string('url_boton', 500)->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('estado')->default(1);
            $table->string('imagen', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('venta_detalles');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('favoritos');
        Schema::dropIfExists('detalle_carrito');
        Schema::dropIfExists('carrito');
        Schema::dropIfExists('detalle_pedido');
        Schema::dropIfExists('cupones_usados');
        Schema::dropIfExists('pedido');
        Schema::dropIfExists('tipo_entrega');
        Schema::dropIfExists('cupones');
        Schema::dropIfExists('producto_variante_imagen');
        Schema::dropIfExists('producto_variante');
        Schema::dropIfExists('producto');
        Schema::dropIfExists('categoria');
        Schema::dropIfExists('usuario');
        Schema::dropIfExists('tipo_documento');
        Schema::dropIfExists('rol');
        Schema::dropIfExists('distrito');
        Schema::dropIfExists('provincia');
        Schema::dropIfExists('departamento');
    }
};