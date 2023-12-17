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
if (!\function_exists('appurl')) {
    function appurl()
    {
        return config('app.url');
    }
}

if (!\function_exists('href_profile')) {
    function href_profile($user)
    {
        $appurl = appurl();

        return sprintf('%s/users/%s', $appurl, $user->username);
    }
}

if (!\function_exists('href_article')) {
    function href_article($article)
    {
        $appurl = appurl();

        return sprintf('%s/articles/%s', $appurl, $article->id);
    }
}

if (!\function_exists('href_torrent')) {
    function href_torrent($torrent)
    {
        $appurl = appurl();

        return sprintf('%s/torrents/%s', $appurl, $torrent->id);
    }
}

if (!\function_exists('href_request')) {
    function href_request($torrentRequest)
    {
        $appurl = appurl();

        return sprintf('%s/requests/%s', $appurl, $torrentRequest->id);
    }
}

if (!\function_exists('href_poll')) {
    function href_poll($poll)
    {
        $appurl = appurl();

        return sprintf('%s/polls/%s', $appurl, $poll->id);
    }
}

if (!\function_exists('href_playlist')) {
    function href_playlist($playlist)
    {
        $appurl = appurl();

        return sprintf('%s/playlists/%s', $appurl, $playlist->id);
    }
}

if (!\function_exists('href_collection')) {
    function href_collection($collection)
    {
        $appurl = appurl();

        return sprintf('%s/mediahub/collections/%s', $appurl, $collection->id);
    }
}

if (!\function_exists('tmdb_image')) {
    function tmdb_image($type, $original)
    {
        $new = match ($type) {
            'back_big'     => 'w1280',
            'back_small'   => 'w780',
            'poster_big'   => 'w500',
            'poster_mid'   => 'w342',
            'poster_small' => 'w92',
            'cast_face'    => 'w138_and_h175_face',
            'cast_mid'     => 'w185',
            'cast_big'     => 'w300',
            'still_mid'    => 'w400',
            'logo_small'   => 'h60',
            'logo_mid'     => 'w300',
            default        => 'original',
        };

        return str_replace('/original/', '/'.$new.'/', (string) $original);
    }
}

if (!\function_exists('modal_style')) {
    function modal_style()
    {
        return (auth()->user()->style == 0) ? '' : ' modal-dark';
    }
}

if (!\function_exists('rating_color')) {
    function rating_color($number)
    {
        $rating = round((float) $number);

        if ($rating > 0 && $rating <= 4) {
            return 'text-danger';
        }

        if ($rating >= 4 && $rating <= 7) {
            return 'text-warning';
        }

        if ($rating >= 7 && $rating <= 10) {
            return 'text-success';
        }
    }
}

if (!\function_exists('language_flag')) {
    function language_flag($language)
    {
        $flag = match ($language) {
            'English', 'English (US)' => 'us',
            'English (GB)' => 'gb',
            'English (CA)' => 'can',
            'English (AU)' => 'au',
            'Albanian', 'Albanian (AL)' => 'al',
            'Arabic', 'Arabic (001)', 'Arabic (AE)' => 'ae',
            'Arabic (SA)' => 'sa',
            'Arabic (MA)' => 'ma',
            'Armenian'    => 'am',
            'Azerbaijani' => 'az',
            'Belarusian'  => 'by',
            'Bengali'     => 'bd',
            'Bosnian', 'Bosnian (BA)' => 'ba',
            'Bulgarian', 'Bulgarian (BG)' => 'bg',
            'Burmese' => 'mm',
            'Chinese', 'Mandarin', 'Mandarin (Hans)', 'Mandarin (Hant)', 'Cantonese', 'Cantonese (Hant)', 'Chinese (Simplied)', 'Chinese (Traditional)', 'Chinese (Simplified)', 'Chinese-yue-Hant', 'Chinese-cmn-Hans', 'Chinese-cmn-Hant' => 'cn',
            'Chinese (HK)', 'Chinese-Hant-HK', 'Mandarin (HK)', 'Cantonese (HK)', 'Chinese-cmn-HK' => 'hk',
            'Chinese (Taiwan)' => 'tw',
            'Croatian', 'Croatian (HR)' => 'hr',
            'Czech', 'Czech (CZ)' => 'cz',
            'Danish', 'Danish (DK)' => 'dk',
            'Dutch', 'Dutch (NL)', 'Limburgish' => 'nl',
            'Dutch (BE)' => 'be',
            'Estonian', 'Estonian (EE)' => 'ee',
            'Finnish', 'Finnish (FI)' => 'fi',
            'French', 'French (FR)' => 'fr',
            'French (CA)' => 'can-qc',
            'Georgian'    => 'ge',
            'German', 'German (DE)' => 'de',
            'German (CH)' => 'ch',
            'Greek', 'Greek (GR)' => 'gr',
            'Hebrew', 'Hebrew (IL)' => 'il',
            'Hindi', 'Tamil', 'Telugu', 'Hindi (IN)', 'Tamil (IN)', 'Telugu (IN)', 'Kannada', 'Kannada (IN)', 'Malayalam', 'Malayalam (IN)', 'Marathi', 'Marathi (IN)' => 'in',
            'Hungarian', 'Hungarian (HU)' => 'hu',
            'Icelandic', 'Icelandic (IS)' => 'is',
            'Indonesian', 'Indonesian (ID)' => 'id',
            'Irish', 'Irish (IE)' => 'ie',
            'Italian', 'Italian (IT)' => 'it',
            'Japanese', 'Japanese (JP)' => 'jp',
            'Kazakh', 'Kazakh (KZ)' => 'kz',
            'Korean', 'Korean (KR)' => 'kr',
            'Latvian', 'Latvian (LV)' => 'lv',
            'Lithuanian', 'Lithuanian (LT)' => 'lt',
            'Malay', 'Malay (MY)' => 'my',
            'Malay (SG)' => 'sg',
            'Macedonian', 'Macedonian (MK)' => 'mk',
            'Mongolian' => 'mn',
            'Norwegian', 'Norwegian Bokmal', 'Norwegian (NO)', 'Norwegian Bokmal (NO)', 'Norwegian Nynorsk', 'Norwegian Nynorsk (NO)' => 'no',
            'Persian' => 'ir',
            'Polish', 'Polish (PL)' => 'pl',
            'Portuguese', 'Portuguese (PT)' => 'pt',
            'Portuguese (BR)' => 'br',
            'Romanian', 'Romanian (RO)' => 'ro',
            'Russian', 'Russian (RU)' => 'ru',
            'Serbian', 'Serbian-Latn-RS', 'Serbian (RS)' => 'rs',
            'Sinhala' => 'lk',
            'Slovak', 'Slovak (SK)' => 'sk',
            'Slovenian', 'Slovenian (SI)' => 'si',
            'Spanish', 'Spanish (ES)', 'Spanish (CA)', 'Spanish (EU)', 'Spanish (150)' => 'es',
            'Spanish (Latin America)', 'Spanish (LA)', 'Spanish (MX)' => 'mx',
            'Spanish (AR)' => 'ar',
            'Basque', 'Basque (ES)' => 'es-pv',
            'Catalan', 'Catalan (ES)' => 'es-ct',
            'Galician', 'Galician (ES)' => 'es-ga',
            'Swedish', 'Swedish (SE)' => 'se',
            'Tagalog', 'fil', 'fil (PH)', 'Filipino' , 'Filipino (PH)' => 'ph',
            'Thai', 'Thai (TH)' => 'th',
            'Turkish', 'Turkish (TR)' => 'tr',
            'Ukrainian', 'Ukrainian (UA)' => 'ua',
            'Vietnamese', 'Vietnamese (VN)' => 'vn',
            'Welsh' => 'gb-wls',
            default => null,
        };

        return $flag !== null ? '/img/flags/'.$flag.'.png' : null;
    }
}
