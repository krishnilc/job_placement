<?php

namespace App\Console\Commands;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Phar;
use PharData;
use RuntimeException;

class BuildUserManuals extends Command
{
    protected $signature = 'manuals:build';

    protected $description = 'Build the public and role-specific PDF and editable Word user manuals';

    public function handle(): int
    {
        $manuals = require base_path('docs/manuals.php');
        $directory = base_path('docs/manuals');
        File::ensureDirectoryExists($directory);

        foreach ($manuals as $key => $manual) {
            $path = $directory.'/FNU-Job-Placement-'.$key.'-Manual';
            Pdf::loadView('manuals.pdf', ['manual' => $manual])
                ->setPaper('a4')->setOption('isRemoteEnabled', false)
                ->setOption('isPhpEnabled', true)->save($path.'.pdf');
            $this->writeWord($path.'.docx', $manual);
            $this->info('Built '.$manual['title'].' (PDF and Word).');
        }

        return self::SUCCESS;
    }

    private function writeWord(string $path, array $manual): void
    {
        $paragraph = function (string $text, string $style = 'Normal'): string {
            return '<w:p><w:pPr><w:pStyle w:val="'.$style.'"/></w:pPr><w:r><w:t xml:space="preserve">'
                .htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</w:t></w:r></w:p>';
        };
        $body = $paragraph('FNU Job Placement', 'Subtitle')
            .$paragraph($manual['title'], 'Title')
            .$paragraph($manual['audience'], 'Subtitle')
            .$paragraph('Version 2.0 | 14 October 2026')
            .$paragraph('Use the portal address supplied by FNU. Available actions depend on your account role.')
            .$paragraph('In Word, open View > Navigation Pane to jump between section headings. This document is editable.')
            .$paragraph('Contents', 'Heading1');

        foreach (array_keys($manual['sections']) as $index => $heading) {
            $body .= $paragraph(($index + 1).'. '.$heading);
        }
        foreach ($manual['sections'] as $heading => $paragraphs) {
            $body .= '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';
            $body .= $paragraph($heading, 'Heading1');
            foreach ($paragraphs as $text) {
                $body .= $paragraph($text);
            }
        }

        // DOCX is an Open XML ZIP package; Phar keeps generation dependency-free.
        $temporary = $path.'.zip';
        if (is_file($temporary)) {
            throw new RuntimeException('Remove the stale manual build file before retrying: '.$temporary);
        }
        try {
            $zip = new PharData($temporary, 0, null, Phar::ZIP);
            $zip['[Content_Types].xml'] = '<?xml version="1.0" encoding="UTF-8"?>'
                .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                .'<Default Extension="xml" ContentType="application/xml"/>'
                .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
                .'<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/></Types>';
            $zip['_rels/.rels'] = '<?xml version="1.0" encoding="UTF-8"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>';
            $zip['word/_rels/document.xml.rels'] = '<?xml version="1.0" encoding="UTF-8"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
            $zip['word/document.xml'] = '<?xml version="1.0" encoding="UTF-8"?>'
                .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'
                .$body.'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/>'
                .'<w:pgMar w:top="1000" w:right="1000" w:bottom="1000" w:left="1000"/></w:sectPr></w:body></w:document>';
            $zip['word/styles.xml'] = '<?xml version="1.0" encoding="UTF-8"?>'
                .'<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
                .'<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/>'
                .'<w:pPr><w:spacing w:after="180"/></w:pPr><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:sz w:val="22"/></w:rPr></w:style>'
                .'<w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:basedOn w:val="Normal"/>'
                .'<w:rPr><w:b/><w:color w:val="163B65"/><w:sz w:val="48"/></w:rPr></w:style>'
                .'<w:style w:type="paragraph" w:styleId="Subtitle"><w:name w:val="Subtitle"/><w:basedOn w:val="Normal"/>'
                .'<w:rPr><w:color w:val="666666"/><w:sz w:val="26"/></w:rPr></w:style>'
                .'<w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/>'
                .'<w:pPr><w:keepNext/><w:outlineLvl w:val="0"/></w:pPr>'
                .'<w:rPr><w:b/><w:color w:val="163B65"/><w:sz w:val="32"/></w:rPr></w:style></w:styles>';
            unset($zip);
            if (! rename($temporary, $path)) {
                throw new RuntimeException('Unable to save Word manual: '.$path);
            }
        } finally {
            if (is_file($temporary)) {
                File::delete($temporary);
            }
        }
    }
}
