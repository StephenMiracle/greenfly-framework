<?php



namespace Greenfly\Modules\Content\Models;

 

use Greenfly\Modules\Model;



class Content extends Model

{


    protected $fillable = ['name', 'type_name'];


    /**
     * Get all the versions associated with a content item. The data attribute will also be converted to an array.
     *
     * @return mixed
     */
    public function versions()

    {


        try {

            $versions = Version::where('content_name', $this->getAttributeValue('name'))->get();

            foreach ($versions as $version) {

                $version->data = json_decode($version->getAttributeValue('data'), true);

            }

            return $versions;

        } catch (\Exception $e) {

            echo $e; die;

        }


    }


    /**
     * get the Type entity associated for a particular Content item.
     *
     * @param $contentEntity
     * @return mixed
     */
    public function type($contentEntity)

    {


        return Type::where('name', $contentEntity->type_name)->firstOrFail();


    }




    /**
     * Attaches related taxonomies to content entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taxonomies()

    {


        return $this->hasMany('Greenfly\Modules\Content\Models\Taxonomy');


    }




    /**
     * Attaches tags to a content entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()

    {


        return $this->belongsToMany('Greenfly\Modules\Content\Models\Tag');


    }


    /**
     * @param array $fields
     *
     * @return mixed
     */
    public function latestVersion(array $fields = [])

    {


            if (!isset($fields['content_name'])) {


                $fields['content_name'] = $this->getAttributeValue('name');


            }


            $version = Version::where($fields)->orderBy('published_date', 'DESC')->first()->toArray();



        $version['data'] = json_decode($version['data'], true);


        return $version;


    }



}