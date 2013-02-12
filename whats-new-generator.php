<?php
/*
Plugin Name: What's New Generator
Plugin URI: http://residentbird.main.jp/bizplugin/
Description: What's New(新着情報)を指定した固定ページや投稿に自動的に表示するプラグインです。
Version: 1.6.0
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
		wp_deregister_style('whats-new-style');
	}

	function on_print_styles() {
		$cssPath = WP_PLUGIN_DIR . '/whats-new-genarator/whats-new.css';

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
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'whats-new-admin-js', plugins_url('whats-new-admin.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
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
					"wng_newmark" => "7",
					"wng_number" => "10"
			);
			update_option($this->option_name, $arr);
		}
	}

	function on_admin_init() {
		register_setting($this->option_name, $this->option_name, array ( &$this, 'validate' ));
		add_settings_section('main_section', '表示設定', array(&$this,'section_text_fn'), __FILE__);
		add_settings_field('wng_title', 'タイトル', array(&$this,'setting_title'), __FILE__, 'main_section');
		add_settings_field('wng_background_color', 'タイトル背景色', array(&$this,'setting_background_color'), __FILE__, 'main_section');
		add_settings_field('wng_content_type', '表示するコンテンツ', array(&$this,'setting_content_type'), __FILE__, 'main_section');
		add_settings_field('wng_category', '　カテゴリーを指定する', array(&$this,'setting_category_chk'), __FILE__, 'main_section');
		add_settings_field('wng_category_name', '　カテゴリーのスラッグ', array(&$this,'setting_category_name'), __FILE__, 'main_section');
		add_settings_field('wng_orderby', '表示順序', array(&$this,'setting_orderby'), __FILE__, 'main_section');
		add_settings_field('wng_number', '表示件数', array(&$this,'setting_number'), __FILE__, 'main_section');
		add_settings_field('wng_newmark', 'NEW!マーク表示期間', array(&$this,'setting_newmark'), __FILE__, 'main_section');
		wp_register_style( 'whats-new-style', plugins_url('whats-new.css', __FILE__) );
	}

	function validate($input) {
		if(!preg_match('/^#[a-f0-9]{6}$/i', $input['wng_background_color'])){
			$input['wng_background_color'] = "#f5f5f5";
		}
		if ( !is_numeric( $input['wng_newmark']) || $input['wng_newmark'] < 0){
			$input['wng_newmark'] = 0;
		}
		if ($input['wng_newmark'] > 30 ){
			$input['wng_newmark'] = 30;
		}
		return $input; // return validated input
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

	function setting_newmark() {
		$options = get_option($this->option_name);
		$value = esc_html( $options["wng_newmark"] );
		echo "<input id='wng_newmark' name='whats_new_options[wng_newmark]' size='2' type='text' value='{$value}' />日間";
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
		$items = array("3", "5", "7","10", "15", "20");
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
		$items = array("投稿", "固定ページ", "投稿＋固定ページ");
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
		$this->createWhatsNewItems( $option_name );
	}

	private function createWhatsNewItems($option_name){
		$condition = array();
		if ( $this->content_type == '投稿'){
			$condition['post_type'] = 'post';
		}else if ( $this->content_type == '固定ページ' ){
			$condition['post_type'] = 'page';
		}else{
			$condition['post_type'] = array('page', 'post');
		}
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
			$this->items[] = new WhatsNewItem($post, $this->orderby ,$option_name );
		}
	}
}

/**
 * 個々のWhat's New項目の内容
 *
 */
class WhatsNewItem{
	var $date;
	var $raw_date;
	var $title;
	var $url;
	var $newmark;

	public function __construct( $post, $orderby, $option_name ){
		$this->raw_date = $orderby == '公開日順' ? $post->post_date : $post->post_modified;
		$this->date = date("Y年n月j日", strtotime($this->raw_date));
		$this->title = esc_html( $post->post_title );
		$this->url = get_permalink($post->ID);
		$this->newmark = $this->is_new( $option_name );
	}

	public function is_new( $option_name ){
		$options = get_option($option_name);
		$term = $options['wng_newmark'];
		if ( !isset($term) || $term == 0){
			return false;
		}
		$today = date_i18n('U');
		$post_date = date('U', strtotime($this->raw_date));
		$diff = ( $today - $post_date ) / ( 24 * 60 * 60 );
		if ($term > $diff){
			return true;
		}
		return false;
	}
}
?>