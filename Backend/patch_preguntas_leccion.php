<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (Schema::hasTable('banco_preguntas')) {
    if (!Schema::hasColumn('banco_preguntas', 'leccion_id')) {
        Schema::table('banco_preguntas', function (Blueprint $table) {
            $table->foreignId('leccion_id')->nullable()->constrained('lecciones_materia')->onDelete('set null');
        });
        echo "Column 'leccion_id' added to 'banco_preguntas' successfully.\n";
    } else {
        echo "Column 'leccion_id' already exists.\n";
    }
} else {
    echo "Table 'banco_preguntas' does not exist.\n";
}
