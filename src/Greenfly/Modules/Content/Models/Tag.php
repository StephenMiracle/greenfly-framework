<?php



namespace Greenfly\Modules\Content\Models;



use Greenfly\Modules\Model;



class Tag extends Model

{

    protected $fillable = ['name', 'taxonomy_name'];

    protected $typeName = 'type_name';

    public $currentContentName;
 
    public $typerName;

    public $offset;

    public $take;


    /**
     * get the taxonomy entity associated with a tag.
     *
     * @return mixed
     */
    public function taxonomy()

    {


        return Taxonomy::where('taxonomy_name', $this->taxonomy_name)->firstOrFail();


    }


    /**
     *
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contents()

    {

        $contents = $this->belongsToMany('Greenfly\Modules\Content\Models\Content');



        if (!empty($this->typerName)) {

            $contents->where('type_name', $this->typeName);

        }



        if (!empty($this->currentContentName)) {

            $contents->where('name', '!=', $this->currentContentName);

        }

        $contents->orderBy('published_date', 'DESC');



        if (!empty($this->take)) {

            $contents->take($this->take);



            if (!empty($this->offset)) {

                $contents->offset($this->offset);

            }

        }



        return $contents;


    }




    public function getContentWithVersion()

    {

        $contents = $this->contents();



        foreach ($contents as $content) {

            $content->version = $content->latestVersion();

        }


        return $contents;


    }




    public function getContents()

    {

        $contentQuery = $this->belongsToMany('Greenfly\Modules\Content\Models\Content');


        if (!empty($this->typerName)) {

            $contentQuery = $contentQuery->where('type_name', $this->typeName);

        }


        if (!empty($this->take)) {

            $contentQuery = $contentQuery->take($this->take);


            if (!empty($this->offset)) {

                $contentQuery = $contentQuery->offset($this->offset);

            }


        }


        $contentQuery->orderBy('published_date', 'DESC');


        return $contentQuery;


    }




}
