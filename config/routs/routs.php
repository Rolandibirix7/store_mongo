<?php
class Rutas {
    public function pagina($ruta) {
        return match($ruta) {
            'producto'    => __DIR__ . '/../../pages/producto.php',
            'categoria'   => __DIR__ . '/../../pages/categoria.php',
            'login'       => __DIR__ . '/../../login.php',
            'registro'    => __DIR__ . '/../../registro.php',
            'logout'      => __DIR__ . '/../../logout.php',

            'carrito'      => __DIR__ . '/../../pages/carrito.php',
            'checkout'     => __DIR__ . '/../../pages/checkout.php',
            'mis_pedidos'  => __DIR__ . '/../../pages/mis_pedidos.php',
            'mis_tarjetas' => __DIR__ . '/../../pages/mis_tarjetas.php',

            'dashboard'               => __DIR__ . '/../../admin/dashboard.php',
            'agregar_producto'        => __DIR__ . '/../../admin/agregar_producto.php',
            'agregar_categoria'       => __DIR__ . '/../../admin/agregar_categoria.php',
            'categorias'              => __DIR__ . '/../../admin/categorias.php',
            'registrar_administrador' => __DIR__ . '/../../admin/registrar_administrador.php',
            'admin_pedidos'           => __DIR__ . '/../../admin/admin_pedidos.php',
            'repartidor_panel'        => __DIR__ . '/../../pages/repartidor_panel.php',

            default => null
        };
    }
}
