<?php

namespace Serengiy\Sluggable\Traits\Models;

use Serengiy\Sluggable\Exceptions\InvalidOptionsException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasSlug
{
    protected SlugOptions $slugOptions;
    abstract static function getOptions(): SlugOptions;
    protected static function bootHasSlug()
    {
        static::creating(function (Model $model){
            $model->generateSlugOnCreate();
        });
    }

    protected function generateSlugOnCreate()
    {
        $this->slugOptions = self::getOptions();

        if($this->slugOptions->skipSlugGenerating){
            return;
        }

        if($this->hasSlug() && !$this->isSlugExists($this->{$this->slugOptions->slugFiled})){
            return;
        }

        $this->addSlug();
    }

    private function slugOrNull(): string|null
    {
        if(strlen($this->{$this->slugOptions->slugFiled}) > 0 && isset($this->{$this->slugOptions->slugFiled})){
            return $this->{$this->slugOptions->slugFiled};
        }
        return null;
    }

    protected function addSlug(): void
    {
        $this->verifySlugOptions();
        $slug = $this->slugOrNull() ?? $this->makeNoneUniqueSlug();

        if($this->slugOptions->makeSlugUnique){
            $slug = $this->makeUniqueSlug($slug);
        }

        $slugField = $this->slugOptions->slugFiled;
        $this->{$slugField} = $slug;
    }
    private function hasSlug():bool
    {
        return (bool) ($this->{$this->slugOptions->slugFiled});
    }
    private function verifySlugOptions():void
    {

        if($this->slugOptions->maxLength <=0 ){
            throw new InvalidOptionsException('Length of slug cannot be 0 or lower');
        }
    }

    private function makeNoneUniqueSlug():string
    {
        $slug = Str::slug($this->{$this->slugOptions->slugFrom}, $this->slugOptions->separator);
        $this->{$this->slugOptions->slugFiled} = $slug;
        return $slug;
    }

    private function makeUniqueSlug($slug): string
    {
        if($this->isSlugExists($slug)){
            $slug = $this->addSuffix($slug);
        }
        return $slug;
    }

    private function isSlugExists($slug): bool
    {
        $slugQuery = $this->query()->where(
            $this->slugOptions->slugFiled,
            $slug
        );

        return $slugQuery->exists();
    }

    private function addSuffix($slug):string
    {
        $i = $this->slugOptions->slugSuffix;
        while($this->isSlugExists($slug)){
            $slug = $this->{$this->slugOptions->slugFiled}.$this->slugOptions->separator.$i++;
        }

        return $slug;
    }

}
