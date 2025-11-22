<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'name',
        'asset_type_id',
        'brand',
        'serial_number',
        'price',
        'purchase_date',
        'location',
        'condition',
        'status',
        'image',
        'qr_code',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($asset) {
            if (empty($asset->asset_id)) {
                $asset->asset_id = self::generateAssetId($asset->asset_type_id);
            }
        });
    }

    public static function generateAssetId($assetTypeId)
    {
        $type = AssetType::find($assetTypeId);
        $year = date('Y');
        $month = date('m');

        $lastAsset = self::where('asset_id', 'LIKE', "$year/$month/{$type->code}-%")
            ->orderBy('asset_id', 'desc')
            ->first();

        if ($lastAsset) {
            $lastNumber = (int) substr($lastAsset->asset_id, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "$year/$month/{$type->code}-$newNumber";
    }

    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }

    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    public function qrCode()
    {
        return $this->hasOne(QrCode::class);
    }
}