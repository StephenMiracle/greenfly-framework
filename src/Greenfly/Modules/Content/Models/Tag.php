<?php



namespace Greenfly\Modules\Content\Models;



use Greenfly\Modules\Model;



class Tag extends Model

{

    protected $fillable = ['name', 'taxonomy_name'];

    protected $typeName = 'type_name';
 
    public $typerName;

    public $take;



    public function versions()

    {

        return $this->belongsToMany('Greenfly\Modules\Content\Models\Version');

    }



    public function taxonomy()

    {

        return $this->belongsTo('Greenfly\Modules\Content\Models\Taxonomy');

    }



    public function contents()

    {

        $contents = $this->belongsToMany('Greenfly\Modules\Content\Models\Content');



        if (!empty($this->typerName)) {

            $contents->where('type_name', $this->typerName);

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



    public function getContentWithVersion($currentContentName = '', $type_name = '', $take = '', $offset = '')

    {

        $this->currentContentName = $currentContentName;

        $this->typerName = $type_name;

        $this->take = $take;

        $this->offset = $offset;

        $contents = $this->contents;



        foreach ($contents as $content) {

            $content->version = $content->latestVersion();

        }



        return $contents;

    }



    public function getContents($params)

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
