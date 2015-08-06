<?php



namespace Greenfly\Modules\Content\Models;



use Greenfly\Modules\Model;



class Type extends Model

{

    protected $fillable = ['name', 'data'];




    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function content()

    {

        return $this->hasMany('Greenfly\Modules\Content\Models\Type\Content');

    }




    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function taxonomies()

    {


        return $this->belongsToMany('Greenfly\Modules\Content\Models\Taxonomy');


    }

}