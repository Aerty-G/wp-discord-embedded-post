# WP Discord Embedded Post :satellite:

Plugin WordPress untuk menampilkan konten Discord (channel/message) secara dinamis di website Anda melalui shortcode dan widget.

## :sparkles: Fitur Utama
- **Embed Pesan Discord**  
  Tampilkan pesan Discord spesifik langsung di postingan/pages WordPress
- **Embed Channel Discord**  
  Tampilkan stream terbaru dari channel Discord tertentu
- **Dukungan Webhook**  
  Terintegrasi dengan Discord Webhooks untuk autentikasi
- **Shortcode Fleksibel**  
  `[discord_embed]` dengan parameter kustomisasi
- **Widget Sidebar**  
  Tampilkan konten Discord di area widget/sidebar
- **Manajemen Tema**  
  Dukungan mode terang/gelap via parameter
- **Responsif**  
  Tampilan optimal di desktop & mobile

## :wrench: Instalasi
1. Download [zip terbaru dari GitHub](https://github.com/Aerty-G/wp-discord-embedded-post/archive/refs/heads/main.zip)
2. Di WordPress admin, buka **Plugins → Add New → Upload Plugin**
3. Upload file zip
4. Aktifkan plugin
5. Buka **Discord → Settings** untuk konfigurasi API

## :rocket: Penggunaan

### Shortcode Dasar
```html
[discord_embed 
  channel_id="1077268443495977021" 
  message_id="1077268443495977021"]
```

### Shortcode Lanjutan (Channel)
```html
[discord_embed 
  channel_id="1077268443495977021" 
  theme="dark" 
  height="500" 
  show_header="true"]
```

### Widget Dashboard
1. Buka **Appearance → Widgets**
2. Tambahkan widget "Discord Embedded"
3. Isi Channel ID dan konfigurasi:
   - Tema (light/dark)
   - Tinggi kontainer
   - Tampilkan header
   - Limit pesan

### Konfigurasi Webhook
1. Dapatkan webhook dari server Discord (Channel Settings → Integrations)
2. Tempel URL webhook di **Discord Settings → Webhook URL**
3. Simpan perubahan

## :file_folder: Struktur Kode
```bash
wp-discord-embedded-post/
├── admin/                  # Admin interface
│   ├── admin-page.php      # Halaman setting utama
│   ├── admin-settings.php  # Logika penyimpanan setting
│   └── enqueue-scripts.php # Load assets admin
├── discord/                # Core Discord logic
│   ├── class-discord-api.php  # API handler
│   └── discord-message.php    # Parser data Discord
├── public/                 # Frontend components
│   ├── enqueue-scripts.php # Load public assets
│   └── shortcode.php       # Shortcode processor
├── languages/              # Translation files
├── assets/                 # CSS/JS/Images
│   ├── css/
│   │   ├── admin.css       # Admin styles
│   │   └── public.css      # Frontend styles
│   └── js/
│       ├── admin.js        # Admin scripts
│       └── public.js       # Frontend interactivity
├── includes/               # Helper functions
│   └── utils.php           # Utilities
├── wp-discord-embedded-post.php # Plugin bootstrap
└── readme.txt              # WordPress.org metadata
```

## :gear: Konfigurasi API
Plugin menggunakan Discord API dengan autentikasi token:
```php
// Contoh inisialisasi API (discord/class-discord-api.php)
$this->api_url = 'https://discord.com/api/v10/channels/';
$this->headers = [
  'Authorization' => 'Bot ' . $this->bot_token,
  'Content-Type' => 'application/json'
];
```

Parameter penting di `admin/admin-settings.php`:
```php
register_setting('discord_settings', 'discord_webhook_url');
register_setting('discord_settings', 'discord_bot_token');
register_setting('discord_settings', 'discord_default_theme');
```

## :triangular_flag_on_post: Best Practices
1. **Security**  
   Semua input user disanitasi menggunakan:
   ```php
   sanitize_text_field($_POST['discord_channel_id'])
   ```
2. **Caching**  
   Data Discord di-cache selama 30 menit (default):
   ```php
   set_transient('discord_channel_data', $data, 30 * MINUTE_IN_SECONDS);
   ```
3. **Error Handling**  
   Penanganan error API di `discord/class-discord-api.php`:
   ```php
   if (is_wp_error($response)) {
     error_log('Discord API error: ' . $response->get_error_message());
     return false;
   }
   ```

## :warning: Troubleshooting
**Masalah Umum:**
- **Konten tidak muncul**  
  Periksa:  
  - Token bot valid (Settings → Discord → Bot Token)  
  - Channel ID benar (bukan server ID)  
  - Channel mengizinkan embed (Discord Channel Settings)  
- **Tampilan rusak**  
  Konflik CSS: Tambahkan `!important` ke file `public/css/public.css`
- **Error API 403**  
  Pastikan bot memiliki permission:  
  `View Channel` dan `Read Message History`

## :arrows_counterclockwise: Kompatibilitas
- **Tested WordPress:** 6.0+  
- **PHP Requirement:** 7.4+  
- **Browser Support:**  
  Chrome, Firefox, Edge, Safari (versi 2 tahun terakhir)

## :handshake: Kontribusi
1. Fork repository
2. Buat branch baru:
   ```bash
   git checkout -b fitur-baru
   ```
3. Commit perubahan:
   ```bash
   git commit -m 'Tambahkan fitur X'
   ```
4. Push ke branch:
   ```bash
   git push origin fitur-baru
   ```
5. Buat Pull Request

## :page_facing_up: Lisensi
GPLv2 - [Lihat LICENSE](https://github.com/Aerty-G/wp-discord-embedded-post/blob/main/LICENSE)

---
> **Catatan Developer**  
> Untuk pengembangan, set environment variable di `wp-config.php`:  
> ```php
> define('DISCORD_DEV_MODE', true);
> ```
```