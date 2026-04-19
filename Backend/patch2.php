<?php
DB::statement("ALTER TABLE pagos MODIFY COLUMN metodo VARCHAR(50) DEFAULT 'efectivo'");
echo "Esquema pagos alterado con exito.\n";
