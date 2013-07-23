<?php 
/**
 * Gallery Thumb Element
 *
 * Displays the thumb nail image for a gallery.
 *
 * PHP versions 5
 *
 * Zuha(tm) : Business Management Applications (http://zuha.com)
 * Copyright 2009-2012, Zuha Foundation Inc. (http://zuhafoundation.org)
 *
 * Licensed under GPL v3 License
 * Must retain the above copyright notice and release modifications publicly.
 *
 * @copyright     Copyright 2009-2012, Zuha Foundation Inc. (http://zuha.com)
 * @link          http://zuha.com Zuha™ Project
 * @package       zuha
 * @subpackage    zuha.app.plugins.galleries.views.elements
 * @since         Zuha(tm) v 0.0.1
 * @license       GPL v3 License (http://www.gnu.org/licenses/gpl.html) and Future Versions
 */
 
 
if (!empty($model) && !empty($foreignKey)) {
	$galleryThumb = $this->requestAction("/galleries/galleries/thumb/{$model}/{$foreignKey}");
} else {
    $model = 'Gallery';
}
// set up the config vars
$thumbLink = !empty($thumbLink) ? $thumbLink : null;
$thumbSize = !empty($thumbSize) ? $thumbSize : 'small';
$showEmpty = isset($showEmpty) ? $showEmpty : true;
// default sizes
$indexWidth = !empty($galleryThumb['GallerySettings']['indexImageWidth']) ? $galleryThumb['GallerySettings']['indexImageWidth'] : 24;
$indexHeight = !empty($galleryThumb['GallerySettings']['indexImageHeight']) ? $galleryThumb['GallerySettings']['indexImageHeight'] : 24;


$title = isset($title) ? array('title' => $title) : array();
$thumbWidth = !empty($galleryThumb['GallerySettings'][$thumbSize.'ImageWidth']) ? $galleryThumb['GallerySettings'][$thumbSize.'ImageWidth'] : $indexWidth;
$thumbHeight = !empty($galleryThumb['GallerySettings'][$thumbSize.'ImageHeight']) ? $galleryThumb['GallerySettings'][$thumbSize.'ImageHeight'] : $indexHeight;
// if the width was defined in the element call

$thumbWidth = !empty($thumbWidth) ? array('width' => $thumbWidth) : array('width' => $indexWidth);
$thumbHeight = !empty($thumbHeight) ? array('height' => $thumbHeight) : array('height' => $indexHeight);
$thumbAlt = !empty($thumbAlt) ? array('alt' => $thumbAlt) : array('alt' => $model);
$thumbClass = !empty($thumbClass) || $thumbClass == 'empty' ? array('class' => $thumbClass) : array('class' => 'thumbnail gallery-thumb');
$thumbId = !empty($thumbId) ? array('id' => $thumbId) : array('id' => 'gallery'.$foreignKey); // was $galleryThumb['Gallery']['id'] (didn't work for /cart)
$thumbImageOptions = array_merge($thumbWidth, $thumbHeight, $thumbAlt, $thumbClass, $thumbId, $title);
$thumbDiv = isset($thumbDiv) ? ($thumbDiv==true ? true : false) : true; // added to skip the display of div on demand (true/false)
$thumbLinkOptions = !empty($thumbLinkOptions) ? array_merge($thumbClass, $thumbId, $thumbLinkOptions, array('escape' => false)) : array('escape' => false);
$thumbLinkAppend = !empty($thumbLinkAppend) ? ' '.$thumbLinkAppend : ''; // to append anything to the image within the link

if (!empty($galleryThumb['GalleryThumb']['filename'])) {
    $imagePath = $galleryThumb['GalleryThumb']['dir'].'thumb/'. $thumbSize .'/'.$galleryThumb['GalleryThumb']['filename'];
	$conversionType = !empty($conversionType) ? $conversionType : $galleryThumb['GallerySettings']['conversionType'];
    $image = $this->Html->image($imagePath, $thumbImageOptions,	array(
    	'conversion' => $conversionType,
		'quality' => 75,
		'alt' => 'thumbnail',
		));	
} else if (!empty($showEmpty)) {
	$imagePath = '/img/noImage.jpg';
    $image = $this->Html->image($imagePath, array(
        'class' => $thumbImageOptions['class'],
        'title' => $thumbImageOptions['title']
    ));	
}

echo !empty($thumbLink) ? $this->Html->link($image . $thumbLinkAppend, $thumbLink, $thumbLinkOptions) :	$image;