<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\Illuminate\Support\Facades\Schema::dropIfExists('system_settings');
\Illuminate\Support\Facades\DB::table('migrations')->where('migration', 'like', '%create_system_settings_table%')->delete();
echo "Cleanup completed successfully.";
