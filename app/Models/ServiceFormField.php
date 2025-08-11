<?php
// app/Models/ServiceFormField.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ServiceFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_service_id',
        'field_name',
        'field_label',
        'field_type',
        'field_options',
        'validation_rules',
        'normal_range',
        'sort_order',
        'is_required',
        'placeholder',
        'help_text',
        'field_group',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'field_options' => 'array',
            'validation_rules' => 'array',
            'normal_range' => 'array',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot method ສຳລັບ auto-generate field_name
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($field) {
            // Auto-generate field_name ຖ້າບໍ່ໄດ້ລະບຸ
            if (empty($field->field_name)) {
                $field->field_name = self::generateFieldName($field->field_label);
            }
        });
    }

    /**
     * ສ້າງ field_name ຈາກ field_label
     */
    public static function generateFieldName(string $label): string
    {
        // ລຶບພາສາລາວ ແລະ ສັນຍາລັກພິເສດ
        $cleaned = preg_replace('/[^\w\s]/', '', $label);
        $cleaned = trim($cleaned);
        
        // ແປງເປັນ snake_case ແບບງ່າຍ
        $fieldName = strtolower(str_replace(' ', '_', $cleaned));
        
        // ເພີ່ມ prefix ຖ້າຂຶ້ນຕົ້ນດ້ວຍຕົວເລກ
        if (is_numeric(substr($fieldName, 0, 1))) {
            $fieldName = 'field_' . $fieldName;
        }
        
        return $fieldName ?: 'custom_field_' . time();
    }

    /**
     * ຄວາມສຳພັນກັບ MedicalService
     */
    public function medicalService(): BelongsTo
    {
        return $this->belongsTo(MedicalService::class);
    }

    /**
     * Scope ສຳລັບ fields ທີ່ active
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ສຳລັບ fields ທີ່ required
     */
    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope ສຳລັບເລຽງລຳດັບ
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * ດຶງ field types ທີ່ໃຊ້ໄດ້ທັງໝົດ
     */
    public static function getAvailableFieldTypes(): array
    {
        return [
            'text' => [
                'label' => '📝 ຂໍ້ຄວາມສັ້ນ',
                'description' => 'ຂໍ້ຄວາມ 1 ແຖວ (ເຊັ່ນ: ຊື່, ເລກ)',
                'icon' => 'heroicon-o-pencil',
            ],
            'textarea' => [
                'label' => '📄 ຂໍ້ຄວາມຍາວ',
                'description' => 'ຂໍ້ຄວາມຫຼາຍແຖວ (ເຊັ່ນ: ໝາຍເຫດ, ອາການ)',
                'icon' => 'heroicon-o-document-text',
            ],
            'number' => [
                'label' => '🔢 ຕົວເລກ',
                'description' => 'ຕົວເລກ + ຄ່າປົກກະຕິ (ເຊັ່ນ: ຄວາມດັນ, ນ້ຳໜັກ)',
                'icon' => 'heroicon-o-calculator',
            ],
            'select' => [
                'label' => '📋 ລາຍການເລືອກ',
                'description' => 'ເລືອກ 1 ຈາກຫຼາຍຕົວເລືອກ (ເຊັ່ນ: ເພດ, ຜົນການກວດ)',
                'icon' => 'heroicon-o-list-bullet',
            ],
            'checkbox' => [
                'label' => '☑️ ຕິກເລືອກ',
                'description' => 'ເລືອກໄດ້ຫຼາຍຕົວ ຫຼື ມີ/ບໍ່ມີ',
                'icon' => 'heroicon-o-check-badge',
            ],
            'date' => [
                'label' => '📅 ວັນທີ',
                'description' => 'ເລືອກວັນທີ (ເຊັ່ນ: ວັນນັດກວດຄືນ)',
                'icon' => 'heroicon-o-calendar-days',
            ],
            'time' => [
                'label' => '🕐 ເວລາ',
                'description' => 'ເລືອກເວລາ (ເຊັ່ນ: ເວລາເກັບຕົວຢ່າງ)',
                'icon' => 'heroicon-o-clock',
            ],
            'datetime' => [
                'label' => '📅🕐 ວັນທີ + ເວລາ',
                'description' => 'ເລືອກທັງວັນທີ ແລະ ເວລາ',
                'icon' => 'heroicon-o-calendar',
            ],
        ];
    }

    /**
     * ສ້າງ Filament form component ຈາກ field ນີ້
     */
    public function createFilamentComponent(): array
    {
        $component = [];
        
        switch ($this->field_type) {
            case 'text':
                $component = [
                    'type' => 'TextInput',
                    'config' => [
                        'label' => $this->field_label,
                        'placeholder' => $this->placeholder,
                        'required' => $this->is_required,
                        'helperText' => $this->help_text,
                    ]
                ];
                break;

            case 'textarea':
                $component = [
                    'type' => 'Textarea',
                    'config' => [
                        'label' => $this->field_label,
                        'placeholder' => $this->placeholder,
                        'required' => $this->is_required,
                        'rows' => $this->field_options['rows'] ?? 3,
                        'helperText' => $this->help_text,
                    ]
                ];
                break;

            case 'number':
                $component = [
                    'type' => 'TextInput',
                    'config' => [
                        'label' => $this->field_label,
                        'placeholder' => $this->placeholder,
                        'required' => $this->is_required,
                        'numeric' => true,
                        'helperText' => $this->buildRangeHelperText(),
                        'min' => $this->normal_range['min'] ?? null,
                        'max' => $this->normal_range['max'] ?? null,
                    ]
                ];
                break;

            case 'select':
                $component = [
                    'type' => 'Select',
                    'config' => [
                        'label' => $this->field_label,
                        'required' => $this->is_required,
                        'options' => $this->field_options['options'] ?? [],
                        'helperText' => $this->help_text,
                    ]
                ];
                break;

            case 'checkbox':
                if ($this->field_options['type'] ?? 'single' === 'single') {
                    $component = [
                        'type' => 'Toggle',
                        'config' => [
                            'label' => $this->field_label,
                            'helperText' => $this->help_text,
                        ]
                    ];
                } else {
                    $component = [
                        'type' => 'CheckboxList',
                        'config' => [
                            'label' => $this->field_label,
                            'required' => $this->is_required,
                            'options' => $this->field_options['options'] ?? [],
                            'helperText' => $this->help_text,
                        ]
                    ];
                }
                break;

            case 'date':
                $component = [
                    'type' => 'DatePicker',
                    'config' => [
                        'label' => $this->field_label,
                        'required' => $this->is_required,
                        'helperText' => $this->help_text,
                    ]
                ];
                break;

            case 'time':
                $component = [
                    'type' => 'TimePicker',
                    'config' => [
                        'label' => $this->field_label,
                        'required' => $this->is_required,
                        'helperText' => $this->help_text,
                    ]
                ];
                break;

            case 'datetime':
                $component = [
                    'type' => 'DateTimePicker',
                    'config' => [
                        'label' => $this->field_label,
                        'required' => $this->is_required,
                        'helperText' => $this->help_text,
                    ]
                ];
                break;
        }

        return $component;
    }

    /**
     * ສ້າງຂໍ້ຄວາມຊ່ວຍເຫຼືອສຳລັບ range
     */
    private function buildRangeHelperText(): ?string
    {
        $text = $this->help_text;
        
        if (!empty($this->normal_range)) {
            $min = $this->normal_range['min'] ?? null;
            $max = $this->normal_range['max'] ?? null;
            $unit = $this->normal_range['unit'] ?? '';
            
            if ($min !== null && $max !== null) {
                $rangeText = "ຄ່າປົກກະຕິ: {$min} - {$max} {$unit}";
                $text = $text ? $text . " | " . $rangeText : $rangeText;
            }
        }
        
        return $text;
    }

    /**
     * ກວດສອບຄ່າທີ່ປ້ອນມາວ່າຢູ່ໃນເກນປົກກະຕິບໍ່
     */
    public function validateValue($value): array
    {
        $result = ['is_valid' => true, 'message' => null, 'status' => 'normal'];
        
        if ($this->field_type === 'number' && !empty($this->normal_range)) {
            $min = $this->normal_range['min'] ?? null;
            $max = $this->normal_range['max'] ?? null;
            $numValue = (float) $value;
            
            if ($min !== null && $numValue < $min) {
                $result = [
                    'is_valid' => false,
                    'message' => "ຄ່າຕ່ຳກວ່າເກນປົກກະຕິ (< {$min})",
                    'status' => 'low'
                ];
            } elseif ($max !== null && $numValue > $max) {
                $result = [
                    'is_valid' => false,
                    'message' => "ຄ່າສູງກວ່າເກນປົກກະຕິ (> {$max})",
                    'status' => 'high'
                ];
            } else {
                $result['message'] = 'ຄ່າຢູ່ໃນເກນປົກກະຕິ';
                $result['status'] = 'normal';
            }
        }
        
        return $result;
    }
}