<?php

namespace App\Enums;

enum ProspectStatus: string
{
    case Nouveau = 'nouveau';
    case Contacte = 'contacte';
    case Engage = 'engage';
    case DemoEssai = 'demo_essai';
    case Client = 'client';
    case Perdu = 'perdu';

    public function label(): string
    {
        return match ($this) {
            self::Nouveau => 'Nouveau', self::Contacte => 'Contacté', self::Engage => 'Engagé',
            self::DemoEssai => 'Démo / Essai', self::Client => 'Client', self::Perdu => 'Perdu',
        };
    }

    public function allowsAutomaticTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return true;
        }
        if (in_array($this, [self::Client, self::Perdu], true) || $target === self::Perdu) {
            return false;
        }
        $pipeline = [self::Nouveau, self::Contacte, self::Engage, self::DemoEssai, self::Client];

        return array_search($target, $pipeline, true) > array_search($this, $pipeline, true);
    }
}
