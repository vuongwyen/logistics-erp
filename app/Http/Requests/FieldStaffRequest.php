<?php

namespace App\Http\Requests;

use App\Support\VietnameseDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FieldStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(VietnameseDate::normalizedFields($this->all(), ['date_of_birth', 'start_date']));

        if (is_string($this->input('phone'))) {
            $this->merge(['phone' => trim($this->input('phone'))]);
        }

        if (! $this->has('responsible_location_ids') && $this->filled('responsible_location_id')) {
            $this->merge(['responsible_location_ids' => [$this->input('responsible_location_id')]]);
        }

        if ($this->has('responsible_location_ids')) {
            $locationIds = collect((array) $this->input('responsible_location_ids'))
                ->filter()
                ->values()
                ->all();

            $this->merge([
                'responsible_location_ids' => $locationIds,
                'responsible_location_id' => $locationIds[0] ?? null,
            ]);
        }
    }

    public function rules(): array
    {
        $fieldStaff = $this->route('field_staff');
        $fieldStaffId = $fieldStaff ? $fieldStaff->id : null;

        return [
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role_id', function ($subQuery) {
                        $subQuery->select('id')
                            ->from('roles')
                            ->where('role_code', 'FIELD');
                    });
                }),
                Rule::unique('field_staff', 'user_id')->ignore($fieldStaffId),
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^0\d{9}$/'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'certificates' => ['nullable', 'string', 'max:1000'],
            'responsible_location_id' => ['nullable', 'integer'],
            'responsible_location_ids' => ['required', 'array', 'min:1'],
            'responsible_location_ids.*' => [
                'required',
                'integer',
                Rule::exists('locations', 'id')->where(fn ($query) => $query->whereIn('type', ['depot', 'warehouse'])),
            ],
            'start_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'Tài khoản liên kết phải thuộc vai trò Nhân viên hiện trường.',
            'user_id.unique' => 'Tài khoản này đã được liên kết với một nhân viên hiện trường khác.',
            'full_name.required' => 'Vui lòng nhập họ tên nhân viên hiện trường.',
            'phone.regex' => 'Số điện thoại phải gồm đúng 10 số và bắt đầu bằng 0.',
            'date_of_birth.required' => 'Vui lòng nhập ngày sinh nhân viên hiện trường.',
            'date_of_birth.date' => 'Ngày sinh phải đúng định dạng ngày/tháng/năm.',
            'responsible_location_ids.required' => 'Vui lòng chọn ít nhất một khu vực phụ trách.',
            'responsible_location_ids.*.exists' => 'Khu vực phụ trách phải là kho hoặc bãi hợp lệ.',
        ];
    }
}
