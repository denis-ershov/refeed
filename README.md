# ReFeed - WordPress Plugin

Плагин для создания кастомной RSS-ленты с возможностью указания источника и других параметров из мета-полей WordPress.

## Автор

**Denis Ershov**

## Описание

Плагин создает RSS-ленту по адресу `https://yourdomain.com/refeed` с полной поддержкой:
- Кастомных мета-полей для ссылки на источник
- Кастомных полей для автора и даты публикации
- Гибких настроек через админ-панель
- Поддержки нескольких типов записей
- Полного соответствия стандарту RSS 2.0

## Установка

### Способ 1: Через админ-панель WordPress

1. Скачайте файл `refeed.php`
2. Войдите в админ-панель WordPress
3. Перейдите в **Плагины → Добавить новый**
4. Нажмите **Загрузить плагин**
5. Выберите файл и нажмите **Установить**
6. Активируйте плагин

### Способ 2: Через FTP

1. Загрузите файл `refeed.php` в папку `/wp-content/plugins/refeed/`
2. Войдите в админ-панель WordPress
3. Перейдите в **Плагины**
4. Найдите **ReFeed** и активируйте

## Настройка

После активации плагина:

1. Перейдите в **Настройки → ReFeed**
2. Настройте основные параметры:
   - Название RSS канала
   - Описание
   - Язык
   - Copyright
   - Контакты редактора и веб-мастера
   - Количество записей в ленте

3. **Важно!** Укажите название мета-полей:
   - **Мета-поле для ссылки на источник** (обязательно для `<guid>`)
   - Мета-поле для автора (опционально)
   - Мета-поле для даты (опционально)

4. Выберите типы записей для включения в RSS
5. Сохраните настройки

## Использование

### Доступ к RSS-ленте

Ваша RSS-лента будет доступна по адресу:
```
https://yourdomain.com/refeed
```

### Добавление мета-полей к записям

#### Через админ-панель (с плагином Advanced Custom Fields или аналогичным):

1. Установите плагин для работы с кастомными полями
2. Создайте поля с названиями, указанными в настройках
3. Заполните их при создании/редактировании записи

#### Программно (в functions.php или плагине):

```php
// Добавление мета-полей при создании записи
$post_id = wp_insert_post(array(
    'post_title' => 'Заголовок новости',
    'post_content' => 'Содержание...',
    'post_status' => 'publish'
));

// Добавляем ссылку на источник (обязательно)
update_post_meta($post_id, 'original_source_link', 'https://example.com/news/1');

// Опционально: автор
update_post_meta($post_id, 'source_author', 'Имя Автора');

// Опционально: дата публикации источника
update_post_meta($post_id, 'original_date', '2024-01-01 12:00:00');
```

#### Массовое добавление через SQL:

```sql
-- Добавить мета-поле ко всем записям
INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
SELECT ID, 'original_source_link', CONCAT('https://example.com/news/', ID)
FROM wp_posts
WHERE post_type = 'post' AND post_status = 'publish';
```

## Формат RSS-ленты

Плагин генерирует RSS в следующем формате:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <channel>
    <title>Название RSS канала</title>
    <link>https://yourdomain.com</link>
    <description>Описание RSS канала</description>
    <language>ru</language>
    <copyright>Copyright 2024</copyright>
    <managingEditor>editor@example.com (Имя редактора)</managingEditor>
    <webMaster>webmaster@example.com (Веб-мастер)</webMaster>
    <pubDate>Mon, 01 Jan 2024 12:00:00 GMT</pubDate>
    <lastBuildDate>Mon, 01 Jan 2024 12:00:00 GMT</lastBuildDate>
    <generator>WordPress with ReFeed Plugin</generator>
    <atom:link href="https://yourdomain.com/refeed" rel="self" type="application/rss+xml"/>
    
    <item>
      <title>Заголовок новости</title>
      <link>https://yourdomain.com/articles/news-1</link>
      <description><![CDATA[Краткое описание...]]></description>
      <guid isPermaLink="true">https://example.com/news/1</guid>
      <author>editor@example.com (Имя Автора)</author>
      <dc:creator>Имя Автора</dc:creator>
      <pubDate>Mon, 01 Jan 2024 12:00:00 GMT</pubDate>
      <dc:date>2024-01-01T12:00:00Z</dc:date>
    </item>
  </channel>
</rss>
```

## Важные моменты

### Поле `<guid>` (источник)

- **Если указано мета-поле** — используется значение из мета-поля
- **Если мета-поле пустое** — используется permalink записи WordPress
- Это поле обязательно заполнено в любом случае

### Поле `<link>` (ссылка на запись)

- **Всегда** содержит permalink записи на вашем сайте
- Формат: `https://russpain.com/articles/news-1`

### Различие между `<link>` и `<guid>`

- `<link>` — ссылка на запись на **вашем сайте**
- `<guid>` — ссылка на **оригинальный источник** (если указан в мета-поле)

### Автор и дата

- Если мета-поля не указаны, используются стандартные данные WordPress
- Дата автоматически форматируется в правильный RFC 822 формат

## Troubleshooting

### RSS-лента не открывается (404 ошибка)

1. Перейдите в **Настройки → Постоянные ссылки**
2. Нажмите **Сохранить изменения** (это обновит rewrite rules)
3. Попробуйте снова открыть `/refeed`

### Мета-поля не отображаются в RSS

1. Проверьте правильность написания meta_key в настройках
2. Убедитесь, что мета-поля действительно существуют у записей:
```php
// Проверка в коде
$value = get_post_meta($post_id, 'original_source_link', true);
var_dump($value);
```

### RSS показывает неправильную кодировку

Убедитесь, что:
1. Файл плагина сохранен в UTF-8 без BOM
2. В WordPress установлена правильная кодировка (Settings → Reading)

## Дополнительные возможности

### Фильтрация записей

Если нужно добавить дополнительную фильтрацию записей, можно использовать хуки WordPress:

```php
// В functions.php или в отдельном плагине
add_filter('pre_get_posts', function($query) {
    if (isset($query->query_vars['custom_rss_feed'])) {
        // Например, показывать только записи из определенной категории
        $query->set('cat', 5);
    }
    return $query;
});
```

### Кастомизация вывода

Можно изменить код плагина в методе `generate_rss()` для добавления дополнительных полей RSS.

## Поддержка

Для вопросов и поддержки:
- GitHub: https://github.com/denis-ershov/refeed
- Author: Denis Ershov

## Лицензия

GPL v3 or later

## Changelog

### Version 1.0.0
- Первый релиз
- Поддержка кастомных мета-полей
- Настройка через админ-панель
- Поддержка нескольких типов записей
- Полная поддержка RSS 2.0, Atom и Dublin Core
