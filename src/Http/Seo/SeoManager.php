<?php

namespace App\Http\Seo;

class SeoManager
{
    protected array $data = [];
    protected array $defaults = [];
    public function __construct(array $defaults = [])
    {
        $this->data = array_merge([
            'logo' => '',
            'title' => '',
            'description' => '',
            'keywords' => '',
            'canonical' => '',
            'robots' => 'index,follow',
        ], $defaults);
    }
    public function set(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function get(string $key, $default = null): string
    {
        return $this->data[$key] ?? $this->defaults[$key] ?? $default ?? '';
    }

    public function setTitle(string $title): self
    {
        $this->data['title'] = $title;
        return $this;
    }
    public function setDescription(string $description): self
    {
        $this->data['description'] = $description;
        return $this;
    }
    public function setKeywords(string $keywords): self
    {
        $this->data['keywords'] = $keywords;
        return $this;
    }
    public function setCanonical(string $canonical): self
    {
        $this->data['canonical'] = $canonical;
        return $this;
    }
    public function setRobots(string $robots): self
    {
        $this->data['robots'] = $robots;
        return $this;
    }
    public function getAll(): array
    {
        return array_merge($this->defaults, $this->data);
    }
}
