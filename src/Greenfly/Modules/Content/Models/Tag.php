<?php



namespace Greenfly\Modules\Content\Models;



use Greenfly\Modules\Model;



class Tag extends Model

{

    protected $fillable = ['name', 'taxonomy_name'];

    protected $typeName;

    public $currentContentName;
 
    public $typerName;

    public $offset;

    public $take;

    public $shuffle;


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



        if (!empty($this->typeName)) {

            $contents = $contents->where('type_name', $this->typeName);

        }




        if (!empty($this->currentContentName)) {

            $contents = $contents->where('name', '!=', $this->currentContentName);

        }


        if (!empty($this->take)) {

            $contents = $contents->take($this->take);



            if (!empty($this->offset)) {

                $contents = $contents->offset($this->offset);

            }

        }

        $contents = $contents->get();

        if ($this->shuffle) {


            $contents = $contents->shuffle();


        }


        return $contents;


    }






    public function getContentWithVersion($TypeName = '', $take = null, $offset = null, $shuffle = 0, $excludedContent = '')

    {

        $this->currentContentName = $excludedContent;

        $this->typeName = $TypeName;

        $this->take = $take;

        $this->offset = $offset;

        $this->shuffle = $shuffle;

        $contents = $this->getContents();


        foreach ($contents as $key => $content) {


            $content->attributes['version'] = $content->latestVersion();


        }


        $this->attributes['contents'] = $contents->toArray();


        return $this;


    }




    public function getContents()

    {


        $contentQuery = $this->belongsToMany('Greenfly\Modules\Content\Models\Content');


        if (!empty($this->typeName)) {


            $contentQuery = $contentQuery->where('type_name', $this->typeName);


        }


        if ($this->take) {


            $contentQuery = $contentQuery->take($this->take);


            if ($this->offset) {


                $contentQuery = $contentQuery->offset($this->offset);


            }


        }


        $contentQuery = $contentQuery->get();

        if ($this->shuffle) {


            $contentQuery = $contentQuery->shuffle();


        }
        //$contentQuery->orderBy('published_date', 'DESC');


        return $contentQuery;


    }




}
