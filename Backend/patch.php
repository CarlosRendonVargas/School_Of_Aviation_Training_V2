<?php
DB::statement("ALTER TABLE facturas MODIFY COLUMN estado ENUM('pendiente', 'pagada', 'vencida', 'anulada', 'parcial') DEFAULT 'pendiente'");
echo "Esquema facturas alterado con exito.\n";
