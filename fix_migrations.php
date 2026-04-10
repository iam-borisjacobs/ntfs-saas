<?php

$files = glob('C:\\dev\\NTFS-SaaS\\database\\migrations\\*.php');
$count = 0;
foreach ($files as $file) {
    if (strpos(basename($file), 'create_audit_logs_table') !== false) continue;
    
    $content = file_get_contents($file);
    $modified = false;
    
    // Look for DB::unprepared("...") or DB::unprepared('...')
    $content = preg_replace_callback('/(\\\\?Illuminate\\\\Support\\\\Facades\\\\)?DB::unprepared\s*\(\s*(["\'])(.*?)\2\s*\)\s*;/su', function($matches) use (&$modified) {
        $stmt = $matches[0];
        $innerSql = $matches[3];
        if (stripos($innerSql, 'plpgsql') !== false || stripos($innerSql, 'CONSTRAINT') !== false || stripos($innerSql, 'trgm') !== false || stripos($innerSql, 'CREATE EXTENSION') !== false) {
            $modified = true;
            return "if (\\\\Illuminate\\\\Support\\\\Facades\\\\DB::connection()->getDriverName() === 'pgsql') {\n            " . trim($stmt) . "\n        }";
        }
        return $stmt;
    }, $content);
    
    if ($modified) {
        file_put_contents($file, $content);
        $count++;
    }
}
echo "Modified: $count\n";
