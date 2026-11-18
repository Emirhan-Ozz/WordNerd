# WordNerd

Türkçe ve İngilizce kelime tahmin oyunu. 5 harfli gizli kelimeyi 6 denemede bul.

İzmir Ekonomi Üniversitesi — MBP281 İnternet Programcılığı II dönem projesi. Made by Emirhan Öz.

Repo: https://github.com/Emirhan-Ozz/WordNerd

## Teknik yapı

- **PHP**: Sayfalar, oturum (giriş), tahmin kontrolü ve tüm kayıtlar
- **JSON dosyaları** (`data/`): Veritabanı yerine kullanıcılar, skorlar ve kelime listeleri
- **localStorage**: Tema, arayüz dili, son seçilen oyun modu ve yarım kalan oyun

## Klasör yapısı

```
WordNerd/
├── index.php          Oyun ekranı
├── login.php          Giriş
├── register.php       Kayıt (ilk kayıt olan kullanıcı yönetici olur)
├── logout.php         Çıkış
├── stats.php          Kişisel istatistikler
├── leaderboard.php    Liderlik tablosu
├── admin.php          Yönetim paneli (kelimeler, kullanıcılar, skorlar)
├── api/game.php       Oyun API'si (start, guess) — JSON alır, JSON döner
├── config.php         Ayarlar, session, include'lar
├── includes/
│   ├── helpers.php    e(), lower(), upper(), letters(), json_response()
│   ├── storage.php    read_json(), write_json()
│   ├── words.php      Kelime listeleri, günlük/rastgele kelime
│   ├── auth.php       Kayıt, giriş, yetki, CSRF
│   ├── game.php       Renk hesaplama, zor mod, oyun durumu
│   ├── scores.php     Skor kaydı, istatistik, liderlik
│   ├── filters.php    İstatistik/liderlik filtre formu
│   └── layout.php     Ortak başlık ve altlık
├── data/              (tarayıcıdan erişime kapalı)
│   ├── users.json
│   ├── scores.json
│   └── words/words_{tr|en}_5.json
└── assets/
    ├── css/style.css  5 tema: klasik, aqua, neon, granit, claude
    └── js/
        ├── i18n.js    TR/EN arayüz çevirileri
        ├── common.js  Tema, arayüz dili, filtre hafızası (localStorage)
        └── game.js    Tahta, klavye, API istekleri, paylaşma
```

## localStorage anahtarları

| Anahtar | Ne tutar |
|---|---|
| `wn_theme` | Seçili tema |
| `wn_ui_lang` | Arayüz dili (tr/en) |
| `wn_game_settings` | Son seçilen kelime dili, harf sayısı, mod, zor mod |
| `wn_daily_{dil}_{n}` | Bugünkü günlük oyunun tahminleri (yarım oyun yedeği) |
| `wn_stats_filter`, `wn_lb_filter` | İstatistik ve liderlik sayfasında son kullanılan filtre |

## Çalıştırma (XAMPP)

1. Klasörü `C:\xampp\htdocs\WordNerd` içine koy.
2. XAMPP Control Panel'den **Apache**'yi başlat.
3. Tarayıcıda `http://localhost/WordNerd/` adresini aç.

## XAMPP olmadan çalıştırma

PHP kuruluysa (7.4 veya üstü, `mbstring` eklentisi açık) proje klasöründe:

```
php -S localhost:8000 router.php
```

Sonra tarayıcıda `http://localhost:8000` adresini aç. `router.php`, Apache'deki `.htaccess` kurallarının yerine geçer ve `data/` ile `includes/` klasörlerini tarayıcıya kapatır.

## Kelime listesi kaynakları

- Türkçe sözlük: [mertemin/turkish-word-list](https://github.com/mertemin/turkish-word-list)
- Kelime sıklıkları: [hermitdave/FrequencyWords](https://github.com/hermitdave/FrequencyWords)
- İngilizce: [dwyl/english-words](https://github.com/dwyl/english-words), [dolph/dictionary (ENABLE)](https://github.com/dolph/dictionary), [tabatkins/wordle-list](https://github.com/tabatkins/wordle-list)
- Uygunsuz kelime filtresi: [LDNOOBW](https://github.com/LDNOOBW/List-of-Dirty-Naughty-Obscene-and-Otherwise-Bad-Words)
