<?php

include_once __DIR__ . '/WordPress.class.php';
include_once __DIR__ . '/shortcode.class.php';

$Wordpress = new Wordpress();

$Tfolders = array(
    array(
        'sourceFile' => 'http://www.lesite.ltd/wp-json/wp/v2/posts',
        'folderDest' => __DIR__ . '/exported/posts/',
    ),
    array(
        'sourceFile' => __DIR__ . '/data-sources/rest/posts-002.json',
        'folderDest' => __DIR__ . '/exported/posts/',
    )
);

$categoryFile = 'http://www.lesite.ltd/wp-json/wp/v2/categories';



?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <link href="https://fonts.googleapis.com/css?family=Inconsolata:400,700|Poppins:400,400i,500,700,700i&amp;subset=latin-ext" rel="stylesheet">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="utf-8">
        <title>Export</title>
        <link rel="stylesheet" href="tpl/mini-default.min.css">
    </head>
    <body>
<?php



foreach ($Tfolders as $folder){
    $sourceFile = $folder['sourceFile'];
    $folderDest = $folder['folderDest'];

    $numbersOfCats = $Wordpress->getCategoriesFromJson($categoryFile);
    $numbersOfPosts = $Wordpress->getPostsFromJson($sourceFile);

    if($numbersOfPosts>0){


        foreach ($Wordpress->posts as $post){
            $out='';

            $out.= '<div class="container">';

            $out.= '<div class="section double-padded">';
            $out.= '<h1 class="post-title ">'.$post->title.'</h1>';

            $out.= '<p>';
            $out.= '<small class="post-date">Date : '.date('d/m/Y', $post->pubDate).'</small>';
            if(!empty($post->categories)){
                $out.= '&nbsp;&nbsp;&nbsp;&nbsp; <small class="post-category">Categorie'.(count($post->categories)>1?'(s)':'').' : '.implode(', ',$post->categories).'</small>';
            }

            $out.= '</p>';
            $out.= '</div>';

            $out.= '<div class="post-content section">'.$post->content.'</div>';
            $out.= '</div>';

            print $out.'<br/><br/><br/><br/>';

            $outputFile = '<!DOCTYPE html>
    <html lang="fr">
      <head>
        <meta charset="utf-8">
        <title>'.$post->title.'</title>
        <link rel="stylesheet" href="../../tpl/mini-default.min.css">
      </head>
      <body>
        '.$out.'
      </body>
    </html>';
            $filename = date('Y-m-d', $post->pubDate).'_'.str_replace(' ', '_', $post->title);
            $filename =  preg_replace("/[^A-Za-z0-9\- _]/", '', $filename);
            file_put_contents ( $folderDest.$filename.'.html' , $outputFile);
        }
    }

}


print '</body>
</html>';




function shortcode_gallery_slider($context, $params=false){

    $TContext = explode(':', $context);

    $out = "";

    return $out;
}



function shortcode_gallery($context, $params=false){

    global $Wordpress;

    $TContext = explode(':', $context);

    $out = '';

    if(!empty($params) && is_array($params)){
        if(!empty($params['ids'])){
            $TIds = explode(',', $params['ids']);
            if(!empty($TIds)){
                $out.='<div class="row imagecontainer">';
                $i = 0;
                foreach ($TIds as $item){
                    if(!empty($Wordpress->images[$item])){

                        if($i>=2){
                            $i = 0;
                            $out.='</div><div class="row imagecontainer">';
                        }

                        $out.='<div class="col-sm-6">';
                        // $Wordpress->images[$item]->url
                        $out .= '<div class="card fluid"><figure>
                              <img src="'.$Wordpress->images[$item]->fileInfos->sizes['medium_large']->url.'" alt="'.htmlentities($Wordpress->images[$item]->title, ENT_QUOTES).'"/>
                              <figcaption>'.$Wordpress->images[$item]->title.'</figcaption>
                            </figure></div>';

                        $out.='</div>';

                        $i++;
                    }
                }
                $out.='</div>';
            }
        }
    }

    return $out;
}