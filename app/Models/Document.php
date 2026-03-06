<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'file_id',
        'movement_id',
        'uploaded_by',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'file_hash',
        'version_number',
        'status',
    ];

    /**
     * Get the historic versions of this document.
     */
    public function versions()
    {
        return $this->hasMany(DocumentVersion::class)->orderBy('version_number', 'desc');
    }

    /**
     * Optional: The parent physical file this document belongs to.
     */
    public function fileRecord()
    {
        return $this->belongsTo(FileRecord::class, 'file_id');
    }

    /**
     * Optional: The specific movement action this was attached during.
     */
    public function movement()
    {
        return $this->belongsTo(FileMovement::class, 'movement_id');
    }

    /**
     * The user who uploaded the current active version.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
