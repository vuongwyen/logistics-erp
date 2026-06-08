<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $activeTab = $request->input('_active_tab');
        $rules = [];
        $profileData = [];

        $validationMessages = [
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'password.mixed' => 'Mật khẩu phải có cả chữ hoa và chữ thường.',
            'password.numbers' => 'Mật khẩu phải có ít nhất một chữ số.',
            'password.symbols' => 'Mật khẩu phải có ít nhất một ký tự đặc biệt.',
        ];

        if ($activeTab === 'tab-hosoca' || !$activeTab || $request->has('name')) {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id];

            $profileData['name'] = $request->name;
            $profileData['full_name'] = $request->name;
            $profileData['email'] = $request->email;
        }
        
        if ($activeTab === 'tab-baomat' || !$activeTab || $request->has('theme_color')) {
            $rules['theme_color'] = ['required', 'string', 'max:7'];
            $rules['is_dark_mode'] = ['boolean'];
            $rules['timezone'] = ['required', 'in:Asia/Ho_Chi_Minh,Asia/Bangkok,UTC'];
            $rules['date_format'] = ['required', 'in:d/m/Y,Y-m-d,d-m-Y'];
            $rules['two_factor_enabled'] = ['boolean'];
            $rules['password'] = ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()];

            $profileData['theme_color'] = $request->theme_color;
            $profileData['is_dark_mode'] = $request->boolean('is_dark_mode');

            foreach (['timezone', 'date_format', 'two_factor_enabled'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $profileData[$column] = $column === 'two_factor_enabled'
                        ? $request->boolean($column)
                        : $request->input($column);
                }
            }

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }
        }

        $request->validate($rules, $validationMessages);

        if (! empty($profileData)) {
            $user->update($profileData);
        }

        session()->flash('active_tab', $activeTab ?: 'tab-hosoca');

        return back()->with('success', 'Cập nhật thành công! (Chế độ tối: '.($user->is_dark_mode ? 'Bật' : 'Tắt').')');
    }
}
