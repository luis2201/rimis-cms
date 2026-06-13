<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\File;

class HtmlSanitizer
{
    public function clean(string $html): string
    {
        File::ensureDirectoryExists(storage_path('framework/cache/htmlpurifier'));

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', storage_path('framework/cache/htmlpurifier'));
        $config->set('HTML.Allowed', 'p[style|class],br,h2[style|class],h3[style|class],h4[style|class],h5[style|class],h6[style|class],strong,b,em,i,u,s,blockquote[style|class],ul[style|class],ol[style|class],li[style|class],a[href|target|rel|class|style],img[src|alt|title|width|height|class|style],table[style|class|width],thead,tbody,tr[style|class],th[style|class|width|height],td[style|class|width|height],hr,span[class|style],div[class|style]');
        $config->set('CSS.AllowedProperties', [
            'text-align',
            'color',
            'background-color',
            'font-size',
            'font-weight',
            'font-style',
            'text-decoration',
            'width',
            'height',
            'max-width',
            'margin',
            'margin-left',
            'margin-right',
            'padding',
            'border',
            'border-collapse',
            'float',
        ]);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('URI.DisableExternalResources', false);
        $config->set('URI.DisableResources', false);

        return (new HTMLPurifier($config))->purify($html);
    }
}
