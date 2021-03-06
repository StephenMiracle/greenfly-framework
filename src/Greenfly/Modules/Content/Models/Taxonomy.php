<?php



namespace Greenfly\Modules\Content\Models;



use Greenfly\Modules\Model;



class Taxonomy extends Model

{

    protected $fillable = ['name']; 



    public function types()

    {

        return $this->belongsToMany('Greenfly\Modules\Content\Models\Type');

    }



    public function tags()

    {
        $tags = Tag::where('taxonomy_name', $this->getAttributeValue('name'))->get()->toArray();
        return $this->hasMany('Greenfly\Modules\Content\Models\Tag');

    }

}