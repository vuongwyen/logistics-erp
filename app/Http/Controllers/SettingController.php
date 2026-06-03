<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Danh sách key tham số hệ thống mặc định ở Phân khu 2.
     *
     * @var array<string, array{description: string, default: string}>
     */
    private static array $systemParamDefaults = [
        'system.usd_rate' => ['description' => 'Tỷ giá quy đổi (USD/VND)', 'default' => '25450'],
        'system.vat_percent' => ['description' => 'Thuế GTGT mặc định (%)', 'default' => '10'],
        'system.fuel_limit' => ['description' => 'Hạn mức tiền dầu (VND)', 'default' => '5000000'],
        'system.toll_limit' => ['description' => 'Hạn mức phí cầu đường (VND)', 'default' => '2000000'],
        'system.overage_alert' => ['description' => 'Bật cảnh báo vượt định mức', 'default' => '0'],
        'system.auto_backup_frequency' => ['description' => 'Tần suất tự động sao lưu', 'default' => 'never'],
        'system.last_auto_backup' => ['description' => 'Thời gian tự động sao lưu gần nhất', 'default' => ''],
    ];

    public function index()
    {
        $settings = Setting::all()->groupBy('group');

        // Đảm bảo các key tham số hệ thống luôn tồn tại
        foreach (self::$systemParamDefaults as $key => $meta) {
            Setting::firstOrCreate(
                ['key' => $key],
                ['value' => $meta['default'], 'group' => 'system', 'description' => $meta['description']]
            );
        }

        // Làm mới sau khi seed mặc định
        $settings = Setting::all()->groupBy('group');

        $systemParams = Setting::where('group', 'system')->get()->keyBy('key');
        $user = auth()->user();

        // Danh sách file backup tự động trên server
        $backupFiles = [];
        if (Storage::exists('backups')) {
            $files = Storage::files('backups');
            foreach ($files as $file) {
                $backupFiles[] = [
                    'name' => basename($file),
                    'size' => Storage::size($file),
                    'modified' => Storage::lastModified($file),
                ];
            }
            // Sắp xếp mới nhất lên trên
            usort($backupFiles, fn ($a, $b) => $b['modified'] <=> $a['modified']);
        }

        $installers = collect();
        if (Storage::disk('public')->exists('installers')) {
            $files = Storage::disk('public')->files('installers');
            foreach ($files as $file) {
                if (str_ends_with($file, '.exe')) {
                    $installers->push([
                        'name' => basename($file),
                        'path' => $file,
                        'size' => Storage::disk('public')->size($file),
                        'modified' => Storage::disk('public')->lastModified($file),
                        'url' => Storage::disk('public')->url($file),
                    ]);
                }
            }
        }
        $installers = $installers->sortByDesc('modified');

        return view('settings.index', compact('settings', 'systemParams', 'backupFiles', 'installers'));
    }

    public function company()
    {
        $settings = Setting::all()->groupBy('group');
        $companySettings = $settings->get('company', collect())->keyBy('key');
        return view('settings.company', compact('companySettings'));
    }

    public function update(Request $request)
    {
        // Cập nhật tham số cấu hình công ty / chung
        if ($request->has('settings')) {
            foreach ($request->settings as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        // Cập nhật tham số hệ thống (Phân khu 2)
        if ($request->has('system_params')) {
            foreach ($request->system_params as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => 'system']
                );
            }
        }

        // Checkbox "Bật cảnh báo vượt định mức" (unchecked gửi null)
        Setting::updateOrCreate(
            ['key' => 'system.overage_alert'],
            ['value' => $request->boolean('system_params.system.overage_alert') ? '1' : '0', 'group' => 'system']
        );

        ActivityLog::log('update_settings', 'Cập nhật cấu hình và tham số hệ thống');

        return back()->with('success', 'Đã cập nhật cấu hình hệ thống thành công!');
    }

    public function backup()
    {
        $dbHost = config('database.connections.mysql.host', '127.0.0.1');
        $dbPort = config('database.connections.mysql.port', '3306');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        // Tìm mysqldump.exe (XAMPP hoặc PATH)
        $mysqldumpBin = 'mysqldump';
        $xamppMysqldump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        if (file_exists($xamppMysqldump)) {
            $mysqldumpBin = "\"{$xamppMysqldump}\"";
        }

        $passArg = $dbPass ? '-p'.escapeshellarg($dbPass) : '';

        $filename = 'backup_logistics_'.date('Ymd_His').'.sql';

        // Tạo thư mục tạm nếu chưa có
        if (! Storage::exists('backup_tmp')) {
            Storage::makeDirectory('backup_tmp');
        }

        $fullPath = Storage::path('backup_tmp/'.$filename);

        $command = "{$mysqldumpBin} -h {$dbHost} -P {$dbPort} -u ".escapeshellarg($dbUser)." {$passArg} ".escapeshellarg($dbName).' > '.escapeshellarg($fullPath).' 2>&1';

        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            ActivityLog::log('backup_database_failed', 'Thất bại khi xuất cơ sở dữ liệu: '.implode(' ', $output));

            return back()->with('error', 'Sao lưu thất bại: '.implode(' ', $output));
        }

        ActivityLog::log('backup_database', 'Xuất bản sao lưu cơ sở dữ liệu hệ thống (.sql)');

        return Response::download($fullPath, $filename, [
            'Content-Type' => 'application/sql',
        ])->deleteFileAfterSend(true);
    }

    public function downloadBackup(string $filename)
    {
        // Chỉ cho phép tải file .sql từ thư mục backups/
        $safeName = basename($filename);
        $path = 'backups/'.$safeName;

        if (! Storage::exists($path) || ! str_ends_with($safeName, '.sql')) {
            abort(404, 'File backup không tồn tại.');
        }

        ActivityLog::log('download_auto_backup', 'Tải xuống file sao lưu tự động: '.$safeName);

        return Response::download(Storage::path($path), $safeName, [
            'Content-Type' => 'application/sql',
        ]);
    }

    public function deleteBackup(string $filename)
    {
        $safeName = basename($filename);
        $path = 'backups/'.$safeName;

        if (! Storage::exists($path) || ! str_ends_with($safeName, '.sql')) {
            abort(404, 'File backup không tồn tại.');
        }

        Storage::delete($path);
        ActivityLog::log('delete_auto_backup', 'Xóa file sao lưu tự động: '.$safeName);

        return back()->with('success', 'Đã xóa file backup: '.$safeName);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'restore_file' => 'required|file|mimes:sql,txt|max:51200',
            'admin_password' => 'required|string',
        ], [
            'restore_file.required' => 'Vui lòng chọn file .sql để khôi phục.',
            'restore_file.mimes' => 'File khôi phục phải có định dạng .sql hoặc .txt.',
            'restore_file.max' => 'File khôi phục không được vượt quá 50MB.',
            'admin_password.required' => 'Vui lòng nhập mật khẩu xác nhận.',
        ]);

        // Kiểm tra mật khẩu admin
        if (! Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->with('error', 'Mật khẩu xác nhận không chính xác. Chặn quyền khôi phục!');
        }

        $file = $request->file('restore_file');
        $sqlPath = $file->storeAs('restore_tmp', 'restore_'.now()->format('YmdHis').'.sql');
        $fullPath = Storage::path($sqlPath);

        $dbHost = config('database.connections.mysql.host', '127.0.0.1');
        $dbPort = config('database.connections.mysql.port', '3306');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        // Tìm mysql.exe (XAMPP hoặc PATH)
        $mysqlBin = 'mysql';
        $xamppMysql = 'C:\\xampp\\mysql\\bin\\mysql.exe';
        if (file_exists($xamppMysql)) {
            $mysqlBin = "\"{$xamppMysql}\"";
        }

        $passArg = $dbPass ? '-p'.escapeshellarg($dbPass) : '';
        $command = "{$mysqlBin} -h {$dbHost} -P {$dbPort} -u ".escapeshellarg($dbUser)." {$passArg} ".escapeshellarg($dbName).' < '.escapeshellarg($fullPath).' 2>&1';

        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);

        // Dọn file tạm
        Storage::delete($sqlPath);

        if ($exitCode !== 0) {
            ActivityLog::log('restore_database_failed', 'Thất bại khi khôi phục cơ sở dữ liệu: '.implode(' ', $output));

            return back()->with('error', 'Khôi phục thất bại: '.implode(' ', $output));
        }

        ActivityLog::log('restore_database', 'Khôi phục cơ sở dữ liệu thành công từ file: '.$file->getClientOriginalName());

        return back()->with('success', 'Khôi phục cơ sở dữ liệu thành công!');
    }

    /**
     * Đồng bộ lõi Portable (PHP, MariaDB, Composer) + công cụ 7-Zip SFX.
     * Chỉ tải về những file còn thiếu hoặc bị hỏng (< 10KB).
     */
    public function syncInstallers()
    {
        set_time_limit(0);

        $toolsDir = storage_path('app'.DIRECTORY_SEPARATOR.'tools');
        $installersDir = storage_path('app'.DIRECTORY_SEPARATOR.'installers');

        if (! is_dir($toolsDir)) {
            mkdir($toolsDir, 0755, true);
        }
        if (! is_dir($installersDir)) {
            mkdir($installersDir, 0755, true);
        }

        $sevenZaExe = $toolsDir.DIRECTORY_SEPARATOR.'7za.exe';

        // ── Tải lõi Portable (PHP x86, MariaDB x86, Composer) ──────────────
        $portableFiles = [
            'php.zip' => 'https://windows.php.net/downloads/releases/archives/php-8.2.19-Win32-vs16-x86.zip',
            'mariadb.zip' => 'https://archive.mariadb.org/mariadb-10.6.17/win32-packages/mariadb-10.6.17-win32.zip',
            'composer.phar' => 'https://getcomposer.org/composer.phar',
        ];

        $errors = [];
        $synced = [];

        foreach ($portableFiles as $fileName => $url) {
            $filePath = $installersDir.DIRECTORY_SEPARATOR.$fileName;
            if (! file_exists($filePath) || filesize($filePath) < 10000) {
                exec('curl.exe -L --max-time 300 -o '.escapeshellarg($filePath).' '.escapeshellarg($url).' 2>&1', $out, $code);
                if ($code !== 0 || ! file_exists($filePath) || filesize($filePath) < 10000) {
                    $errors[] = "Loi tai {$fileName}";
                } else {
                    $synced[] = $fileName;
                }
            } else {
                $synced[] = "{$fileName} (da co)";
            }
        }

        // ── Tải module 7zSD.sfx (cần cho việc tạo .exe SFX) ───────────────
        $sfxPath = $toolsDir.DIRECTORY_SEPARATOR.'7zSD.sfx';
        if (! file_exists($sfxPath) || filesize($sfxPath) < 100000) {
            if (file_exists($sevenZaExe)) {
                // 7-Zip installer là SFX 7z, dùng 7za.exe để trích xuất 7zSD.sfx
                $tempInstaller = $toolsDir.DIRECTORY_SEPARATOR.'7z-inst-tmp.exe';
                exec('curl.exe -L --max-time 120 -o '.escapeshellarg($tempInstaller).' https://www.7-zip.org/a/7z2409.exe 2>&1', $o, $c);
                if ($c === 0 && file_exists($tempInstaller) && filesize($tempInstaller) > 100000) {
                    exec('"'.$sevenZaExe.'" e "'.str_replace('/', '\\', $tempInstaller).'" 7zSD.sfx -o"'.str_replace('/', '\\', $toolsDir).'" -y 2>&1', $o2, $c2);
                    @unlink($tempInstaller);
                    if (file_exists($sfxPath) && filesize($sfxPath) > 100000) {
                        $synced[] = '7zSD.sfx';
                    } else {
                        $errors[] = 'Khong the trich xuat 7zSD.sfx tu 7-Zip installer (7za exit='.$c2.')';
                    }
                } else {
                    @unlink($tempInstaller);
                    $errors[] = 'Khong the tai 7-Zip installer (curl exit='.$c.')';
                }
            } else {
                $errors[] = '7za.exe khong ton tai trong storage/app/tools/ — can copy thu cong';
            }
        } else {
            $synced[] = '7zSD.sfx (da co)';
        }

        // ── Dọn file cũ không cần thiết ────────────────────────────────────
        foreach (['xampp-installer.exe', 'Composer-Setup.exe', 'NT-Logistics-System-Installer.exe'] as $old) {
            @unlink($installersDir.DIRECTORY_SEPARATOR.$old);
        }

        ActivityLog::log('sync_installers', 'Dong bo cai dat: '.implode(', ', $synced));

        if (! empty($errors)) {
            return back()->with('error', 'Loi dong bo: '.implode('; ', $errors).'. File OK: '.implode(', ', $synced));
        }

        return back()->with('success', 'Dong bo thanh cong: '.implode(', ', $synced).'. Co the bam "Tao bo cai (.exe)" ngay bay gio!');
    }

    /**
     * Tạo file installer .exe dạng 7-Zip SFX.
     * File được lưu tại storage/app/installers/NT-Logistics-System-Installer.exe.
     * Chỉ cần Render 1 lần, người dùng tải xuống mãi cho đến khi Render lại.
     */
    public function buildExeInstaller()
    {
        set_time_limit(600); // 10 phút tối đa

        $toolsDir = storage_path('app'.DIRECTORY_SEPARATOR.'tools');
        $installersDir = storage_path('app'.DIRECTORY_SEPARATOR.'installers');
        $buildId = 'build_'.time();
        $tempDir = storage_path('app'.DIRECTORY_SEPARATOR.$buildId);

        $sevenZaExe = file_exists($toolsDir.DIRECTORY_SEPARATOR.'7z.exe')
            ? $toolsDir.DIRECTORY_SEPARATOR.'7z.exe'
            : $toolsDir.DIRECTORY_SEPARATOR.'7za.exe';
        $sfxModule = $toolsDir.DIRECTORY_SEPARATOR.'7zSD.sfx';
        $archivePath = $tempDir.DIRECTORY_SEPARATOR.'archive.7z';
        $configPath = $tempDir.DIRECTORY_SEPARATOR.'config.txt';
        $exePath = $installersDir.DIRECTORY_SEPARATOR.'NT-Logistics-System-Installer.exe';

        // ── Kiểm tra công cụ ──────────────────────────────────────────────
        if (! file_exists($sevenZaExe)) {
            return back()->with('error', 'Thieu cong cu 7za.exe. Vui long bam "Dong bo loi" truoc.');
        }
        if (! file_exists($sfxModule)) {
            return back()->with('error', 'Thieu module 7zSD.sfx. Vui long bam "Dong bo loi" truoc.');
        }

        @mkdir($tempDir, 0755, true);
        @mkdir($installersDir, 0755, true);

        try {
            $rootPath = str_replace('/', '\\', base_path());
            $oldCwd = getcwd();

            // ── Bước 1: Nén mã nguồn vào archive.7z ──────────────────────
            $excludes = implode(' ', [
                '-xr!.git',
                '-xr!node_modules',
                '-xr!vendor',
                '-xr!storage\logs',
                '-xr!storage\framework\cache',
                '-xr!storage\framework\sessions',
                '-xr!storage\framework\testing',
                '-xr!storage\app\backups',
                '-xr!storage\app\installers',
                '-xr!storage\app\restore_tmp',
                '-xr!storage\app\backup_tmp',
                '-xr!storage\app\build_*',
                '-xr!.env',
                '-xr!.env.backup',
                '-xr!_build_exe_now.php',
                '-xr!test_*.bat',
                '-xr!test_*.php',
                '-xr!*.DDF',
                '-xr!test_installer*.exe',
            ]);

            chdir($rootPath);
            $archiveW = str_replace('/', '\\', $archivePath);
            $cmd = "\"{$sevenZaExe}\" a -t7z -mx=5 -mmt=on \"{$archiveW}\" * {$excludes} 2>&1";
            exec($cmd, $out1, $code1);
            chdir($oldCwd);

            // exit code 1 = cảnh báo (bỏ qua được), > 1 = lỗi
            if ($code1 > 1 || ! file_exists($archivePath)) {
                throw new \Exception('Loi nen ma nguon (exit '.$code1.'): '.implode(' ', array_slice($out1, -5)));
            }

            // ── Bước 2: Thêm thư mục prerequisites/ vào archive ──────────
            $prereqTemp = $tempDir.DIRECTORY_SEPARATOR.'prerequisites';
            @mkdir($prereqTemp, 0755, true);

            $validPrereqs = false;
            foreach (glob($installersDir.DIRECTORY_SEPARATOR.'*.{zip,phar}', GLOB_BRACE) as $file) {
                if (filesize($file) > 10000) {
                    copy($file, $prereqTemp.DIRECTORY_SEPARATOR.basename($file));
                    $validPrereqs = true;
                }
            }

            if ($validPrereqs) {
                chdir($tempDir);
                $cmd2 = "\"{$sevenZaExe}\" a \"{$archiveW}\" prerequisites 2>&1";
                exec($cmd2, $out2, $code2);
                chdir($oldCwd);
            }

            // ── Bước 3: Tạo config.txt cho SFX (chỉ dùng ASCII) ─────────
            $configContent = ";!@Install@!UTF-8!\r\n"
                ."Title=\"NT Logistics ERP - Bo Cai Dat All-in-One\"\r\n"
                ."BeginPrompt=\"Ban co muon cai dat NT Logistics ERP vao thu muc D:\\NT-Logistics-ERP khong?\r\nYeu cau chay voi quyen Administrator.\"\r\n"
                ."Directory=\"D:\\NT-Logistics-ERP\"\r\n"
                ."RunProgram=\"cmd.exe /c install.bat\"\r\n"
                ."GUIMode=\"2\"\r\n"
                .';!@InstallEnd@!';
            file_put_contents($configPath, $configContent);

            // ── Bước 4: Ghép SFX + config + archive → .exe ───────────────
            if (file_exists($exePath)) {
                unlink($exePath);
            }

            $sfxW = str_replace('/', '\\', $sfxModule);
            $configW = str_replace('/', '\\', $configPath);
            $exeW = str_replace('/', '\\', $exePath);

            // Dùng Windows copy /b để nối binary
            $concatCmd = 'cmd /c copy /b "'.$sfxW.'" + "'.$configW.'" + "'.$archiveW.'" "'.$exeW.'"';
            exec($concatCmd, $out3, $code3);

            if (! file_exists($exePath) || filesize($exePath) < 500000) {
                throw new \Exception('Loi ghep file .exe (exit '.$code3.'): '.implode(' | ', array_slice($out3, -3)));
            }

            $sizeMB = round(filesize($exePath) / 1024 / 1024, 1);
            ActivityLog::log('build_exe_installer', "Tao bo cai dat .exe thanh cong: {$sizeMB}MB");

            return back()->with('success', "Da tao thanh cong NT-Logistics-System-Installer.exe ({$sizeMB} MB)! Bam 'Tai bo cai' de tai ve.");

        } catch (\Exception $e) {
            return back()->with('error', 'Loi khi tao bo cai: '.$e->getMessage());
        } finally {
            if (is_dir($tempDir)) {
                $this->removeDirectory($tempDir);
            }
        }
    }

    /**
     * Tải file installer .exe đã được Render về máy người dùng.
     */
    public function downloadExeInstaller()
    {
        $exePath = storage_path('app/installers/NT-Logistics-System-Installer.exe');
        if (! file_exists($exePath)) {
            return back()->with('error', 'Chua co ban Render nao. Vui long bam "Tao bo cai (.exe)" truoc!');
        }

        ActivityLog::log('download_exe_installer', 'Tai bo cai dat NT-Logistics-System-Installer.exe');

        return response()->download($exePath, 'NT-Logistics-System-Installer.exe', [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    /**
     * Xóa bộ cài .exe đã Render để chuẩn bị Render lại.
     * (Tùy chọn — Render mới sẽ tự ghi đè.)
     */
    public function deleteExeInstaller()
    {
        $exePath = storage_path('app/installers/NT-Logistics-System-Installer.exe');
        if (file_exists($exePath)) {
            unlink($exePath);
        }

        ActivityLog::log('delete_exe_installer', 'Xoa bo cai dat .exe cu');

        return back()->with('success', 'Da xoa bo cai cu. Co the Render lai phien ban moi.');
    }

    public function uploadAsset(Request $request)
    {
        $request->validate([
            'stamp' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
        ], [
            'stamp.mimes' => 'Con dấu phải là file ảnh PNG hoặc JPG.',
            'logo.mimes' => 'Logo phải là file ảnh PNG hoặc JPG.',
            'stamp.max' => 'Con dấu không được vượt quá 2MB.',
            'logo.max' => 'Logo không được vượt quá 2MB.',
        ]);

        if (! $request->hasFile('stamp') && ! $request->hasFile('logo')) {
            return back()->with('error', 'Vui lòng chọn ít nhất một tệp để tải lên.');
        }

        if ($request->hasFile('stamp')) {
            $request->file('stamp')->move(public_path('img'), 'company-stamp.png');
        }

        if ($request->hasFile('logo')) {
            $request->file('logo')->move(public_path('img'), 'company-logo.png');
        }

        ActivityLog::log('upload_asset', 'Tải lên mẫu in ấn mới (Con dấu/Logo)');

        return back()->with('success', 'Tải lên tệp thành công! Các mẫu in sẽ sử dụng tệp mới ngay lập tức.');
    }

    /**
     * Xóa đệ quy một thư mục và toàn bộ nội dung bên trong.
     */
    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getRealPath()) : unlink($item->getRealPath());
        }

        rmdir($dir);
    }
}
