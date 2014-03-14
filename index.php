<?php

class Imagsk
{
    protected $a_config_image = array();
    protected $a_range = array();
    protected $nb_result = 0;
    protected $range_style_default = "overflow:hidden;border:1px solid black;position:relative;";
    protected $container_style_default = "position:absolute;";
    protected $range_type = array('width','height','square');

    /**
     * Renseigne les informations relative à l'image à utiliser
     * @example array(array('image_1'=>array('src' => 'image_1.jpg','width'=>100,'height'=>120, 'ref_mask' => array('width'=>100,'height'=>120)))) OR array('image_1'=>array('src' => 'image_1.jpg','width'=>100,'height'=>120, 'ref_mask' => array('width'=>100,'height'=>120)))
     * @param type $a_config_image
     * @return \Imagsk
     */
    public function set_a_config_image(array $a_config_image)
    {
        $nb_array = sizeof($a_config_image);

        foreach ($a_config_image as $key => $value) {
            if($nb_array > 1 && is_array($value))
                $this->a_config_image = array_merge($this->a_config_image,$value);
            else
                $this->a_config_image[$key] = $value;
        }

        return $this;
    }

    /**
     * Renseigne les informations relative aux différents masques
     * @example array(array('masque1'=>array('width'=>100,'height'=>120))) OR array('masque1'=>array('width'=>100,'height'=>120))
     * @param type $a_range
     * @return \Imagsk
     */
    public function set_a_range(array $a_range)
    {
        $nb_array = sizeof($a_range);

        foreach ($a_range as $key => $value) {
            if($nb_array > 1 && is_array($value))
                $this->a_range = array_merge($this->a_range,$value);
            else
                $this->a_range[$key] = $value;
        }

        return $this;
    }

    public function get_image() {
        return $this->a_config_image;
    }

    public function get_range() {
        return $this->a_range;
    }

    /**
     * Exécute la mise en relation entre les masques et les images
     * @return \Imagsk
     * @throws Exception
     */
    function run()
    {
        try
        {
            if(empty($this->a_range))
                throw new Exception("Le range n'est pas configuré.");

            $this->nb_result = sizeof($this->a_config_image);

            // On créer les ranges
            $this->range();

            // On traite les images
            $this->image();

            return $this;
        }
        catch (Exception $exc) {
            echo $exc->getMessage();exit;
        }
    }


    /***************************************************************************
    ********************** FUNCTION PRIVATE/PROTECTED **************************
    ***************************************************************************/

    /**
     * Ajoute le style css à chaque range en fonction de la configuraiton indiqué
     * On indique le type de range traité
     * @return \Imagsk
     * @throws Exception
     */
    protected function range()
    {
        try
        {
            foreach ($this->a_range as $key => $value)
            {
                if(!isset($value['width']))
                    throw new Exception("La largeur du range $key n'est pas indiqué");
                if(!isset($value['height']))
                    throw new Exception("La hauteur du range $key n'est pas indiqué");

                // On créer l'élément style
                $this->a_range[$key]['style'] = "width:{$value['width']}px;height:{$value['height']}px;{$this->range_style_default}";

                // On définit le style du range
                if($value['width'] > $value['height'])
                    $this->a_range[$key]['type'] = $this->range_type[0];
                elseif($value['width'] < $value['height'])
                    $this->a_range[$key]['type'] = $this->range_type[1];
                if($value['width'] == $value['height'])
                    $this->a_range[$key]['type'] = $this->range_type[2];
            }

            return $this;
        }
        catch (Exception $exc)
        {
            echo $exc->getMessage();exit;
        }
    }

    /**
     * Génère les propriété CSS du contenaire d'une image
     * @param array $a_rang
     * @param array $a_image
     * @return string
     */
    protected function container(array $a_rang,array $a_image)
    {
        $css_result = 'position:absolute;';

        switch ($a_rang['type']) {
            case 'width': // Masque large
                $top = $this->calc_top_left($a_image['height'],$a_rang['height']);

                $width = $this->calc_width_height($a_image['width'], $a_rang['width']);
                $height = $this->calc_width_height($a_image['height'], $a_rang['height']);
                $css_result.= "left:0;top:-{$top}px;left:0;width:{$width}px;height:{$height}px";
            break;
            case 'height': // masque haut
                $left = $this->calc_top_left($a_image['width'],$a_rang['width']);
                $width = $this->calc_width_height($a_image['width'], $a_rang['width']);
                $height = $this->calc_width_height($a_image['height'], $a_rang['height']);
                $css_result.= "left:0;left:-{$left}px;top:0;width:{$width}px;height:{$height}px";
            break;
            case 'square': // masque carré
                if($a_image['width'] < $a_image['height']) // Image haute
                {
                    $top = $this->calc_top_left($a_image['height'],$a_rang['height']);
                    $left = 0;
                    $width = $a_rang['width'];
                    $height = $this->calc_width_height($a_image['height'], $a_rang['height']);
                }
                elseif($a_image['width'] > $a_image['height']) // image large
                {
                    $top = 0;
                    $left = $this->calc_top_left($a_image['width'],$a_rang['width']);
                    $width = $this->calc_width_height($a_image['width'], $a_rang['width']);
                    $height = $a_rang['height'];
                }
                else // Image carré
                {
                    $top = $this->calc_top_left($a_image['height'],$a_rang['height']);
                    $left = $this->calc_top_left($a_image['width'],$a_rang['width']);
                    $width = $a_image['width'];
                    $height = $a_image['height'];
                }
                
                $css_result.= "left:0;left:-{$left}px;top:-{$top}px;width:{$width}px;height:{$height}px";
            break;
        }

        return $css_result;
    }

    /**
     * Calcule la valeur de top ou de left
     * @param int $imgT
     * @param int $rangT
     * @return int
     */
    protected function calc_top_left($imgT,$rangT)
    {
        return $imgT-$rangT;
    }

    /**
     * Calcule la valeur de width ou de height
     * @param int $imgT
     * @param int $rangT
     * @return int
     */
    protected function calc_width_height($imgT,$rangT)
    {
        return ($imgT*2)-$rangT;
    }

    /**
     * Complète les informations sur l'image comme son masque (range) et les propriété de son contenair
     * @return \Imagsk
     * @throws Exception
     */
    protected function image()
    {
        try
        {
            foreach ($this->a_config_image as $keyImage => $image)
            {
                if(!isset($image['ref_mask']))
                    throw new Exception("Le tableau 'ref_mask' n'est pas renseigné pour l'image $keyImage");
                if(!isset($image['ref_mask']['width']))
                    throw new Exception("La largeur du mask de référence n'est pas renseigné dans 'ref_mask' pour l'image $keyImage");
                if(!isset($image['ref_mask']['height']))
                    throw new Exception("La hauteur du mask de référence n'est pas renseigné dans 'ref_mask' pour l'image $keyImage");

                // On indique les valeurs de références hauteur et largeur du masque de notre image
                $ref_mask_width = $image['ref_mask']['width'];
                $ref_mask_height = $image['ref_mask']['height'];

                // on boucle tous les masques
                foreach ($this->a_range as $keyRange => $range)
                {
                    if(($ref_mask_width == $range['width']) && ($ref_mask_height == $range['height']))
                    {
                        // On a trouvé le masque
                        $this->a_config_image[$keyImage]['range'] = $keyRange;
                        $this->a_config_image[$keyImage]['container'] = $this->container($range,$image);
                        break;
                    }
                }
            }

            return $this;
        }
        catch (Exception $exc)
        {
            echo $exc->getMessage();exit;
        }
    }
}

/*******************************************************************************
******************************** EXEMPLE *************************************** 
*******************************************************************************/

// On créer notre objet
$imagsk = new Imagsk();

// On configure les masques (taille du contener) de l'image à placer
$a_range = array(
    array('masque1' => array('width'=>254,'height' => 142)),
    array('masque2' => array('width'=>142,'height' => 254)),
    array('masque3' => array('width'=>250,'height' => 250))
);
$imagsk->set_a_range($a_range);

// On configure les images
$a_image = array(
   array('loremH_142x254' => array('src'=> 'loremH_142x254.png', 'width' => 218, 'height' => 254, 'ref_mask' => array('width'=>142,'height'=>254))),
   array('loremH_250x250' => array('src'=> 'loremH_250x250.png', 'width' => 250, 'height' => 291, 'ref_mask' => array('width'=>250,'height'=>250))),
   array('loremH_254x142' => array('src'=> 'loremH_254x142.png', 'width' => 254, 'height' => 296, 'ref_mask' => array('width'=>254,'height'=>142))),
   array('loremL_142x254' => array('src'=> 'loremL_142x254.jpg', 'width' => 448, 'height' => 254, 'ref_mask' => array('width'=>142,'height'=>254))),
   array('loremL_250x250' => array('src'=> 'loremL_250x250.jpg', 'width' => 441, 'height' => 250, 'ref_mask' => array('width'=>250,'height'=>250))),
   array('loremL_254x142' => array('src'=> 'loremL_254x142.jpg', 'width' => 254, 'height' => 144, 'ref_mask' => array('width'=>254,'height'=>142))),
);
//$a_image = array('loremH_142x254' => array('src'=> 'loremH_142x254.png', 'width' => 218, 'height' => 254));
$imagsk->set_a_config_image($a_image);

// On lance le script
$imagsk->run();

// On récupère les données
$a_ranges = $imagsk->get_range();
$a_images = $imagsk->get_image();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta charset="utf-8">
	<title></title>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
	<script src="//code.jquery.com/jquery-1.9.1.js"></script>
	<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

	<style>
.range {
    margin: 0 auto;
}

	</style>
</head>

<body>
    <div style="width:600px;margin:0 auto;background:#999;">
        <?php foreach ($a_images as $imgkey => $image):?>

            <?php // On récupère le range associé
            $range_name = $image['range'];

            // On récupère le style css du range
            $range_css = $a_ranges[$range_name]['style']; ?>
            <div style="<?php echo $range_css;?>" class="range" id="<?php echo $imgkey;?>">
                <div style="<?php echo $image['container'];?>" class="contenaire">
                    <img src="<?php echo $image['src'];?>" style="width: <?php echo $image['width'];?>;height: <?php echo $image['height'];?>" />
                </div>
            </div>
            <script>
                $("#<?php echo $imgkey;?> img").draggable({
                    containment: $('#<?php echo $imgkey;?> .contenaire')
                });
            </script>
        <?php endforeach;?>
    </div>
</body>
</html>