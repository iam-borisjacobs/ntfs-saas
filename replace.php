<?php
$files = glob('C:\\dev\\NTFS-SaaS\\database\\migrations\\*.php');
$count = 0;
foreach ($files as $file) {
    if (strpos(basename($file), 'create_audit_logs_table') !== false) continue;
    
    $content = file_get_contents($file);
    // In PHP, '\\\\' matches literally two backslashes in a string.
    if (strpos($content, 'if (\\\\Illuminate') !== false) {
        $content = str_replace('if (\\\\Illuminate', 'if (\\Illuminate', $content);
        file_put_contents($file, $content);
        $count++;
    }
}
echo "Replaced in $count files\n";
