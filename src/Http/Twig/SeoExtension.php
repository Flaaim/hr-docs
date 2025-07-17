<?php

namespace App\Http\Twig;

use App\Http\Seo\SeoManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SeoExtension extends AbstractExtension
{
    protected SeoManager $manager;

    public function __construct(SeoManager $manager)
    {
        $this->manager = $manager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('seo', [$this, 'getSeoData']),
            new TwigFunction('seo_logo', [$this, 'getLogo']),
            new TwigFunction('seo_title', [$this, 'getTitle']),
            new TwigFunction('seo_description', [$this, 'getDescription']),
            new TwigFunction('seo_keywords', [$this, 'getKeywords']),
        ];
    }

    public function getSeoData(string $key = '', $default = ''): string|array
    {
        if ($key === '') {
            return $this->manager->getAll();
        }

        return $this->manager->get($key, $default);
    }
    public function getLogo(): string
    {
        return $this->manager->get('logo');
    }
    public function getTitle(): string
    {
        return $this->manager->get('title');
    }
    public function getDescription(): string
    {
        return $this->manager->get('description');
    }
    public function getKeywords(): string
    {
        return $this->manager->get('keywords');
    }
}
