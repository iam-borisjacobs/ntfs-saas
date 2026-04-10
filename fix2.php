<?php
$files = glob('C:\\dev\\NTFS-SaaS\\database\\migrations\\*.php');
$count = 0;
foreach ($files as $file) {
    if (strpos(basename($file), 'create_audit_logs_table') !== false) continue;
    $content = file_get_contents($file);
    if (strpos($content, 'DB::statement') !== false) {
        $content = preg_replace_callback('/(\\\\?Illuminate\\\\Support\\\\Facades\\\\)?DB::statement\s*\(\s*(["\'])(.*?)\2\s*\)\s*;/su', function($matches) {
            $stmt = $matches[0];
            $innerSql = $matches[3];
            if (stripos($innerSql, 'plpgsql') !== false || stripos($innerSql, 'CONSTRAINT') !== false || stripos($innerSql, 'trgm') !== false || stripos($innerSql, 'CREATE EXTENSION') !== false) {
                return "if (\\Illuminate\\Support\\Facades\\DB::connection()->getDriverName() === 'pgsql') {\n            " . trim($stmt) . "\n        }";
            }
            return $stmt;
        }, $content);
        file_put_contents($file, $content);
        $count++;
    }
}
echo "Replaced DB::statement in $count files\n";
