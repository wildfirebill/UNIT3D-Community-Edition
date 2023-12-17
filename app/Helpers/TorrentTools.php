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

class TorrentTools
{
    /**
     * Name of the file to be saved.
     */
    public static string $fileName = '';

    /**
     * Representative table of the decoded torrent.
     */
    public static array $decodedTorrent = [];

    /**
     * Moves and decodes the torrent.
     */
    public static function normalizeTorrent($torrentFile)
    {
        $result = Bencode::bdecode_file($torrentFile);

        // Whitelisted keys
        $result = array_intersect_key($result, [
            'comment'    => '',
            'created by' => '',
            'encoding'   => '',
            'info'       => '',
        ]);
        $result['info'] = array_intersect_key($result['info'], [
            'files'        => '',
            'length'       => '',
            'name'         => '',
            'piece length' => '',
            'pieces'       => '',
        ]);

        $result['info']['source'] = config('torrent.source');
        $result['info']['private'] = 1;

        if (config('torrent.created_by_append') && \array_key_exists('created by', $result)) {
            $result['created by'] = trim((string) $result['created by'], '. ').'. '.config('torrent.created_by', '');
        } else {
            $result['created by'] = config('torrent.created_by', '');
        }

        $comment = config('torrent.comment');

        if ($comment !== null) {
            $result['comment'] = $comment;
        }

        return $result;
    }

    /**
     * Calculate the number of files in the torrent.
     */
    public static function getFileCount($decodedTorrent): int
    {
        // Multiple file torrent ?
        if (\array_key_exists('files', $decodedTorrent['info']) && (is_countable($decodedTorrent['info']['files']) ? \count($decodedTorrent['info']['files']) : 0)) {
            return is_countable($decodedTorrent['info']['files']) ? \count($decodedTorrent['info']['files']) : 0;
        }

        return 1;
    }

    /**
     * Returns the size of the torrent files.
     */
    public static function getTorrentSize($decodedTorrent): mixed
    {
        $size = 0;

        if (\array_key_exists('files', $decodedTorrent['info']) && (is_countable($decodedTorrent['info']['files']) ? \count($decodedTorrent['info']['files']) : 0)) {
            foreach ($decodedTorrent['info']['files'] as $file) {
                $dir = '';
                $size += $file['length'];
                $count = is_countable($file['path']) ? \count($file['path']) : 0;
            }
        } else {
            $size = $decodedTorrent['info']['length'];
            //$files[0] = $decodedTorrent['info']['name.utf-8'];
        }

        return $size;
    }

    /**
     * Returns the torrent file list.
     */
    public static function getTorrentFiles($decodedTorrent): array
    {
        $files = [];

        if (\array_key_exists('files', $decodedTorrent['info']) && (is_countable($decodedTorrent['info']['files']) ? \count($decodedTorrent['info']['files']) : 0)) {
            foreach ($decodedTorrent['info']['files'] as $k => $file) {
                $dir = '';
                $count = is_countable($file['path']) ? \count($file['path']) : 0;

                for ($i = 0; $i < $count; $i++) {
                    if ($i + 1 === $count) {
                        $fname = $dir.$file['path'][$i];
                        $files[$k]['name'] = $fname;
                    } else {
                        $dir .= $file['path'][$i].'/';
                        $files[$k]['name'] = $dir;
                    }

                    $files[$k]['size'] = $file['length'];
                }
            }
        } else {
            $files[0]['name'] = $decodedTorrent['info']['name'];
            $files[0]['size'] = $decodedTorrent['info']['length'];
        }

        return $files;
    }

    /**
     * Returns file and folder names from the torrent.
     */
    public static function getFilenameArray($decodedTorrent): array
    {
        $filenames = [];

        if (\array_key_exists('files', $decodedTorrent['info']) && (is_countable($decodedTorrent['info']['files']) ? \count($decodedTorrent['info']['files']) : 0)) {
            foreach ($decodedTorrent['info']['files'] as $file) {
                $count = is_countable($file['path']) ? \count($file['path']) : 0;

                for ($i = 0; $i < $count; $i++) {
                    if (!\in_array($file['path'][$i], $filenames)) {
                        $filenames[] = $file['path'][$i];
                    }
                }
            }
        } else {
            $filenames[] = $decodedTorrent['info']['name'];
        }

        return $filenames;
    }

    /**
     * Returns the sha1 (hash) of the torrent.
     */
    public static function getTorrentHash($decodedTorrent): string
    {
        return sha1((string) Bencode::bencode($decodedTorrent['info']));
    }

    /**
     * Returns the number of the torrent file.
     */
    public static function getTorrentFileCount($decodedTorrent): int
    {
        if (\array_key_exists('files', $decodedTorrent['info'])) {
            return is_countable($decodedTorrent['info']['files']) ? \count($decodedTorrent['info']['files']) : 0;
        }

        return 1;
    }

    /**
     * Returns the NFO.
     */
    public static function getNfo($inputFile): bool|string|null
    {
        $fileName = uniqid('', true).'.nfo';
        $inputFile->move(getcwd().'/files/tmp/', $fileName);

        if (file_exists(getcwd().'/files/tmp/'.$fileName)) {
            $fileContent = file_get_contents(getcwd().'/files/tmp/'.$fileName);
            unlink(getcwd().'/files/tmp/'.$fileName);
        } else {
            $fileContent = null;
        }

        return $fileContent;
    }

    /**
     * Check if the filename is valid or not.
     */
    public static function isValidFilename(string $filename): bool
    {
        return !(
            \strlen($filename) > 255
            // nodes containing: `\`, `/`, `?`, `<`, `>`, `:`, `8`, `|`, and ascii characters from 0 through 31
            || preg_match('/[\\\\\\/?<>:*|"\x00-\x1f]/', $filename)
            // nodes only containing one or many: `.`; or only containing one or many `.`, ` `.
            || preg_match('/(^\\.+|[. ]+)$/', $filename)
            // Special windows filenames.
            || preg_match('/^(con|prn|aux|nul|com\d|lpt\d)(\\..*)?$/i', $filename)
            // BitComet padding files
            || preg_match('/^\.?____padding.*$/i', $filename)
            // BEP 47 torrent padding files that many clients aren't able to handle
            || str_starts_with($filename, '.pad')
        );
    }

    /**
     * Anonymize A Torrent Media Info.
     */
    public static function anonymizeMediainfo($mediainfo): array|string|null
    {
        if ($mediainfo === null) {
            return null;
        }

        $completeNameI = strpos((string) $mediainfo, 'Complete name');

        if ($completeNameI !== false) {
            $pathI = strpos((string) $mediainfo, ': ', $completeNameI);

            if ($pathI !== false) {
                $pathI += 2;
                $endI = strpos((string) $mediainfo, "\n", $pathI);
                $path = substr((string) $mediainfo, $pathI, $endI - $pathI);
                $newPath = MediaInfo::stripPath($path);

                return substr_replace((string) $mediainfo, $newPath, $pathI, \strlen($path));
            }
        }

        return $mediainfo;
    }

    /**
     * Parse Torrent Keywords.
     */
    public static function parseKeywords($text): array
    {
        return array_filter(array_unique(array_map('trim', explode(',', (string) $text))));
    }
}
