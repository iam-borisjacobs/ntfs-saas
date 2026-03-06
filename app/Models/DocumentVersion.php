<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentVersion extends Model
{
    protected $fillable = [
        'document_id',
        'version_number',
        'file_path',
        'uploaded_by',
    ];

    /**
     * The main document this history entry belongs to.
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * The user who uploaded this specific historical version.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
