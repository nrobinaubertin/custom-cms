<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ElementController extends Controller
{
    public function indexAction($collection, $element)
    {
        // first we find the root directory of the collection
        $listDir = $this->get('kernel')->getRootDir() . '/../data/mariages/';
        foreach(scandir($listDir) as $directory) {
            if(
                is_dir($listDir.$directory)
                && preg_match("/".$collection."/i", $directory)
            ) {
                $collectionDir = $directory;
                break;
            }
        }

        // then we find the dir of the element
        foreach(scandir($listDir.$collectionDir) as $directory) {
            if(
                is_dir($listDir.$collectionDir."/".$directory)
                && preg_match("/".$element."/i", $directory)
            ) {
                $elementDir = $directory;
                break;
            }
        }

        $path = [$listDir, $collectionDir, $elementDir];
        $infos = $this->getFiles($listDir, $collectionDir, $elementDir);
        $base_url = "/mariages/".$collectionDir."/";
        return $this->renderHTML($path, $infos["main_image"], $infos["images"], $base_url);
    }

    public function getFiles($listDir, $collectionDir, $elementDir)
    {
        $fullPath = $listDir.$collectionDir."/".$elementDir;

        // now we need to find all the elements of the collection
        $images = [];
        $main_image = "";
        foreach(scandir($fullPath) as $file) {
            if(
                $file == "."
                || $file == ".."
                || !is_file($fullPath."/".$file)
                || !preg_match("/image/", mime_content_type($fullPath."/".$file))
            ) {
                continue;
            }

            if(preg_match("/^V/", $file)) {
                $main_image = $elementDir."/".$file;
                array_unshift($images, $elementDir."/".$file);
            } else {
                $images[] = $elementDir."/".$file;
            }
        }

        $infos = array(
            "main_image" => $main_image,
            "images" => $images
        );
        return $infos;
    }

    private function getNiceUrl($url) {
        return strtolower(preg_replace("/\d+[_-]+/","", $url));
    }

    private function getName($str) {
        $name = preg_replace("/^\d+[_-](.+)/","$1",$str);
        $name = preg_replace("/[_-]+/"," ",$name);
        return $name;
    }

    private function renderHTML($path, $main_image, $images, $base_url) {

        $collection = $this->getName($path[1]);
        $element = $this->getName($path[2]);

        $breadcrumbs = array(
            ["/", "Accueil"],
            ["/mariages", "Mariages"],
            ["/mariages/".$path[1], $collection],
            ["/mariages/".$path[1]."/".$path[2], $element],
        );

        $others = [];
        $listDir = $path[0].$path[1]."/";
        foreach(scandir($listDir) as $e) {
            if($e == "." || $e == ".." || !is_dir($listDir.$e)) {
                continue;
            }
            foreach(scandir($listDir.$e) as $file) {
                if(
                    is_file($listDir.$e."/".$file)
                    && preg_match("/image/", mime_content_type($listDir.$e."/".$file))
                    && preg_match("/^V/", $file)
                ) {
                    $miniature = $e."/".$file;
                    break;
                }
            }
            $others[] = array(
                "url" => $this->getNiceUrl("/mariages/".$path[1]."/".$e),
                "name" => $this->getName($e),
                "miniature" => $miniature
            );
        }

        return $this->render('@App/element.html.twig',array(
            "base_url" => $base_url,
            "main_image" => $main_image,
            "images" => $images,
            "breadcrumbs" => $breadcrumbs,
            "categorie" => $collection." - ".$element,
            "others" => $others,
            "title" => "",
            "subtitle" => "",
        ));
    }
}