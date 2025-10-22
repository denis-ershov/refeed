<?php
/**
 * Plugin Name: ReFeed
 * Plugin URI: https://github.com/denis-ershov/refeed
 * Description: Создает кастомную RSS-ленту с возможностью указания источника из мета-полей
 * Version: 1.0.0
 * Author: Denis Ershov
 * Author URI: https://github.com/denis-ershov
 * Text Domain: refeed
 */

// Защита от прямого доступа
if (!defined('ABSPATH')) {
    exit;
}

class ReFeed {
    
    private $option_name = 'refeed_settings';
    
    public function __construct() {
        // Добавляем RSS endpoint
        add_action('init', array($this, 'add_rss_endpoint'));
        
        // Обрабатываем запрос к RSS
        add_action('template_redirect', array($this, 'handle_rss_request'));
        
        // Добавляем страницу настроек
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // Активация плагина
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Активация плагина
     */
    public function activate() {
        $this->add_rss_endpoint();
        flush_rewrite_rules();
        
        // Установка настроек по умолчанию
        if (!get_option($this->option_name)) {
            $defaults = array(
                'feed_title' => get_bloginfo('name'),
                'feed_description' => get_bloginfo('description'),
                'feed_language' => get_locale(),
                'feed_copyright' => 'Copyright ' . date('Y') . ' ' . get_bloginfo('name'),
                'managing_editor' => get_option('admin_email') . ' (' . get_bloginfo('name') . ')',
                'webmaster' => get_option('admin_email') . ' (Webmaster)',
                'posts_per_feed' => 10,
                'post_types' => array('post'),
                'source_meta_key' => 'original_source_link',
                'author_meta_key' => '',
                'date_meta_key' => ''
            );
            update_option($this->option_name, $defaults);
        }
    }
    
    /**
     * Деактивация плагина
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Добавляем RSS endpoint
     */
    public function add_rss_endpoint() {
        add_rewrite_rule('^refeed/?$', 'index.php?custom_rss_feed=1', 'top');
        add_rewrite_tag('%custom_rss_feed%', '([^&]+)');
    }
    
    /**
     * Обработка запроса к RSS
     */
    public function handle_rss_request() {
        if (get_query_var('custom_rss_feed')) {
            $this->generate_rss();
            exit;
        }
    }
    
    /**
     * Генерация RSS-ленты
     */
    private function generate_rss() {
        $settings = get_option($this->option_name);
        
        // Получаем записи
        $args = array(
            'post_type' => $settings['post_types'],
            'posts_per_page' => intval($settings['posts_per_feed']),
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $posts = get_posts($args);
        
        // Устанавливаем правильные заголовки
        header('Content-Type: application/rss+xml; charset=UTF-8');
        
        // Генерируем XML
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <channel>
    <title><?php echo esc_html($settings['feed_title']); ?></title>
    <link><?php echo esc_url(home_url('/')); ?></link>
    <description><?php echo esc_html($settings['feed_description']); ?></description>
    <language><?php echo esc_html($settings['feed_language']); ?></language>
    <copyright><?php echo esc_html($settings['feed_copyright']); ?></copyright>
    <managingEditor><?php echo esc_html($settings['managing_editor']); ?></managingEditor>
    <webMaster><?php echo esc_html($settings['webmaster']); ?></webMaster>
    <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></pubDate>
    <lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', current_time('mysql', true), false); ?></lastBuildDate>
    <generator>WordPress <?php echo get_bloginfo('version'); ?> with ReFeed Plugin</generator>
    <atom:link href="<?php echo esc_url(home_url('/refeed')); ?>" rel="self" type="application/rss+xml"/>
    
<?php foreach ($posts as $post): 
    setup_postdata($post);
    
    // Получаем ссылку на источник из мета-поля
    $source_link = '';
    if (!empty($settings['source_meta_key'])) {
        $source_link = get_post_meta($post->ID, $settings['source_meta_key'], true);
    }
    
    // Если источник не указан, используем permalink
    if (empty($source_link)) {
        $source_link = get_permalink($post->ID);
    }
    
    // Получаем автора
    $author = '';
    if (!empty($settings['author_meta_key'])) {
        $author = get_post_meta($post->ID, $settings['author_meta_key'], true);
    }
    if (empty($author)) {
        $author_obj = get_userdata($post->post_author);
        $author = $author_obj->display_name;
    }
    
    // Получаем дату
    $pub_date = '';
    if (!empty($settings['date_meta_key'])) {
        $pub_date = get_post_meta($post->ID, $settings['date_meta_key'], true);
        if (!empty($pub_date)) {
            $pub_date = mysql2date('D, d M Y H:i:s +0000', $pub_date, false);
        }
    }
    if (empty($pub_date)) {
        $pub_date = mysql2date('D, d M Y H:i:s +0000', $post->post_date_gmt, false);
    }
    
    // Формат ISO для dc:date
    $dc_date = '';
    if (!empty($settings['date_meta_key'])) {
        $dc_date_raw = get_post_meta($post->ID, $settings['date_meta_key'], true);
        if (!empty($dc_date_raw)) {
            $dc_date = mysql2date('Y-m-d\TH:i:s\Z', $dc_date_raw, false);
        }
    }
    if (empty($dc_date)) {
        $dc_date = mysql2date('Y-m-d\TH:i:s\Z', $post->post_date_gmt, false);
    }
?>
    <item>
      <title><?php echo esc_html($post->post_title); ?></title>
      <link><?php echo esc_url(get_permalink($post->ID)); ?></link>
      <description><![CDATA[<?php echo wpautop($post->post_excerpt ? $post->post_excerpt : wp_trim_words($post->post_content, 55)); ?>]]></description>
      <guid isPermaLink="true"><?php echo esc_url($source_link); ?></guid>
      <author><?php echo esc_html($author); ?></author>
      <dc:creator><?php echo esc_html($author); ?></dc:creator>
      <pubDate><?php echo $pub_date; ?></pubDate>
      <dc:date><?php echo $dc_date; ?></dc:date>
    </item>
<?php endforeach; 
    wp_reset_postdata();
?>
  </channel>
</rss>
        <?php
    }
    
    /**
     * Добавляем страницу настроек
     */
    public function add_settings_page() {
        add_options_page(
            'ReFeed Settings',
            'ReFeed',
            'manage_options',
            'refeed-settings',
            array($this, 'settings_page_html')
        );
    }
    
    /**
     * Регистрируем настройки
     */
    public function register_settings() {
        register_setting('refeed_group', $this->option_name, array($this, 'sanitize_settings'));
        
        // Секция основных настроек
        add_settings_section(
            'refeed_main_section',
            'Основные настройки RSS-ленты',
            array($this, 'section_main_callback'),
            'refeed-settings'
        );
        
        // Поля настроек
        $fields = array(
            'feed_title' => 'Название RSS канала',
            'feed_description' => 'Описание RSS канала',
            'feed_language' => 'Язык (например: ru, en)',
            'feed_copyright' => 'Copyright',
            'managing_editor' => 'Редактор (email и имя)',
            'webmaster' => 'Веб-мастер (email и имя)',
            'posts_per_feed' => 'Количество записей в ленте',
        );
        
        foreach ($fields as $field => $label) {
            add_settings_field(
                $field,
                $label,
                array($this, 'field_callback'),
                'refeed-settings',
                'refeed_main_section',
                array('field' => $field, 'label' => $label)
            );
        }
        
        // Секция настроек мета-полей
        add_settings_section(
            'refeed_meta_section',
            'Настройки кастомных полей',
            array($this, 'section_meta_callback'),
            'refeed-settings'
        );
        
        $meta_fields = array(
            'source_meta_key' => 'Мета-поле для ссылки на источник (guid)',
            'author_meta_key' => 'Мета-поле для автора (опционально)',
            'date_meta_key' => 'Мета-поле для даты публикации (опционально)',
        );
        
        foreach ($meta_fields as $field => $label) {
            add_settings_field(
                $field,
                $label,
                array($this, 'field_callback'),
                'refeed-settings',
                'refeed_meta_section',
                array('field' => $field, 'label' => $label)
            );
        }
        
        // Секция типов записей
        add_settings_section(
            'refeed_post_types_section',
            'Типы записей',
            array($this, 'section_post_types_callback'),
            'refeed-settings'
        );
        
        add_settings_field(
            'post_types',
            'Типы записей для включения в RSS',
            array($this, 'post_types_callback'),
            'refeed-settings',
            'refeed_post_types_section'
        );
    }
    
    /**
     * Callback для секций
     */
    public function section_main_callback() {
        echo '<p>Настройте основные параметры вашей RSS-ленты.</p>';
    }
    
    public function section_meta_callback() {
        echo '<p>Укажите названия кастомных полей (meta_key), из которых будут браться данные для RSS-ленты.</p>';
        echo '<p><strong>Важно:</strong> Если поле не указано или пустое, будут использоваться стандартные данные WordPress.</p>';
    }
    
    public function section_post_types_callback() {
        echo '<p>Выберите типы записей, которые будут включены в RSS-ленту.</p>';
    }
    
    /**
     * Callback для полей
     */
    public function field_callback($args) {
        $settings = get_option($this->option_name);
        $field = $args['field'];
        $value = isset($settings[$field]) ? $settings[$field] : '';
        
        if ($field === 'posts_per_feed') {
            echo '<input type="number" name="' . $this->option_name . '[' . $field . ']" value="' . esc_attr($value) . '" min="1" max="100" />';
        } elseif ($field === 'feed_description') {
            echo '<textarea name="' . $this->option_name . '[' . $field . ']" rows="3" cols="50">' . esc_textarea($value) . '</textarea>';
        } else {
            echo '<input type="text" name="' . $this->option_name . '[' . $field . ']" value="' . esc_attr($value) . '" size="50" />';
        }
        
        if (in_array($field, array('source_meta_key', 'author_meta_key', 'date_meta_key'))) {
            echo '<p class="description">Например: original_source_link, source_author, original_date</p>';
        }
    }
    
    /**
     * Callback для типов записей
     */
    public function post_types_callback() {
        $settings = get_option($this->option_name);
        $selected = isset($settings['post_types']) ? $settings['post_types'] : array('post');
        
        $post_types = get_post_types(array('public' => true), 'objects');
        
        foreach ($post_types as $post_type) {
            $checked = in_array($post_type->name, $selected) ? 'checked' : '';
            echo '<label><input type="checkbox" name="' . $this->option_name . '[post_types][]" value="' . esc_attr($post_type->name) . '" ' . $checked . ' /> ' . esc_html($post_type->label) . '</label><br>';
        }
    }
    
    /**
     * Санитизация настроек
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['feed_title'] = sanitize_text_field($input['feed_title']);
        $sanitized['feed_description'] = sanitize_textarea_field($input['feed_description']);
        $sanitized['feed_language'] = sanitize_text_field($input['feed_language']);
        $sanitized['feed_copyright'] = sanitize_text_field($input['feed_copyright']);
        $sanitized['managing_editor'] = sanitize_text_field($input['managing_editor']);
        $sanitized['webmaster'] = sanitize_text_field($input['webmaster']);
        $sanitized['posts_per_feed'] = absint($input['posts_per_feed']);
        $sanitized['source_meta_key'] = sanitize_key($input['source_meta_key']);
        $sanitized['author_meta_key'] = sanitize_key($input['author_meta_key']);
        $sanitized['date_meta_key'] = sanitize_key($input['date_meta_key']);
        
        if (isset($input['post_types']) && is_array($input['post_types'])) {
            $sanitized['post_types'] = array_map('sanitize_key', $input['post_types']);
        } else {
            $sanitized['post_types'] = array('post');
        }
        
        return $sanitized;
    }
    
    /**
     * HTML страницы настроек
     */
    public function settings_page_html() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (isset($_GET['settings-updated'])) {
            flush_rewrite_rules();
            add_settings_error('refeed_messages', 'refeed_message', 'Настройки сохранены', 'updated');
        }
        
        settings_errors('refeed_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="notice notice-info">
                <p><strong>Ваша RSS-лента доступна по адресу:</strong> <a href="<?php echo esc_url(home_url('/refeed')); ?>" target="_blank"><?php echo esc_url(home_url('/refeed')); ?></a></p>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('refeed_group');
                do_settings_sections('refeed-settings');
                submit_button('Сохранить настройки');
                ?>
            </form>
            
            <hr>
            
            <h2>Инструкция по использованию</h2>
            <ol>
                <li>Укажите название мета-поля для ссылки на источник (например: <code>original_source_link</code>)</li>
                <li>Добавьте это кастомное поле к вашим записям</li>
                <li>В значение поля укажите URL источника</li>
                <li>Этот URL будет использован в теге <code>&lt;guid&gt;</code> RSS-ленты</li>
                <li>Опционально можете указать мета-поля для автора и даты публикации</li>
            </ol>
            
            <h3>Пример добавления мета-поля программно:</h3>
            <pre><code>// Добавление мета-поля к записи
update_post_meta($post_id, 'original_source_link', 'https://example.com/news/1');
update_post_meta($post_id, 'source_author', 'Имя Автора');
update_post_meta($post_id, 'original_date', '2024-01-01 12:00:00');</code></pre>
        </div>
        <?php
    }
}

// Инициализация плагина
new ReFeed();
