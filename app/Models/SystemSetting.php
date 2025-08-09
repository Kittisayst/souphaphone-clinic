<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'is_public' => 'boolean',
        ];
    }

    /**
     * Boot method ສຳຫລັບ cache management
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache ເມື່ອມີການປ່ຽນແປງ
        static::saved(function ($setting) {
            Cache::forget("setting_{$setting->key}");
            Cache::forget("settings_group_{$setting->group}");
            Cache::forget('all_settings');
        });

        static::deleted(function ($setting) {
            Cache::forget("setting_{$setting->key}");
            Cache::forget("settings_group_{$setting->group}");
            Cache::forget('all_settings');
        });
    }

    /**
     * ດຶງຄ່າການຕັ້ງຄ່າ (ມີ cache)
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * ຕັ້ງຄ່າ (ອັບເດດຫຼືສ້າງໃໝ່)
     */
    public static function set(
        string $key, 
        $value, 
        string $group = 'general', 
        string $type = 'auto',
        string $description = null,
        bool $isPublic = false
    ): self {
        // Auto-detect type ຖ້າບໍ່ໄດ້ລະບຸ
        if ($type === 'auto') {
            $type = self::detectType($value);
        }

        // ແປງຄ່າເປັນ format ທີ່ເກັບໄດ້
        $storedValue = self::prepareValue($value, $type);

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'group' => $group,
                'type' => $type,
                'description' => $description,
                'is_public' => $isPublic,
            ]
        );
    }

    /**
     * ດຶງການຕັ້ງຄ່າທັງກຸ່ມ
     */
    public static function getGroup(string $group): array
    {
        return Cache::remember("settings_group_{$group}", 3600, function () use ($group) {
            $settings = self::where('group', $group)->get();
            
            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = self::castValue($setting->value, $setting->type);
            }
            
            return $result;
        });
    }

    /**
     * ດຶງການຕັ້ງຄ່າທັງໝົດ
     */
    public static function getAll(): array
    {
        return Cache::remember('all_settings', 3600, function () {
            $settings = self::all();
            
            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = self::castValue($setting->value, $setting->type);
            }
            
            return $result;
        });
    }

    /**
     * ດຶງການຕັ້ງຄ່າສາທາລະນະ (ບໍ່ຕ້ອງ login)
     */
    public static function getPublic(): array
    {
        return Cache::remember('public_settings', 3600, function () {
            $settings = self::where('is_public', true)->get();
            
            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = self::castValue($setting->value, $setting->type);
            }
            
            return $result;
        });
    }

    /**
     * ລົບການຕັ້ງຄ່າ
     */
    public static function remove(string $key): bool
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->delete() : false;
    }

    /**
     * ກວດສອບວ່າມີການຕັ້ງຄ່ານີ້ບໍ່
     */
    public static function has(string $key): bool
    {
        return self::where('key', $key)->exists();
    }

    /**
     * Auto-detect ປະເພດຂອງຄ່າ
     */
    private static function detectType($value): string
    {
        if (is_bool($value)) return 'boolean';
        if (is_int($value)) return 'number';
        if (is_float($value)) return 'number';
        if (is_array($value)) return 'json';
        if (is_object($value)) return 'json';
        
        return 'text';
    }

    /**
     * ເຕຣຽມຄ່າສຳຫຼັບການເກັບ
     */
    private static function prepareValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return ['value' => (bool) $value];
            case 'number':
                return ['value' => is_numeric($value) ? +$value : 0];
            case 'json':
                return is_array($value) || is_object($value) ? $value : ['value' => $value];
            case 'text':
            default:
                return ['value' => (string) $value];
        }
    }

    /**
     * Cast ຄ່າເປັນປະເພດທີ່ຖືກຕ້ອງ
     */
    private static function castValue($storedValue, string $type)
    {
        if (!is_array($storedValue)) {
            return $storedValue;
        }

        $value = $storedValue['value'] ?? $storedValue;

        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'number':
                return is_numeric($value) ? +$value : 0;
            case 'json':
                return is_array($value) ? $value : json_decode($value, true);
            case 'text':
            default:
                return (string) $value;
        }
    }

    /**
     * ປະເພດການຕັ້ງຄ່າເປັນພາສາລາວ
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'text' => 'ຂໍ້ຄວາມ',
            'number' => 'ຕົວເລກ',
            'boolean' => 'ບູລີນ (ຈິງ/ເທັດ)',
            'json' => 'JSON',
            default => $this->type,
        };
    }

    /**
     * ກຸ່ມການຕັ້ງຄ່າເປັນພາສາລາວ
     */
    public function getGroupLabelAttribute(): string
    {
        return match ($this->group) {
            'general' => 'ທົ່ວໄປ',
            'clinic' => 'ຄລີນິກ',
            'billing' => 'ການເງິນ',
            'system' => 'ລະບົບ',
            'notification' => 'ການແຈ້ງເຕືອນ',
            'security' => 'ຄວາມປອດໄພ',
            'appearance' => 'ຮູບລັກສະນະ',
            default => $this->group,
        };
    }

    /**
     * ຄ່າທີ່ຖືກ cast ແລ້ວ
     */
    public function getCastedValueAttribute()
    {
        return self::castValue($this->value, $this->type);
    }

    /**
     * Scope ສຳຫຼັບຄົ້ນຫາ
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * ການຕັ້ງຄ່າເຊື່ອມຕໍ່ພາຍນອກ (Helper Methods)
     */

    // ການຕັ້ງຄ່າຄລີນິກ
    public static function getClinicName(): string
    {
        return self::get('clinic_name', 'ຄລີນິກ');
    }

    public static function getClinicAddress(): string
    {
        return self::get('clinic_address', '');
    }

    public static function getClinicPhone(): string
    {
        return self::get('clinic_phone', '');
    }

    public static function getClinicEmail(): string
    {
        return self::get('clinic_email', '');
    }

    // ການຕັ້ງຄ່າເວລາ
    public static function getWorkingHours(): array
    {
        return self::get('working_hours', [
            'monday' => ['start' => '08:00', 'end' => '17:00'],
            'tuesday' => ['start' => '08:00', 'end' => '17:00'],
            'wednesday' => ['start' => '08:00', 'end' => '17:00'],
            'thursday' => ['start' => '08:00', 'end' => '17:00'],
            'friday' => ['start' => '08:00', 'end' => '17:00'],
            'saturday' => ['start' => '08:00', 'end' => '12:00'],
            'sunday' => ['start' => '', 'end' => ''], // ປິດ
        ]);
    }

    // ການຕັ້ງຄ່າການເງິນ
    public static function getDefaultCurrency(): string
    {
        return self::get('default_currency', 'LAK');
    }

    public static function getTaxRate(): float
    {
        return self::get('tax_rate', 0.0);
    }

    public static function getAutoInvoiceNumber(): bool
    {
        return self::get('auto_invoice_number', true);
    }

    // ການຕັ້ງຄ່າຄິວ
    public static function getQueuePrefix(): string
    {
        return self::get('queue_prefix', 'A');
    }

    public static function getAutoResetQueue(): bool
    {
        return self::get('auto_reset_queue', true);
    }

    public static function getMaxQueuePerDay(): int
    {
        return self::get('max_queue_per_day', 999);
    }

    // ການຕັ້ງຄ່າການແຈ້ງເຕືອນ
    public static function getLowStockThreshold(): int
    {
        return self::get('low_stock_threshold', 10);
    }

    public static function getExpiryWarningDays(): int
    {
        return self::get('expiry_warning_days', 30);
    }

    /**
     * ສ້າງການຕັ້ງຄ່າເລີ່ມຕົ້ນ
     */
    public static function createDefaults(): void
    {
        $defaults = [
            // ການຟັ້ງຄ່າຄລີນິກ
            ['key' => 'clinic_name', 'value' => 'ຄລີນິກສຸຂະພາບ', 'group' => 'clinic', 'type' => 'text', 'description' => 'ຊື່ຄລີນິກ', 'is_public' => true],
            ['key' => 'clinic_address', 'value' => '', 'group' => 'clinic', 'type' => 'text', 'description' => 'ທີ່ຢູ່ຄລີນິກ', 'is_public' => true],
            ['key' => 'clinic_phone', 'value' => '', 'group' => 'clinic', 'type' => 'text', 'description' => 'ເບີໂທຄລີນິກ', 'is_public' => true],
            ['key' => 'clinic_email', 'value' => '', 'group' => 'clinic', 'type' => 'text', 'description' => 'ອີເມລຄລີນິກ', 'is_public' => true],
            
            // ການຕັ້ງຄ່າການເງິນ
            ['key' => 'default_currency', 'value' => 'LAK', 'group' => 'billing', 'type' => 'text', 'description' => 'ສະກຸນເງິນຫຼັກ', 'is_public' => false],
            ['key' => 'tax_rate', 'value' => 0.0, 'group' => 'billing', 'type' => 'number', 'description' => 'ອັດຕາພາສີ (%)', 'is_public' => false],
            ['key' => 'auto_invoice_number', 'value' => true, 'group' => 'billing', 'type' => 'boolean', 'description' => 'ສ້າງເລກໃບເກັບເງິນອັດຕະໂນມັດ', 'is_public' => false],
            
            // ການຕັ້ງຄ່າຄິວ
            ['key' => 'queue_prefix', 'value' => 'A', 'group' => 'general', 'type' => 'text', 'description' => 'ອັກສອນໜ້າເລກຄິວ', 'is_public' => false],
            ['key' => 'auto_reset_queue', 'value' => true, 'group' => 'general', 'type' => 'boolean', 'description' => 'ຣີເຊັດຄິວອັດຕະໂນມັດທຸກວັນ', 'is_public' => false],
            ['key' => 'max_queue_per_day', 'value' => 999, 'group' => 'general', 'type' => 'number', 'description' => 'ຈຳນວນຄິວສູງສຸດຕໍ່ວັນ', 'is_public' => false],
            
            // ການຕັ້ງຄ່າສາງ
            ['key' => 'low_stock_threshold', 'value' => 10, 'group' => 'system', 'type' => 'number', 'description' => 'ເກນແຈ້ງເຕືອນສາງຕ່ຳ', 'is_public' => false],
            ['key' => 'expiry_warning_days', 'value' => 30, 'group' => 'system', 'type' => 'number', 'description' => 'ແຈ້ງເຕືອນກ່ອນໝົດອາຍຸ (ວັນ)', 'is_public' => false],
            
            // ການຕັ້ງຄ່າຮູບລັກສະນະ
            ['key' => 'app_theme', 'value' => 'light', 'group' => 'appearance', 'type' => 'text', 'description' => 'ໂທນສີຫຼັກ', 'is_public' => true],
            ['key' => 'app_logo', 'value' => '', 'group' => 'appearance', 'type' => 'text', 'description' => 'ໂລໂກ້ຂອງແອັບ', 'is_public' => true],
            
            // ການຕັ້ງຄ່າເວລາເຮັດວຽກ
            ['key' => 'working_hours', 'value' => [
                'monday' => ['start' => '08:00', 'end' => '17:00'],
                'tuesday' => ['start' => '08:00', 'end' => '17:00'],
                'wednesday' => ['start' => '08:00', 'end' => '17:00'],
                'thursday' => ['start' => '08:00', 'end' => '17:00'],
                'friday' => ['start' => '08:00', 'end' => '17:00'],
                'saturday' => ['start' => '08:00', 'end' => '12:00'],
                'sunday' => ['start' => '', 'end' => ''],
            ], 'group' => 'clinic', 'type' => 'json', 'description' => 'ເວລາເຮັດວຽກແຕ່ລະວັນ', 'is_public' => true],
        ];

        foreach ($defaults as $setting) {
            self::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * ສຳຮອງການຕັ້ງຄ່າທັງໝົດ
     */
    public static function exportSettings(): array
    {
        return self::all()->map(function ($setting) {
            return [
                'key' => $setting->key,
                'value' => $setting->casted_value,
                'group' => $setting->group,
                'type' => $setting->type,
                'description' => $setting->description,
                'is_public' => $setting->is_public,
            ];
        })->toArray();
    }

    /**
     * ນຳເຂົ້າການຕັ້ງຄ່າ
     */
    public static function importSettings(array $settings): int
    {
        $imported = 0;

        foreach ($settings as $setting) {
            if (!isset($setting['key']) || !isset($setting['value'])) {
                continue;
            }

            self::set(
                key: $setting['key'],
                value: $setting['value'],
                group: $setting['group'] ?? 'general',
                type: $setting['type'] ?? 'auto',
                description: $setting['description'] ?? null,
                isPublic: $setting['is_public'] ?? false
            );

            $imported++;
        }

        return $imported;
    }

    /**
     * ລຶບ cache ທັງໝົດ
     */
    public static function clearCache(): void
    {
        Cache::forget('all_settings');
        Cache::forget('public_settings');
        
        // ລຶບ cache ທຸກ group
        $groups = self::distinct('group')->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("settings_group_{$group}");
        }

        // ລຶບ cache ແຕ່ລະ key
        $keys = self::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting_{$key}");
        }
    }
}