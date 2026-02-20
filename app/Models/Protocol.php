<?php

namespace App\Models;

use App\Services\Typesense\TypesenseIndexer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Protocol extends Model
{
    /** @use HasFactory<\Database\Factories\ProtocolFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'content',
        'tags',
        'author_id',
        'rating',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $protocol): void {
            app(TypesenseIndexer::class)->upsertProtocol($protocol);
        });

        static::deleted(function (self $protocol): void {
            app(TypesenseIndexer::class)->deleteProtocol((string) $protocol->getKey());
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'rating' => 'float',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
