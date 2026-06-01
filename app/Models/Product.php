<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'price',
        'image',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Resep: bahan baku yang dipakai produk ini, dengan kolom pivot quantity.
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'product_ingredient')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * URL gambar produk (atau placeholder jika belum ada).
     */
    public function imageUrl(): string
    {
        return self::resolveImageUrl($this->image);
    }

    public static function imageDisk(): string
    {
        return config('filesystems.product_image_disk', 'public');
    }

    public static function resolveImageUrl(?string $image): string
    {
        if (! $image) {
            return 'https://placehold.co/300x300?text=No+Image';
        }

        if (str_starts_with($image, 'data:image/') || str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        $disk = self::imageDisk();
        $baseUrl = config("filesystems.disks.{$disk}.url");

        if ($baseUrl) {
            return rtrim($baseUrl, '/').'/'.ltrim($image, '/');
        }

        return Storage::disk($disk)->url($image);
    }
}
