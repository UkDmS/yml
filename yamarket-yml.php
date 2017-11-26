<?php

/*

Plugin Name: Import to Yandex Market

Description: Import to Yandex Market

Version: 0.1

Author: UkDmS

License: GPLv2

*/
//wp_clear_scheduled_hook( 'myprefix_my_cron_action');
//wp_unschedule_event( wp_next_scheduled( 'myprefix_my_cron_action' ), 'myprefix_my_cron_action' );
class yaMarket {
    function __construct(){
        add_action( 'admin_menu', array( $this, 'yml_add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'yml_general_init' ));
        add_action( 'admin_init', array( $this, 'yml_file_init' ));
        add_action( 'admin_init', array( $this, 'yml_category_init'));
        add_action( 'admin_enqueue_scripts', array( $this, 'load_styles') );
    }
	function yml_add_admin_menu() {
        # add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
        # $page_title(строка) (обязательный) - Текст, который будет использован в теге <title> на странице, относящейся к пункту меню.
        # $menu_title(строка) (обязательный) - Название пункта меню в сайдбаре админ-панели.
        # $capability(строка) (обязательный) - Права пользователя (возможности), необходимые чтобы пункт меню появился в списке.
        # $menu_slug(строка) (обязательный) - Уникальное название (slug), по которому затем можно обращаться к этому меню.
        # Если параметр $function не указан, этот параметр должен равняться названию PHP файла относительно каталога плагинов, который отвечает за вывод кода страницы этого пункта меню.
        # $function(строка) - Название функции, которая выводит контент страницы пункта меню.Этот параметр необязательный и если он не указан, WordPress ожидает что текущий подключаемый PHP файл генерирует страницу код страницы админ-меню, без вызова функции. Большинство авторов плагинов предпочитают указывать этот параметр.
        add_menu_page( ( 'Импорт в Яндекс Маркет' ), ( 'Импорт в Яндекс Маркет' ), 'manage_options', 'yandex', array( $this, 'yml_options_page' ) );
	}
    function yml_options_page(  ) {
    ?>
        <h2 class='center'>Импорт в Яндекс Маркет</h2>
        <div class="tabs">
            <input id="tab1" type="radio" name="tabs" checked>
            <label for="tab1" title="Настройки магазина">Настройки магазина</label>
            <input id="tab2" type="radio" name="tabs">
            <label for="tab2" title="Настройки экспорта">Настройки экспорта</label>
            <input id="tab3" type="radio" name="tabs">
            <label for="tab3" title="Категории">Категории/свойства</label>
            <input id="tab4" type="radio" name="tabs">
            <label for="tab4" title="Обновление">Обновление</label>
            <section id="content-tab1">
                <form action='options.php' method='post' id='defaultForm'>
                <?php
                    settings_fields( 'ymlGeneralPage' );
                    do_settings_sections( 'ymlGeneralPage' );
                    submit_button();
                ?>
                </form>
            </section>
            <section id="content-tab2">
                <form action='options.php' method='post'>
                    <?php
                    $path = wp_upload_dir() ;
                    $options = get_option( 'yml_settings_file' );
                    settings_fields( 'ymlFilePage' );
                    do_settings_sections( 'ymlFilePage' );
                    ?>
                    <p>Скачать файл  выгрузки <a download href='<? echo $path['baseurl'];?>/tmp/<? echo $options['yml_file_path'];?>'><? echo $options['yml_file_path'];?></a></p>
                    <?
                    submit_button();
                    ?>
                </form>
            </section>
            <section id="content-tab3">
                <form action='options.php' method='post'>
                 <?
                    # Выводит скрытые поля формы на странице настроек (option_page, _wpnonce, ...)
                    # settings_fields( $option_group );
                    # $option_group(строка) (обязательный) - Название группы настроек
                    settings_fields( 'ymlCategoryPage' );
                    # Выводит на экран все блоки опций, относящиеся к указанной странице настроек в админ-панели
                    # do_settings_sections( $page );
                    # $page(строка) (обязательный) - Идентификатор страницы админ-панели на которой нужно вывести блоки опций. Должен совпадать с параметром $page из add_settings_section( $id, $title, $callback, $page ).
                    do_settings_sections( 'ymlCategoryPage' );
                    submit_button();
                    //$cron_zadachi = get_option( 'cron' );
                    //var_dump( $cron_zadachi );
                    ?>
                </form>
            </section>
            <section id="content-tab4">
            <?
            date_default_timezone_set('Europe/Moscow');
            $path = wp_upload_dir();
            $a = plugins_url();
            $dir = plugin_dir_path( __FILE__ );
            $count = explode("/",$dir);
            $count = array_diff($count, array(''));
            $options = get_option( 'yml_settings_file' );
            $filename = $path['basedir']."/tmp/".$options['yml_file_path'];
            echo "<button id='reload' data-path='".$a."/".$count[count($count)]."/yml.php'>Обновить</button>";
            echo "<br>В последний раз файл  был изменен: " . date ('Y-m-d H:i', filemtime($filename));
            ?>
            </section>
        </div>
    <?php
    }
    function yml_general_init() {

        # register_setting( $option_group, $option_name, $args );
        # $option_group(строка) (обязательный) Название группы, к которой будет принадлежать опция. Это название должно совпадать с названием группы в функции settings_fields().
        # По умолчанию: нет
        # $option_name(строка) (обязательный)
        # Название опции, которая будет сохраняться в БД.
        # По умолчанию: нет
        # $args(массив/строка)
        register_setting( 'ymlGeneralPage', 'yml_settings' );

        # add_settings_section( $id, $title, $callback, $page );
        # 1 - Идентификатор секции
        # 2 - Заголовок секции
        # 3 - Функция заполняет секцию описанием
        # 4 - Страница на которой выводить секцию
        add_settings_section(
        'yml_general_section',
        __( '', 'wordpress' ),
        '',
        'ymlGeneralPage'
        );

        # add_settings_field( $id, $title, $callback, $page, $section, $args );
        # 1 - Ярлык (slug) опции, используется как идентификатор поля
        # 2 - Название поля
        # 3 - Название функции обратного вызова.
        # Функция должна заполнять поле нужным <input> тегом, который станет частью одной большой формы.
        # Атрибут name должен быть равен параметру $option_name из register_setting().
        # Атрибут id обычно равен параметру $id. Результат должен сразу выводиться на экран (echo).
        # 4 - Страница меню в которую будет добавлено поле. Указывать нужно ярлык (slug) страницы.
        # 5 - Название секции настроек, в которую будет добавлено поле
        add_settings_field(
            'yml_short_name_shop',
            __( 'Краткое название магазина', 'wordpress' ),
            array($this,'yml_short_name_shop'),
            'ymlGeneralPage',
            'yml_general_section'
        );
        add_settings_field(
            'yml_full_name_shop',
            __( 'Полное название магазина', 'wordpress' ),
            array($this,'yml_full_name_shop'),
            'ymlGeneralPage',
            'yml_general_section'
        );
        add_settings_field(
            'yml_url_shop',
            __( 'Адрес магазина', 'wordpress' ),
            array($this,'yml_url_shop'),
            'ymlGeneralPage',
            'yml_general_section'
        );
        add_settings_field(
            'yml_currency',
            __( 'Название валюты/курс', 'wordpress' ),
            array($this,'yml_currency'),
            'ymlGeneralPage',
            'yml_general_section'
        );
    }

    function yml_short_name_shop() {
        $options = get_option( 'yml_settings' );
        ?>
            <input type='text' name='yml_settings[yml_short_name_shop]' value='<?php echo $options['yml_short_name_shop']; ?>' size='50'>
        <?php
    }

    function yml_full_name_shop() {
        $options = get_option( 'yml_settings' );
        ?>
        <input type='text' name='yml_settings[yml_full_name_shop]' value='<?php echo $options['yml_full_name_shop']; ?>' size='50'>
        <?php
    }

    function yml_url_shop() {
        $options = get_option( 'yml_settings' );
        ?>
        <input type='text' name='yml_settings[yml_url_shop]' value='<?php echo $options['yml_url_shop']; ?>' size='50'>
        <?php
    }

    function yml_currency() {
        $options = get_option( 'yml_settings' );
        if(empty($options['yml_currency']))
        {
        ?>
        <div class="form-group">
                    <div class='row'>
                    <div class="col-lg-2">
                        <input class="form-control" type="text" name="yml_settings[yml_currency][]"  value='<?php echo $options['yml_currency']; ?>' size='5'/>
                    </div>
                    <div class="col-lg-3">
                        <input class="form-control" type="text" name="yml_settings[yml_rate][]" value='<?php echo $options['yml_rate']; ?>' size='10'/>
                    </div>
                    <div class="col-lg-3">
                        <button type="button" class="btn btn-default btn-sm addButton" data-template="currency-rate">Добавить</button>
                    </div>
                    </div>
                </div>
                <div class="form-group hide" id="currency-rateTemplate">
                    <div class='row'>
                    <div class="col-lg-2">
                        <input class="form-control" type="text" size='5'/>
                    </div>
                    <div class="col-lg-3">
                        <input class="form-control" type="text" size='10'/>
                    </div>
                    <div class="col-lg-3">
                        <button type="button" class="btn btn-default btn-sm removeButton">Удалить</button>
                    </div>
                    </div>
                </div>
        <?php
        }
        else
        {
            function show($n, $m,$k)
            {
                 return("<div class='form-group' number='$k'>
                        <div class='row'>
                        <div class='col-lg-2'>
                            <input class='form-control' type='text' number='$k' name='yml_settings[yml_currency][]'  value='$n'  size='5'/>
                        </div>
                        <div class='col-lg-3'>
                            <input class='form-control' type='text' name='yml_settings[yml_rate][]' value='$m' size='10'/>
                        </div>
                        <div class='col-lg-3'>
                            <button type='button' number='$k' class='btn btn-default btn-sm remove'>Удалить</button>
                        </div>
                        </div>
                    </div>");
            }
            $k =array();
            for($i=0;$i<count($options['yml_currency']);$i++)
            {
                $k[]= $i;
            }
            foreach(array_map("show", $options['yml_currency'], $options['yml_rate'],$k) as $item)
            {
                echo $item;

            }
            ?>
            <div class="form-group hide" id="currency-rateTemplate">
                <div class='row'>
                <div class="col-lg-2">
                    <input class="form-control" type="text" size='5'/>
                </div>
                <div class="col-lg-3">
                    <input class="form-control" type="text" size='10'/>
                </div>
                <div class="col-lg-3">
                    <button type="button" class="btn btn-default btn-sm removeButton">Удалить</button>
                </div>
                </div>
            </div>
            <?
            echo "<div class='col-lg-3 col-lg-offset-7'><button type='button' class='btn btn-default btn-sm addButton' data-template='currency-rate'>Добавить</button>
                        </div>";
        }
    }

    function yml_file_init() {
	    register_setting( 'ymlFilePage', 'yml_settings_file' );
        $path = wp_upload_dir() ;
	    add_settings_section(
            'yml_file_section',
    		__( '', 'wordpress' ),
    		'',
    		'ymlFilePage'
	    );
    	add_settings_field(
    		'yml_file_path',
    		__( 'Путь к файлу выгрузки '.$path['basedir'].'/tmp', 'wordpress' ),
    		array($this,'yml_file_path'),
    		'ymlFilePage',
    		'yml_file_section'
    	);
    }

    function yml_file_path() {
    	$options = get_option( 'yml_settings_file' );
	    ?>
        <div class="row">
            <div class="col-lg-12"><input type='text' name='yml_settings_file[yml_file_path]' value='<?php echo $options['yml_file_path']; ?>'></div>
        </div>
	    <?php
    }

    function yml_category_init()
    {
        register_setting( 'ymlCategoryPage', 'yml_settings_category' );
        add_settings_section(
            'yml_category_section',
            __( 'Список категорий', 'wordpress' ),
            '',
            'ymlCategoryPage'
        );
        add_settings_field(
            'yml_list_category',
            __( '', 'wordpress' ),
            array($this,'yml_category_output'),
            'ymlCategoryPage',
            'yml_category_section'
        );
    }

    function yml_category_output() {
        $options = get_option( 'yml_settings_category' );
        $categories = get_terms('category', array(
            'orderby' => 'term_id',
            'order' => 'ASC',
            'hide_empty' => '0'
        ));
        foreach ($categories as $category)
        {
            if(in_array($category->term_id,$options['yml_list_category']))
            echo "<input type='checkbox' name='yml_settings_category[yml_list_category][]' value='".$category->term_id."' checked> ".$category->name."</input><br>";
            else
            echo "<input type='checkbox' name='yml_settings_category[yml_list_category][]' value='".$category->term_id."'> ".$category->name."</input><br>";
        }
        echo "<h2>Параметры</h2>"."\r\n";
         $args = array( 'numberposts'=>'-1',
                        'post_type'=>'acf'//,
                        //'include'=>array('4001','4006','4002','4003','4004','4007','4008','4009','4010')
                        );
            $a = get_posts($args);
            foreach($a as $item)
            {
                echo "<div class='item-section'><span>".$item->post_title."</span>";
                foreach (get_post_meta($item->ID) as $record_key=>$record_data)
                {
                    $field_object = get_field_object($record_key,$post->ID);
                    if(!empty($field_object['label']))
                    {
                        if(in_array($field_object['name'],$options['yml_list_options']))
                        echo "<input type='checkbox' name='yml_settings_category[yml_list_options][]' value='".$field_object['name']."' checked> ".$field_object['label']."</input><br>";
                        else
                        echo "<input type='checkbox' name='yml_settings_category[yml_list_options][]' value='".$field_object['name']."'> ".$field_object['label']."</input><br>";
                    }
                }
                echo "</div>";
           }
    }

    function load_styles()
    {
        # wp_register_style( $handle, $src, $deps, $ver, $media );
        # $handle(строка) (обязательный) - Название подключаемого файла стилей (буквы в нижнем регистре). Должен быть уникальным, так как он будет использован как идентификатор в системе.
        # $src(строка) (обязательный) - УРЛ к файлу стилей. Например, http://site.ru/css/style.css. Не нужно указывать путь жестко, используйте функции: plugins_url() (для плагинов) и get_template_directory_uri() (для тем).
        # Внешние домены можно указывать с неявным протоколом //notmysite.ru/css/style.css.
        # $deps(массив) - Массив идентификаторов других стилей, от которых зависит подключаемый файл стилей. Указанные тут стили, будут подключены до текущего.
        # По умолчанию: array()
        # $ver(строка/логический) - Строка определяющая версию стилей. Версия будет добавлена в конец ссылки на файл: ?ver=3.5.1. Если не указать (установлено false), будет использована версия WordPress. Если установить null, то никакая версия не будет установлена.
        # По умолчанию: false
        # $media(строка) - Устанавливается значение атрибута media. media указывает тип устройства для которого будет работать текущий стиль. Может быть:
        # all
        # screen
        # handheld
        # print
        wp_register_style( 'custom-style', plugins_url( '/ymlStyle.css', __FILE__ ), array(), '', 'all' );
        wp_register_style( 'bootstrap.css', plugins_url( '/css/bootstrap.css', __FILE__ ), array(), '', 'all' );
        wp_register_style( 'bootstrapValidator.css', plugins_url( '/css/bootstrapValidator.css', __FILE__ ), array(), '', 'all' );
        wp_register_script('bootstrap.min.js', plugins_url( '/js/bootstrap.min.js', __FILE__ ));
        wp_register_script('bootstrapValidator.js', plugins_url( '/js/bootstrapValidator.js', __FILE__ ));
        wp_register_script('jquery.min.js', plugins_url( 'js/jquery.min.js', __FILE__ ));
        wp_register_script('script.js', plugins_url( 'js/script.js', __FILE__ ));
        wp_register_script('button.js', plugins_url( 'js/button.js', __FILE__ ));
        # wp_enqueue_style( $handle, $src, $deps, $ver, $media );
        # $handle(строка) (обязательный) - Название файла стилей (идентификатор). Строка в нижнем регистре. Если строка содержит знак вопроса (?): scriptaculous?v=1.2, то предшествующая часть будет названием скрипта, а все что после будет добавлено в УРЛ как параметры запроса. Так можно указывать версию подключаемого скрипта.
        # $src(строка/логический) - УРЛ к файлу стилей. Например, http://site.ru/css/style.css. Не нужно указывать путь жестко, используйте функции: plugins_url() (для плагинов) и get_template_directory_uri() (для тем). Внешние домены можно указывать с неявным протоколом //notmysite.ru/css/style.css.
        # По умолчанию: false
        # $deps(массив) - Массив идентификаторов других стилей, от которых зависит подключаемый файл стилей. Указанные тут стили, будут подключены до текущего.
        # По умолчанию: array()
        # $ver(строка/логический) - Строка определяющая версию стилей. Версия будет добавлена в конец ссылки на файл: ?ver=3.5.1. Если не указать (установлено false), будет использована версия WordPress. Если установить null, то никакая версия не будет установлена.
        # По умолчанию: false
        # $media(строка/логический) - Устанавливается значение атрибута media. media указывает тип устройства для которого будет работать текущий стиль. Может быть: 'all', 'screen', 'handheld', 'print' или 'all (max-width:480px)'. Полный список смотрите здесь.
        # По умолчанию: 'all'
        wp_enqueue_style( 'custom-style' );
        wp_enqueue_style( 'bootstrap.css' );
        wp_enqueue_style( 'bootstrapValidator.css' );
        wp_enqueue_script('jquery.min.js');
        wp_enqueue_script('script.js');
        wp_enqueue_script('bootstrap.min.js');
        wp_enqueue_script('bootstrapValidator.js');
    }
}

new yaMarket();

add_action('admin_init',add_filter( 'cron_schedules', 'myprefix_add_weekly_cron_schedule' ));
        function myprefix_add_weekly_cron_schedule( $schedules ) {
                                $schedules['hour1'] = array(
                                        'interval' => 600,
                                        'display'  => ( 'Каждую минуту' ),
                                );
                                return $schedules;
                        }

        if (!wp_next_scheduled( 'myprefix_my_cron_action' ) ) {
            wp_schedule_event( time(), 'hour', 'myprefix_my_cron_action' );
        }
        add_action( 'myprefix_my_cron_action', 'myprefix_function_to_run' );
        function myprefix_function_to_run() {
                        $ch = curl_init();
                        $url = "http://wp.goodweb.me/wp-content/plugins/yamarket-yml/yml.php";
                        curl_setopt($ch, CURLOPT_URL,$url);
                        curl_setopt($ch, CURLOPT_HEADER, 1); // читать заголовок
                        curl_setopt($ch, CURLOPT_NOBODY, 1); // читать ТОЛЬКО заголовок без тела
                        $result = curl_exec($ch);
                        curl_close($ch);
                        echo $result;
        }




register_deactivation_hook(__FILE__, 'yml_deactivate' );

function yml_deactivate(){

     remove_menu_page('yandex') ;

}
?>