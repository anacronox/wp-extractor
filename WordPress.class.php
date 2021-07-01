<?php


/**
* WordPress class - Manages the WordPress XML file and gets all data from that.
*/
class Wordpress
{

    /**
     * @var array $posts
     */
	public $posts;

	function __construct()
	{

	}

	public function getPostsFromXml($wpXML)
	{
        $this->posts = array();
		$xml = simplexml_load_file($wpXML);

		foreach($xml->channel->item as $item)
		{
			$categories = array();
			foreach($item->category as $category)
			{
				//echo $category['domain'];
				if($category['nicename'] != "uncategorized" && $category['domain'] == "category")
				{
					//echo 'Yep';
					$categories[] = $category['nicename'];
				}
			}

			$content = $item->children('http://purl.org/rss/1.0/modules/content/');

            $post =  new stdClass();

             $post->title = $item->title;
             $post->content = $content->encoded;
             $post->pubDate = strtotime($item->pubDate);
             $post->categories = $categories;
             $post->slug = str_replace("/", "", str_replace($this->url, "", $item->guid));

			$this->posts[] = $post;
		}

        //appel de la fonction
        usort($this->posts, array($this, "orderPostByDate"));

		return count($this->posts);
	}

    public function getImagesFromXml($wpXML)
    {
        $xml = simplexml_load_file($wpXML);
        $this->images = array();
        foreach($xml->channel->item as $item)
        {
            $content = $item->children('http://purl.org/rss/1.0/modules/content/');
            $excerpt = $item->children('http://wordpress.org/export/1.2/excerpt/');
            $post = $item->children('http://wordpress.org/export/1.2/');
            //$attachedFile= $item->xpath("//wp:postmeta[wp:meta_key[text() = '_wp_attachment_metadata']]/wp:meta_value");

            $images =  new stdClass();
            $images->title = (string) $item->title;
            $images->id = intval($post->post_id);
            $images->pubDate = strtotime($item->pubDate);
            $images->url = (string) $item->guid;
            $images->fileInfos = new stdClass();
            foreach ($post->postmeta as $postmetaItem){
                if(!empty($postmetaItem->meta_key) && $postmetaItem->meta_key == '_wp_attachment_metadata' && !empty($postmetaItem->meta_value)){
                    $fileInfos = unserialize((string)$postmetaItem->meta_value);

                    if(!empty( $fileInfos)){


                        if(isset($fileInfos['file'])){
                            $images->fileInfos->width = $fileInfos['width'];
                            $images->fileInfos->height = $fileInfos['height'];
                            $images->fileInfos->file = $fileInfos['file'];
                            $images->fileInfos->sizes = array();
                            foreach ( $fileInfos['sizes'] as $sizeKey => $sizeInfos){
                                $images->fileInfos->sizes[$sizeKey] = new stdClass();
                                $images->fileInfos->sizes[$sizeKey]->file = $sizeInfos['file'];
                                $images->fileInfos->sizes[$sizeKey]->width = $sizeInfos['width'];
                                $images->fileInfos->sizes[$sizeKey]->height = $sizeInfos['height'];
                                $pathinfo = pathinfo($images->fileInfos->file);
                                $images->fileInfos->sizes[$sizeKey]->filepath = $pathinfo['dirname'].'/'.$pathinfo['filename'].'-'
                                    .$images->fileInfos->sizes[$sizeKey]->width
                                    .'x'.$images->fileInfos->sizes[$sizeKey]->height.'.'.$pathinfo['extension'];

                                $images->fileInfos->sizes[$sizeKey]->url = $this->url.'/wp-content/uploads/'.$images->fileInfos->sizes[$sizeKey]->filepath;
                            }
                        }
                    }
                }
            }

            $this->images[$images->id] = $images;
        }

        return count($this->images);
    }


    public function getCategoriesFromJson($sourceFile)
    {
        $this->categories = array();
        $wpJSON = file_get_contents($sourceFile);
        $jsonCats = json_decode($wpJSON);

        if (!empty($jsonCats)) {

            foreach($jsonCats as $jsonCat)
            {

                $cat = new stdClass();
                $cat->id = $jsonCat->id;
                $cat->name = $jsonCat->name;
                $cat->slug = $jsonCat->slug;
                $cat->taxonomy = $jsonCat->taxonomy;
                $cat->parent = $jsonCat->parent;
                $cat->meta = $jsonCat->meta;

                $this->categories[$cat->id] = $cat;
            }

        }

        return count($this->categories);
    }

    public function getPostsFromJson($sourceFile)
    {
        $this->posts = array();
        $wpJSON = file_get_contents($sourceFile);
        $jsonPosts = json_decode($wpJSON);

        if (!empty($jsonPosts)) {

            foreach($jsonPosts as $jsonPost)
            {
                $categories = array();
                foreach($jsonPost->categories as $catId)
                {
                    if(isset($this->categories[$catId])){
                        $categories[] = $this->categories[$catId]->name;
                    }
                }

                $post =  new stdClass();

                $post->title = $jsonPost->title->rendered;
                $post->content = $jsonPost->content->rendered;
                $post->pubDate = strtotime($jsonPost->date);
                $post->categories = $categories;
                $post->slug = $jsonPost->slug;

                $this->posts[] = $post;
            }

        }

        //appel de la fonction
        usort($this->posts, array($this, "orderPostByDate"));

        return count($this->posts);
    }

    //on déclare la fonction
    static function orderPostByDate($a, $b) {
        return $a->pubDate > $b->pubDate;
    }



    public function meta_gallery_slider($context, $params = array()){

    }
}

