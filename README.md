# WordNerd

Türkçe ve İngilizce kelime tahmin oyunu. 5 harfli gizli kelimeyi 6 denemede bul.

## Klasör yapısı

```
WordNerd/
├── index.php            Oyun ekranı
├── login.php            Giriş
├── register.php         Kayıt (ilk kayıt olan kullanıcı yönetici olur)
├── logout.php           Çıkış
├── profile.php          Profil, avatar, ödüller, istatistikler
├── leaderboard.php      Günlük liderlik tablosu
├── search.php           @kullanıcı arama
├── follow.php           Takip et / takipten çık
├── follows.php          Takipçi ve takip edilen listeleri
├── challenge.php        Meydan okuma sonuçları
├── notifications.php    Bildirimler
├── settings.php         Hesap ayarları (şifre, hesabı silme)
├── admin.php            Yönetim paneli (kelimeler, sözlük, kullanıcılar, yedekleme, skorlar)
├── router.php           Sadece php -S için erişim kuralları
├── config.php           Ayarlar, session, include'lar
├── api/
│   ├── game.php         Oyun API'si (start, guess, create_challenge)
│   └── define.php       Kelime anlamı API'si
├── includes/
│   ├── helpers.php      e(), lower(), upper(), letters(), json_response()
│   ├── storage.php      read_json(), write_json()
│   ├── words.php        Kelime listeleri, günlük/rastgele kelime
│   ├── auth.php         Kayıt, giriş, yetki, CSRF
│   ├── game.php         Renk hesaplama, zor mod, oyun durumu
│   ├── scores.php       Skor kaydı, filtreleme, istatistik
│   ├── rewards.php      Avatar, seri, puan, ödüller, liderlik
│   ├── social.php       Takip sistemi ve kullanıcı arama
│   ├── notifications.php Bildirimler
│   ├── challenges.php   Meydan okumalar
│   ├── account.php      Şifre değiştirme, hesap silme
│   ├── backup.php       Yedekleme ve geri yükleme
│   ├── dictionary.php   TDK ve Free Dictionary API, önbellek
│   ├── filters.php      Dil/zaman filtre formu
│   └── layout.php       Ortak üst bar ve alt bilgi
├── data/                (tarayıcıdan erişime kapalı)
│   ├── users.json
│   ├── scores.json
│   ├── definitions.json
│   ├── challenges.json
│   ├── notifications.json
│   ├── backups/         Sunucudaki otomatik yedekler
│   └── words/words_{tr|en}_5.json
└── assets/
    ├── css/style.css    6 tema: Classic, Aqua, Neon, Granite, Chill, Ruby
    └── js/
        ├── i18n.js      TR/EN arayüz çevirileri
        ├── common.js    Tema, arayüz dili, filtre hafızası (localStorage)
        ├── avatar.js    SVG avatar çizimi
        ├── game.js      Tahta, klavye, API istekleri, paylaşma
        └── profile.js   Avatar düzenleyici
```
