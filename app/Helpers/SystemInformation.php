<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     HDVinnie <hdinnovations@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace App\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class SystemInformation
{
    /**
     * @var string[]
     */
    private const UNITS = ['B', 'KiB', 'MiB', 'GiB', 'TiB'];

    /**
     * @var string[]
     */
    private const KNOWN_DATABASES = [
        'sqlite',
        'mysql',
        'pgsql',
        'sqlsrv',
    ];

    public function avg()
    {
        if (is_readable('/proc/loadavg')) {
            $loads = explode(' ', file_get_contents('/proc/loadavg'));

            return [
                '1-minute'  => $loads[0],
                '5-minute'  => $loads[1],
                '15-minute' => $loads[2],
            ];
        }
    }

    public function memory(): array
    {
        if (is_readable('/proc/meminfo')) {
            $content = file_get_contents('/proc/meminfo');
            preg_match('#^MemTotal: \s*(\d*)#m', (string) $content, $matches);
            $total = ((int) $matches[1]) * 1_024;
            preg_match('/^MemAvailable: \s*(\d*)/m', $content, $matches);
            $available = ((int) $matches[1]) * 1_024;

            return [
                'total'     => $this->formatBytes($total),
                'available' => $this->formatBytes($available),
                'used'      => $this->formatBytes($total - $available),
            ];
        }

        return [
            'total'     => 0,
            'available' => 0,
            'used'      => 0,
        ];
    }

    protected function formatBytes($bytes, $precision = 2): string
    {
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1_024));
        $pow = min($pow, (\count(self::UNITS)) - 1);
        // Uncomment one of the following alternatives
        $bytes /= 1_024 ** $pow;
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.self::UNITS[$pow];
    }

    public function disk(): array
    {
        $total = disk_total_space(base_path());
        $free = disk_free_space(base_path());

        return [
            'total' => $this->formatBytes($total),
            'free'  => $this->formatBytes($free),
            'used'  => $this->formatBytes($total - $free),
        ];
    }

    public function uptime()
    {
        if (is_readable('/proc/uptime')) {
            return (float) file_get_contents('/proc/uptime');
        }
    }

    public function systemTime(): Carbon
    {
        return Carbon::now();
    }

    public function basic(): array
    {
        return [
            'os'       => PHP_OS,
            'php'      => PHP_VERSION,
            'database' => $this->getDatabase(),
            'laravel'  => app()->version(),
        ];
    }

    private function getDatabase(): string
    {
        if (!\in_array(config('database.default'), self::KNOWN_DATABASES, true)) {
            return 'Unkown';
        }

        return DB::select('select version()')[0]->{'version()'};
    }

    /**
     * Get all the directory permissions as well as the recommended ones.
     */
    public function directoryPermissions(): array
    {
        return [
            [
                'directory'   => base_path('bootstrap/cache'),
                'permission'  => $this->getDirectoryPermission('bootstrap/cache'),
                'recommended' => '0775',
            ],
            [
                'directory'   => base_path('public'),
                'permission'  => $this->getDirectoryPermission('public'),
                'recommended' => '0775',
            ],
            [
                'directory'   => base_path('storage'),
                'permission'  => $this->getDirectoryPermission('storage'),
                'recommended' => '0775',
            ],
            [
                'directory'   => base_path('vendor'),
                'permission'  => $this->getDirectoryPermission('vendor'),
                'recommended' => '0775',
            ],
        ];
    }

    /**
     * Get the file permissions for a specific path/file.
     */
    public function getDirectoryPermission($path): string|\Symfony\Component\Translation\TranslatorInterface
    {
        try {
            return substr(sprintf('%o', fileperms(base_path($path))), -4);
        } catch (Exception) {
            return trans('site.error');
        }
    }
}
