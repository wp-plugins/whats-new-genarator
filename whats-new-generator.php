<?php
/*
Plugin Name: What's New Generator
Plugin URI: http://residentbird.main.jp/bizplugin/
Description: What's New(新着情報)を指定した固定ページや投稿に自動的に表示するプラグインです。
Version: 1.3.0
Author:WordPress Biz Plugin
Author URI: http://residentbird.main.jp/bizplugin/
*/

$whatsNewPlugin = new WhatsNewPlugin();

/**
 * プラグイン本体
 */
class WhatsNewPlugin{

	var $shortcode = "showwhatsnew";
	var $option_name = 'whats_new_options';

	public function __construct(){
		register_activation_hook(__FILE__, array(&$this,'on_activation'));	//プラグイン有効時の処理を設定
		register_deactivation_hook(__FILE__, array(&$this,'on_deactivation'));
		add_action( 'admin_init', array(&$this,'on_admin_init') );	//管理画面の初期化
		add_action( 'admin_menu', array(&$this, 'on_admin_menu'));			//管理画面にメニューを追加
		add_action('wp_print_styles', array(&$this,'on_print_styles'));				//cssの設定（管理画面以外)
		add_shortcode($this->shortcode, array(&$this,'show_shortcode')); 		//ショートコードの設定
		add_filter('widget_text', 'do_shortcode');
	}

	function on_deactivation(){
		unregister_setting($this->option_name, $this->option_name );
		//delete_option($this->option_name);
		wp_deregister_style('whats-new-style');
	}

	function on_print_styles() {
		$cssPath = WP_PLUGIN_DIR . '/whats-new-generator/whats-new.css';

		/* CSSファイルが存在すれば、関数呼び出しでCSSを追加する */
		if(file_exists($cssPath)){
			/* CSSの格納URL */
			$cssUrl = plugins_url('whats-new.css', __FILE__);
			/* CSS登録 */
			wp_register_style('whats-new-style', $cssUrl);
			/* CSS追加 */
			wp_enqueue_style('whats-new-style');
		}
	}

	function myplugin_admin_styles() {
		wp_enqueue_style( 'whats-new-style' );
	}

	function on_activation() {
		$tmp = get_option($this->option_name);
		if(!is_array($tmp)) {
			$arr = array(
					"wng_title" => "新着情報",
					"wng_content_type" => "投稿",
					"wng_orderby" => "公開日順",
					"wng_category_chk" => "",
					"wng_category_name" => "",
					"wng_background_color" => "#f5f5f5",
					"wng_number" => "10"
			);
			update_option($this->option_name, $arr);
		}
	}

	function on_admin_init() {
		register_setting($this->option_name, $this->option_name);
		add_settings_section('main_section', '表示設定', array(&$this,'section_text_fn'), __FILE__);
		add_settings_field('wng_title', 'タイトル', array(&$this,'setting_title'), __FILE__, 'main_section');
		add_settings_field('wng_background_color', 'タイトル背景色', array(&$this,'setting_background_color'), __FILE__, 'main_section');
		add_settings_field('wng_content_type', '表示するコンテンツ', array(&$this,'setting_content_type'), __FILE__, 'main_section');
		add_settings_field('wng_category', '　カテゴリーを指定する', array(&$this,'setting_category_chk'), __FILE__, 'main_section');
		add_settings_field('wng_category_name', '　カテゴリーのスラッグ', array(&$this,'setting_category_name'), __FILE__, 'main_section');
		add_settings_field('wng_orderby', '表示順序', array(&$this,'setting_orderby'), __FILE__, 'main_section');
		add_settings_field('wng_number', '表示件数', array(&$this,'setting_number'), __FILE__, 'main_section');
		wp_register_style( 'whats-new-style', plugins_url('whats-new.css', __FILE__) );
	}


	public function on_admin_menu() {
		$page = add_options_page("What's New 設定", "What's New 設定", 'administrator', __FILE__, array(&$this, 'show_admin_page'));
		add_action( 'admin_print_styles-' . $page, array(&$this,'myplugin_admin_styles') );
	}

	public function show_admin_page() {
		$file = __FILE__;
		$option_name = $this->option_name;
		$shortcode = "[" . $this->shortcode . "]";
		include_once('admin-view.php');
		$this->show_whatsnew();
	}

	function show_whatsnew(){
		$info = new WhatsNewInfo($this->option_name);
		include('whatsnew-view.php');
	}

	function show_shortcode(){
		ob_start();
		$this->show_whatsnew();
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}

	// Section HTML, displayed before the first option
	function  section_text_fn() {
		//echo '<p>Below are some examples of different option controls.</p>';
	}

	// TEXTBOX - Name: whats_new_options[text_string]
	function setting_title() {
		$options = get_option($this->option_name);
		$value = esc_html( $options["wng_title"] );
		echo "<input id='wng_title' name='whats_new_options[wng_title]' size='40' type='text' value='{$value}' />";
	}

	function setting_background_color() {
		$options = get_option($this->option_name);
		$value = esc_html( $options["wng_background_color"] );
		echo "<input id='wng_background_color' name='whats_new_options[wng_background_color]' size='10' type='text' value='{$value}' />";
	}


	function setting_category_chk() {
		$id = "wng_category_chk";
		$options = get_option($this->option_name);
		$checked = (isset($options[$id]) && $options[$id]) ? $checked = ' checked="checked" ': "";
		$name = $this->option_name. "[$id]";

		echo "<input ".$checked." id='id_".$id."' name='".$name."' type='checkbox' />";
	}

	// TEXTBOX - Name: whats_new_options[text_string]
	function setting_category_name() {
		$options = get_option($this->option_name);
		$value = esc_html( $options["wng_category_name"] );
		echo "<input id='wng_category_name' name='whats_new_options[wng_category_name]' size='40' type='text' value='{$value}' />";
	}


	// DROP-DOWN-BOX - Name: whats_new_options[dropdown1]
	function  setting_number() {
		$options = get_option($this->option_name);
		$items = array("5", "10", "15", "20");
		echo "<select id='wng_number' name='whats_new_options[wng_number]'>";
		foreach($items as $item) {
			$selected = ($options['wng_number']==$item) ? 'selected="selected"' : '';
			echo "<option value='$item' $selected>$item</option>";
		}
		echo "</select>";
	}

	// RADIO-BUTTON - Name: whats_new_options[option_set1]
	function setting_content_type() {
		$options = get_option($this->option_name);
		$items = array("投稿", "固定ページ");
		foreach($items as $item) {
			$checked = ($options['wng_content_type']==$item) ? ' checked="checked" ' : '';
			echo "<label><input ".$checked." value='$item' name='whats_new_options[wng_content_type]' type='radio' /> $item</label><br />";
		}
	}

	function setting_orderby() {
		$options = get_option($this->option_name);
		$items = array("公開日順", "更新日順");
		foreach($items as $item) {
			$checked = ($options['wng_orderby']==$item) ? ' checked="checked" ' : '';
			echo "<label><input ".$checked." value='$item' name='whats_new_options[wng_orderby]' type='radio' /> $item</label><br />";
		}
	}
}

/**
 * What's New に表示する内容
 *
 */
class WhatsNewInfo{
	var $title;
	var $num;
	var $content_type;
	var $orderby;
	var $category_chk;
	var $category_name;
	var $background_color;
	var $items = array();

	public function __construct($option_name){
		$options = get_option($option_name);
		$this->num = $options['wng_number'];
		$this->content_type = $options['wng_content_type'];
		$this->orderby = $options['wng_orderby'];
		$this->title = esc_html( $options['wng_title'] );
		$this->category_chk = isset($options['wng_category_chk']) ? $options['wng_category_chk'] : "";
		$this->category_name = $options['wng_category_name'];
		$this->background_color = $options['wng_background_color'];
		$this->createWhatsNewItems();
	}

	private function createWhatsNewItems(){
		$condition = array();
		$condition['post_type'] = $this->content_type == '投稿' ? 'post' : 'page';
		$condition['numberposts'] = $this->num;
		$condition['order'] = 'desc';
		$condition['orderby'] = $this->orderby == '公開日順' ? 'post_date' : 'modified';
		if ( $this->content_type == '投稿' && $this->category_chk == 'on' && $this->category_name != ""){
			$condition['category_name'] = $this->category_name;
		}

		$posts = get_posts( $condition );
		if ( !is_array($posts) ){
			return;
		}
		foreach($posts as $post){
			$this->items[] = new WhatsNewItem($post, $this->orderby);
		}
	}
}

/**
 * 個々のWhat's New項目の内容
 *
 */
class WhatsNewItem{
	var $date;
	var $title;
	var $url;

	public function __construct( $post, $orderby ){
		$this->date = $orderby == '公開日順' ? $post->post_date : $post->post_modified;
		$this->date = date("Y年n月j日", strtotime($this->date));
		$this->title = esc_html( $post->post_title );
		$this->url = get_permalink($post->ID);
	}
}
?>