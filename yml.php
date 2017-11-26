<?
require('../../../wp-load.php');
class yml
{
    public $startTime;
    private $name;
    private $company;
    private $url;
    public $platform = 'wordpress';
    private $shop = array('name'=>'','company'=>'','url'=>'','platform'=>'') ;
    private $currency;
    private $rate;

    public function __construct($startTime){
        date_default_timezone_set('Europe/Moscow');
        $options = get_option('yml_settings');
        $this->startTime = $startTime;
        $this->name = $options['yml_short_name_shop'];
        $this->company = $options['yml_full_name_shop'];
        $this->url = $options['yml_url_shop'];
        $this->currency = $options['yml_currency'];
        $this->rate = $options['yml_rate'];
    }

    public function get_name(){ return $this->name; }
    public function get_company(){ return $this->company; }
    public function get_url(){ return $this->url; }
    public function set_header(){ header('Content-type:application/xml'); }

    public function prepare_field($s){
        $from = array('"', '&', '>', '<', '\'');
        $to = array('&quot;', '&amp;', '&gt;', '&lt;', '&apos;');
        $s = str_replace($from, $to, $s);
        return trim($s);
    }

    public function set_shop(){
        $this->shop['name'] = $this->prepare_field($this->get_name());
        $this->shop['company'] = $this->prepare_field($this->get_company());
        $this->shop['url'] = $this->prepare_field($this->get_url());
        $this->shop['platform'] = $this->prepare_field($this->platform);
        return $this->shop;
    }

    public function convert_array_to_tag($arr){
        $s = '';
        foreach($arr as $tag=>$val){
            $s .= '<'.$tag.'>'.$val.'</'.$tag.'>'."\r\n";
        }
        return $s;
    }

    public function set_currency($currency,$rate){
        $response = array_map(function($currency, $rate){
            return "<currencies><currency id='".$currency."' rate='".$rate."'/></currencies>\r\n";},
            $currency,
            $rate);
        return $response;
    }

    public function category_section(){
        $options = get_option( 'yml_settings_category' );
        $categories = get_terms('category', array(
            'orderby' => 'term_id',
            'order' => 'ASC',
            'hide_empty' => '0'
        ));
        $category_ids = array();
        $s .= '<categories>' . "\r\n";
        foreach ($categories as $category){
            if(in_array($category->term_id,$options['yml_list_category'])){
                $s .="<category id='".$category->term_id."'>".$category->name."</category>\r\n";
                $category_ids[] = $category->term_id;
            }
        }
        $s .= '</categories>' . "\r\n";
        return $s;
    }

    public function offers_section(){
        $options = get_option( 'yml_settings_category' );
        $category_ids = $options['yml_list_category'];
        if(empty($category_ids)){
          $category_ids = '9999999999999';
        }
        else $category_ids = implode(',', $category_ids);
        $s = '<offers>' . "\r\n";
        $posts = get_posts(array(
            'category' => $category_ids,
            'numberposts' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
            'post_status' => 'publish'
        ));
        $term_data = wp_get_object_terms(
            array_map(function($v){ return $v->ID; }, $posts),
            array('category', 'brands'),
            array('fields' => 'all_with_object_id')
        );
        $terms = array();
        foreach ($term_data as $term){
            $terms[$term->object_id][$term->taxonomy][$term->term_id] = array('name' => trim($term->name), 'parent' => intval($term->parent));
        }
        foreach ($posts as $post) {
            $records = get_post_meta($post->ID);
            $post_category = current($terms[$post->ID]['category']);
			$post_category_id = current(array_keys($terms[$post->ID]['category']));
			$post_category_parent = $post_category['parent'];
            $brands = trim(implode(', ', array_map(function($v){ return $v['name']; }, $terms[$post->ID]['brands'])));
            $s .= "<offer id='".$post->ID."' available='true'>\r\n";
            $s .= "<url>".get_permalink($post->ID)."</url>\r\n";
            $s .= "<price>";
					$rubl = get_post_meta($post->ID,'rubl',true);
					$rouble = get_post_meta($post->ID,'rouble',true);
					$price_final = get_post_meta($post->ID,'price_final',true);
					if ((int)$rouble)
                        $s .= $rouble;
                    else if ((int)$rubl)
                        $s .= $rubl;
                    else
                        $s .= $price_final;
		    $s .= "</price>\r\n";
            $s .= "<currencyId>RUB</currencyId>\r\n";
		    $s .= "<categoryId>".$post_category_id."</categoryId>\r\n";
            $s .= "<picture>";
                $images = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
                $s .= $images[0];
            $s .= "</picture>\r\n";
            $s .= "<store>false</store>\r\n";
			$s .= "<pickup>false</pickup>\r\n";
			$s .= "<delivery>true</delivery>\r\n";
            if ($brands) {
                $s .="<name>".$post->post_title."</name>\r\n";
                $s .= "<vendor>".$brands."</vendor>\r\n";
                $s .= "<model>".$post->post_name."</model>\r\n";
			}
            else{
				$s .="<name>".$post->post_title."</name>\r\n";
			}
            $from = array('"', '&', '>', '<', '\'');
            $to = array('&quot;', '&amp;', '&gt;', '&lt;', '&apos;');
            $post->post_content = str_replace($from, $to, $post->post_content);
            $s .="<description><![CDATA[".trim(str_replace(array("\r\n", "\n", "\r"), array(' ', ' ', ' '), strip_tags(html_entity_decode((string)$post->post_content))))."]]></description> \r\n";
            $s .='<downloadable>false</downloadable>'."\r\n";
            foreach ($records as $record_key=>$record_data) {
                if(in_array($record_key,$options['yml_list_options'])){
                    $field_object = get_field_object($record_key,$post->ID);
                    if(!empty($field_object['value']))
                    $s .= "<param name='".$field_object['label']."'>".$field_object['value']."</param>\r\n";
                }
            }
             $s .= "</offer>\r\n";
        }
        $s .= '</offers>' . "\r\n";
        return $s;
    }

    public function render_header_xml(){
        $body = '<?xml version="1.0" encoding="UTF-8"?>'. "\r\n".'<!DOCTYPE yml_catalog SYSTEM "shops.dtd">'."\r\n".
        '<yml_catalog date="'.date('Y-m-d H:i').'">'. "\r\n";
        return $body;
    }

    public function render_footer_xml(){
        return '</yml_catalog>';
    }

    public function render_main_xml(){
        $body = '<shop>'. "\r\n";
        $shop = $this->set_shop($name,$company,$url,$platform);
        $body .= $this->convert_array_to_tag($shop);
        foreach($this->set_currency($this->currency,$this->rate) as $item){
            $body .= $item;
        }
        $body .= $this->category_section();
        $body .= $this->offers_section();
        $body .= '</shop>'. "\r\n";
        return $body;
    }

    public function render_xml(){
        $string = $this->render_header_xml();
        $string.= $this->render_main_xml();
        $string.= $this->render_footer_xml();
        return $string;
    }

    public function save_file($market){
        $path = wp_upload_dir() ;
        $options = get_option( 'yml_settings_file' );
        $output_file = $path['basedir']."/tmp/".$options['yml_file_path'];
        file_put_contents($output_file, $market);
    }
}

$yml = new yml($startTime = microtime(true));
$market =  $yml->render_xml();
$yml->set_header();
$yml->save_file($market);
//echo $market;
