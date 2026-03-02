# 📡 RSS 2.0 — Полная структура

```xml
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:dc="http://purl.org/dc/elements/1.1/">

  <channel>
    <title>SITE_NAME</title>
    <link>https://example.com/</link>
    <description>Краткое описание проекта или новостного портала</description>
    <language>ru_RU</language>
    <copyright>Copyright YEAR SITE_NAME</copyright>
    <managingEditor>editor@example.com (SITE_NAME)</managingEditor>
    <webMaster>webmaster@example.com (Webmaster)</webMaster>
    <pubDate>Thu, 01 Jan 2026 00:00:00 +0000</pubDate>
    <lastBuildDate>Thu, 01 Jan 2026 00:00:00 +0000</lastBuildDate>
    <generator>CMS_NAME VERSION</generator>

    <atom:link href="https://example.com/feed"
               rel="self"
               type="application/rss+xml"/>

    <item>
      <title>NEWS_TITLE</title>
      <link>https://example.com/category/article-slug-ID/</link>

      <description><![CDATA[
        <p>SHORT_DESCRIPTION</p>
      ]]></description>

      <guid isPermaLink="true">
        https://source.example.com/article-id
      </guid>

      <author>AUTHOR_NAME</author>
      <dc:creator>AUTHOR_NAME</dc:creator>

      <pubDate>Thu, 01 Jan 2026 00:00:00 +0000</pubDate>
      <dc:date>2026-01-01T00:00:00Z</dc:date>

      <createdDate>Thu, 01 Jan 2026 00:00:00 +0000</createdDate>
      <dc:created>2026-01-01T00:00:00Z</dc:created>

      <lastModifiedBy>EDITOR_ID</lastModifiedBy>
      <dc:lastModifiedBy>EDITOR_ID</dc:lastModifiedBy>
    </item>

  </channel>
</rss>
```

---

# 📘 Описание структуры

## 🔹 Корневой элемент

### `<?xml version="1.0" encoding="UTF-8"?>`

* Объявление XML-документа
* `version="1.0"` — версия XML
* `encoding="UTF-8"` — кодировка (рекомендуется всегда UTF-8)

---

## 🔹 `<rss>`

```xml
<rss version="2.0"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:dc="http://purl.org/dc/elements/1.1/">
```

* `version="2.0"` — стандарт RSS 2.0
* `xmlns:atom` — подключение Atom namespace (для self-ссылки фида)
* `xmlns:dc` — Dublin Core (расширенные метаданные)

---

# 📂 `<channel>` — данные всего сайта

Описывает сам новостной поток.

---

## Основные поля канала

### `<title>`

Название сайта или проекта.

---

### `<link>`

Главная ссылка сайта (НЕ ссылка на RSS).

---

### `<description>`

Краткое описание сайта (1–2 предложения).

---

### `<language>`

Язык контента.

Примеры:

* `ru_RU`
* `en_US`
* `es_ES`

---

### `<copyright>`

Информация об авторских правах.

---

### `<managingEditor>`

Email редактора + название проекта.

Формат:

```
email@example.com (Название сайта)
```

---

### `<webMaster>`

Email технического администратора.

---

### `<pubDate>`

Дата публикации канала.

Формат RFC 822:

```
Thu, 01 Jan 2026 00:00:00 +0000
```

---

### `<lastBuildDate>`

Дата последнего обновления RSS.

Обновляется при добавлении новой статьи.

---

### `<generator>`

CMS или система генерации RSS.

---

## 🔹 `<atom:link>`

```xml
<atom:link href="https://example.com/feed"
           rel="self"
           type="application/rss+xml"/>
```

* `href` — ссылка на текущий RSS
* `rel="self"` — ссылка на сам фид
* `type="application/rss+xml"` — MIME тип

---

# 📰 `<item>` — отдельная новость

Каждый `<item>` — это одна статья.

---

## Поля статьи

### `<title>`

Заголовок статьи.

---

### `<link>`

Ссылка на статью на вашем сайте.

---

### `<description>`

Краткое описание статьи(анонс).

Если используется HTML — обязательно через CDATA:

```xml
<description><![CDATA[
  <p>Краткое описание</p>
]]></description>
```

---

### `<guid>`

Ссылка на оригинал новости(откуда взять новость, а не ссылка на ваш сайт).

* `isPermaLink="true"` — если это URL

---

### `<author>`

Автор статьи(например Egor Repin).

---

### `<dc:creator>`

Имя автора (расширение Dublin Core).

---

### `<pubDate>`

Дата публикации статьи.

Формат:

```
Thu, 01 Jan 2026 00:00:00 +0000
```

---

### `<dc:date>`

Дата в формате ISO 8601:

```
2026-01-01T00:00:00Z
```

---

### `<createdDate>`

Дата создания записи в CMS (необязательное поле).
Именно дата создания, а не дата публикации.

---

### `<dc:created>`

ISO-версия даты создания.

---

### `<lastModifiedBy>`

Slug автора.

---

### `<dc:lastModifiedBy>`

Slug автора(Расширенная версия Dublin Core).