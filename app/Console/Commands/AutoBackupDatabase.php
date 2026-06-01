<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AutoBackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-backup-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically backup database based on settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $frequency = Setting::where('key', 'auto_backup_interval')->value('value') ?? 'off';

        if ($frequency === 'off') {
            $this->info('Auto backup is disabled.');

            return;
        }

        $lastBackupStr = Setting::where('key', 'system.last_auto_backup')->first()?->value;
        $lastBackup = $lastBackupStr ? Carbon::createFromFormat('d/m/Y H:i', $lastBackupStr) : null;

        $shouldBackup = false;
        $now = now();

        if (! $lastBackup) {
            $shouldBackup = true;
        } else {
            $shouldBackup = match ($frequency) {
                'daily' => $now->diffInHours($lastBackup) >= 24,
                '3_days' => $now->diffInDays($lastBackup) >= 3,
                'weekly' => $now->diffInDays($lastBackup) >= 7,
                'monthly' => $now->diffInDays($lastBackup) >= 30,
                default => false,
            };
        }

        if (! $shouldBackup) {
            $this->info('Not time to backup yet. Last backup: '.($lastBackupStr ?: 'None'));

            return;
        }

        $this->info('Starting database backup...');

        $dbHost = config('database.connections.mysql.host', '127.0.0.1');
        $dbPort = config('database.connections.mysql.port', '3306');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $mysqldumpBin = 'mysqldump';
        $xamppMysqldump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        if (file_exists($xamppMysqldump)) {
            $mysqldumpBin = "\"{$xamppMysqldump}\"";
        }

        $passArg = $dbPass ? '-p'.escapeshellarg($dbPass) : '';
        $filename = 'auto_backup_logistics_'.date('Ymd_His').'.sql';

        if (! Storage::exists('backups')) {
            Storage::makeDirectory('backups');
        }

        $fullPath = Storage::path('backups/'.$filename);
        $command = "{$mysqldumpBin} -h {$dbHost} -P {$dbPort} -u ".escapeshellarg($dbUser)." {$passArg} ".escapeshellarg($dbName).' > '.escapeshellarg($fullPath).' 2>&1';

        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error('Backup failed: '.implode(' ', $output));
            ActivityLog::log('auto_backup_database_failed', 'Lỗi tự động sao lưu: '.implode(' ', $output));

            return;
        }

        // Cập nhật thời gian sao lưu
        Setting::updateOrCreate(
            ['key' => 'system.last_auto_backup'],
            ['value' => $now->format('d/m/Y H:i'), 'group' => 'system']
        );

        ActivityLog::log('auto_backup_database', 'Hệ thống tự động sao lưu dữ liệu thành công.');
        $this->info('Backup created successfully: '.$filename);
    }
}
