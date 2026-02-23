<?php

namespace Webteractive\FilamentPassport\Forms\Actions;

use Illuminate\Support\Js;
use Filament\Forms\Components\TextInput\Actions\CopyAction;

class CopyWithFallbackAction extends CopyAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->alpineClickHandler(function (mixed $state): string {
            $copyableState = Js::from((string) $state);
            $copyMessageJs = Js::from($this->getCopyMessage($state));
            $copyMessageDurationJs = Js::from($this->getCopyMessageDuration($state));

            return <<<JS
                const text = {$copyableState}

                if (window.navigator?.clipboard?.writeText) {
                    window.navigator.clipboard.writeText(text)
                } else {
                    const textarea = document.createElement('textarea')
                    textarea.value = text
                    textarea.setAttribute('readonly', '')
                    textarea.style.position = 'fixed'
                    textarea.style.top = '-9999px'
                    document.body.appendChild(textarea)
                    textarea.select()
                    document.execCommand('copy')
                    document.body.removeChild(textarea)
                }

                \$tooltip({$copyMessageJs}, {
                    theme: \$store.theme,
                    timeout: {$copyMessageDurationJs},
                })
                JS;
        });
    }
}
