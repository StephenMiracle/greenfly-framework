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






    public function getContentWithVersion($TypeName = '', $take = null, $offset = null, $excludedContent = '')

    {

        $this->currentContentName = $excludedContent;

        $this->typeName = $TypeName;

        $this->take = $take;

        $this->offset = $offset;

        $contents = $this->getContents();


        foreach ($contents as $key => $content) {


            $content->attributes['version'] = $content->latestVersion();


        }


        $this->attributes['contents'] = $contents;


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


        $contentQuery->orderBy('published_date', 'DESC');


        return $contentQuery->get();


    }




}
