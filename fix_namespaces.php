<?php
$files = glob('C:\\dev\\NTFS-SaaS\\database\\migrations\\*.php');
$count = 0;
foreach ($files as $file) {
    if (strpos(basename($file), 'create_audit_logs_table') !== false) continue;
    
    $content = file_get_contents($file);
    if (strpos($content, "\\Illuminate\\\\Support\\\\Facades\\\\DB") !== false || strpos($content, "\\\\Illuminate\\\\Support\\\\Facades\\\\DB") !== false) {
        $content = str_replace("\\\\Illuminate\\\\Support\\\\Facades\\\\DB", "\\Illuminate\\Support\\Facades\\DB", $content);
        $content = str_replace("\\Illuminate\\\\Support\\\\Facades\\\\DB", "\\Illuminate\\Support\\Facades\\DB", $content);
        file_put_contents($file, $content);
        $count++;
    }
}
echo "Cleaned namespaces in $count files\n";
